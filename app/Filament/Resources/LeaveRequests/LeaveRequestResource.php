<?php

namespace App\Filament\Resources\LeaveRequests;

use App\Filament\Resources\LeaveRequests\Pages\CreateLeaveRequest;
use App\Filament\Resources\LeaveRequests\Pages\EditLeaveRequest;
use App\Filament\Resources\LeaveRequests\Pages\ListLeaveRequests;
use App\Models\Approver;
use App\Models\LeaveRequest;
use App\Services\ApprovalFlowService;
use App\Services\FileNamingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class LeaveRequestResource extends Resource
{
    protected static ?string $model = LeaveRequest::class;

    protected static ?string $slug = 'leave-requests';

    protected static ?string $modelLabel = 'Cuti & Izin';

    protected static ?string $pluralModelLabel = 'Pengajuan Cuti & Izin';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|UnitEnum|null $navigationGroup = 'Presensi & Pengajuan';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if ($user?->intern !== null) {
            return false;
        }

        return ! ($user?->hasRole('Superadmin') ?? false);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if ($user?->intern !== null) {
            return false;
        }

        return true;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if ($user?->intern !== null) {
            return false;
        }

        return $user?->hasAnyRole(['Employee', 'BOD']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->hasRole('Superadmin') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('Superadmin') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('Superadmin') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->hasRole('Superadmin')) {
            return $query;
        }

        $employee = $user->employee;
        $isApprover = $user->hasAnyRole(['Approver', 'BOD']);

        if ($isApprover) {
            $divisionIds = Approver::where('user_id', $user->id)
                ->whereNotNull('division_id')
                ->pluck('division_id')
                ->toArray();

            $companyIds = Approver::where('user_id', $user->id)
                ->whereNull('division_id')
                ->pluck('company_id')
                ->toArray();

            return $query->whereHas('employee', function ($q) use ($divisionIds, $companyIds, $employee) {
                $q->where(function ($sub) use ($divisionIds, $companyIds, $employee) {
                    if (! empty($divisionIds)) {
                        $sub->whereIn('division_id', $divisionIds);
                    }
                    if (! empty($companyIds)) {
                        $sub->orWhereIn('company_id', $companyIds);
                    }
                    if ($employee) {
                        $sub->orWhere('id', $employee->id);
                    }
                });
            });
        }

        if ($employee) {
            return $query->where('employee_id', $employee->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(fn (Get $get): string => match ($get('leave_type')) {
                    'permission' => 'Form Pengajuan Izin Tidak Masuk',
                    'sick' => 'Form Pengajuan Sakit',
                    default => 'Form Pengajuan Cuti Tahunan',
                })
                    ->description('Pilih jenis pengajuan, tentukan rentang tanggal, dan isi alasan pendukung.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('leave_type')
                                ->label('Jenis Pengajuan')
                                ->options([
                                    'annual_leave' => 'Cuti Tahunan (Potong Kuota)',
                                    'permission' => 'Izin Tidak Masuk',
                                    'sick' => 'Sakit (Dengan/Tanpa Surat Dokter)',
                                ])
                                ->default('annual_leave')
                                ->required()
                                ->live(),

                            DatePicker::make('start_date')
                                ->label(fn (Get $get): string => match ($get('leave_type')) {
                                    'permission' => 'Tanggal Mulai Izin',
                                    'sick' => 'Tanggal Mulai Sakit',
                                    default => 'Tanggal Mulai Cuti',
                                })
                                ->required()
                                ->default(today()),

                            DatePicker::make('end_date')
                                ->label(fn (Get $get): string => match ($get('leave_type')) {
                                    'permission' => 'Tanggal Selesai Izin',
                                    'sick' => 'Tanggal Selesai Sakit',
                                    default => 'Tanggal Selesai Cuti',
                                })
                                ->required()
                                ->default(today()),

                            FileUpload::make('attachment')
                                ->label(fn (Get $get): string => match ($get('leave_type')) {
                                    'sick' => 'Surat Keterangan Dokter (Disarankan)',
                                    'permission' => 'Dokumen Pendukung Izin (Opsional)',
                                    default => 'Dokumen Pendukung Cuti (Opsional)',
                                })
                                ->directory('leave-attachments')
                                ->disk('s3')
                                ->visibility('public')
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                    $employeeCode = auth()->user()?->employee?->employee_code ?? auth()->id();

                                    return FileNamingService::generateFileName('LEAVE', (string) $employeeCode, $file);
                                })
                                ->columnSpanFull(),

                            Textarea::make('reason')
                                ->label(fn (Get $get): string => match ($get('leave_type')) {
                                    'permission' => 'Alasan / Keterangan Izin',
                                    'sick' => 'Keterangan Sakit / Gejala',
                                    default => 'Alasan / Keterangan Cuti',
                                })
                                ->required()
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $service = new ApprovalFlowService;

        return $table
            ->columns([
                TextColumn::make('employee.full_name')
                    ->label('Nama Karyawan')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('leave_type')
                    ->label('Jenis Cuti')
                    ->colors([
                        'info' => 'annual_leave',
                        'warning' => 'permission',
                        'danger' => 'sick',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'annual_leave' => 'Cuti Tahunan',
                        'permission' => 'Izin',
                        'sick' => 'Sakit',
                        default => $state,
                    }),

                TextColumn::make('start_date')
                    ->label('Tgl Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Tgl Selesai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('days_count')
                    ->label('Durasi')
                    ->formatStateUsing(fn ($state) => "{$state} Hari"),

                BadgeColumn::make('status')
                    ->label('Status Request')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Menunggu Approval',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),
            ])
            ->actions([
                Action::make('lacakProgres')
                    ->label('Lacak Progres')
                    ->color('info')
                    ->visible(fn (LeaveRequest $record): bool => auth()->user()?->canTrackApprovalProgressFor($record->employee) ?? false)
                    ->modalHeading('Progres Approval Transparan')
                    ->modalDescription('Melacak status persetujuan di setiap tahap secara real-time.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->schema(function (LeaveRequest $record) {
                        $steps = $record->approvalSteps()->orderBy('step_order')->get();
                        $schemaComponents = [];

                        foreach ($steps as $step) {
                            $statusLabel = match ($step->status) {
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                                'pending' => ($step->step_order == $record->current_step && $record->status === 'pending')
                                    ? ' Menunggu Persetujuan (Tahap Aktif)'
                                    : ' Belum Dimulai',
                                default => $step->status,
                            };

                            $actionedBy = $step->actioner ? $step->actioner->name : '-';
                            $actionedAt = $step->actioned_at ? $step->actioned_at->format('d M Y H:i') : '-';
                            $notes = $step->notes ? " (Catatan: {$step->notes})" : '';

                            $schemaComponents[] = Section::make("Tahap {$step->step_order}: {$step->step_name}")
                                ->description("Status: {$statusLabel} | Oleh: {$actionedBy} ({$actionedAt}){$notes}")
                                ->compact();
                        }

                        return $schemaComponents;
                    }),

                Action::make('approveStep')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (LeaveRequest $record) => $service->isUserAuthorizedToApprove($record, auth()->user()))
                    ->requiresConfirmation()
                    ->action(function (LeaveRequest $record) use ($service): void {
                        $service->approveStep($record, auth()->user());

                        Notification::make()
                            ->title('Approval Cuti Berhasil!')
                            ->body('Pengajuan cuti berhasil disetujui.')
                            ->success()
                            ->send();
                    }),

                Action::make('rejectStep')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (LeaveRequest $record) => $service->isUserAuthorizedToApprove($record, auth()->user()))
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (LeaveRequest $record, array $data) use ($service): void {
                        $service->rejectStep($record, auth()->user(), $data['rejection_reason']);

                        Notification::make()
                            ->title('Pengajuan Cuti Ditolak')
                            ->body('Pengajuan cuti telah ditolak.')
                            ->danger()
                            ->send();
                    }),

                EditAction::make()->visible(fn (LeaveRequest $record): bool => static::canEdit($record)),
                DeleteAction::make()->visible(fn (LeaveRequest $record): bool => static::canDelete($record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLeaveRequests::route('/'),
            'create' => CreateLeaveRequest::route('/create'),
            'edit' => EditLeaveRequest::route('/{record}/edit'),
        ];
    }
}
