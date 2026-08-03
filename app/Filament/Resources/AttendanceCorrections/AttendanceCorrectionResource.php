<?php

namespace App\Filament\Resources\AttendanceCorrections;

use App\Filament\Resources\AttendanceCorrections\Pages\CreateAttendanceCorrection;
use App\Filament\Resources\AttendanceCorrections\Pages\EditAttendanceCorrection;
use App\Filament\Resources\AttendanceCorrections\Pages\ListAttendanceCorrections;
use App\Models\Approver;
use App\Models\AttendanceCorrection;
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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use UnitEnum;

class AttendanceCorrectionResource extends Resource
{
    protected static ?string $model = AttendanceCorrection::class;

    protected static ?string $slug = 'koreksi-absensi';

    protected static ?string $modelLabel = 'Koreksi Absensi';

    protected static ?string $pluralModelLabel = 'Koreksi Absensi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static string|UnitEnum|null $navigationGroup = 'Presensi & Pengajuan';

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->employee || $user?->intern || $user?->freelancer || $user?->hasRole('BOD'));
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
        $intern = $user->intern;
        $freelancer = $user->freelancer;
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

            return $query->where(function ($q) use ($divisionIds, $companyIds, $employee) {
                $q->whereHas('employee', function ($sub) use ($divisionIds, $companyIds, $employee) {
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

        if ($intern) {
            return $query->where('intern_id', $intern->id);
        }

        if ($freelancer) {
            return $query->where('freelancer_id', $freelancer->id);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Pengajuan Koreksi Jam Absen')
                    ->description('Pilih tanggal absensi yang ingin dikoreksi dan masukkan usulan jam check-in/out.')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('date')
                                ->label('Tanggal Absensi')
                                ->required()
                                ->default(today()),

                            TimePicker::make('corrected_check_in')
                                ->label('Usulan Jam Check In (24 Jam)')
                                ->native(false)
                                ->displayFormat('H:i')
                                ->format('H:i')
                                ->seconds(false),

                            TimePicker::make('corrected_check_out')
                                ->label('Usulan Jam Check Out (24 Jam)')
                                ->native(false)
                                ->displayFormat('H:i')
                                ->format('H:i')
                                ->seconds(false),

                            FileUpload::make('attachment')
                                ->label('Bukti Pendukung / Lampiran (Opsional)')
                                ->directory('correction-attachments')
                                ->disk('s3')
                                ->visibility('public')
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                    $employeeCode = auth()->user()?->employee?->employee_code ?? auth()->id();

                                    return FileNamingService::generateFileName('CORR', (string) $employeeCode, $file);
                                })
                                ->columnSpanFull(),

                            Textarea::make('reason')
                                ->label('Alasan Koreksi Absensi')
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

                TextColumn::make('date')
                    ->label('Tanggal Absensi')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('corrected_check_in')
                    ->label('Usulan In')
                    ->time('H:i')
                    ->placeholder('-'),

                TextColumn::make('corrected_check_out')
                    ->label('Usulan Out')
                    ->time('H:i')
                    ->placeholder('-'),

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
                    ->visible(fn (AttendanceCorrection $record): bool => auth()->user()?->canTrackApprovalProgressFor($record->employee) ?? false)
                    ->modalHeading('Progres Approval Transparan')
                    ->modalDescription('Melacak status persetujuan di setiap tahap secara real-time.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->schema(function (AttendanceCorrection $record) {
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
                    ->visible(fn (AttendanceCorrection $record) => $service->isUserAuthorizedToApprove($record, auth()->user()))
                    ->requiresConfirmation()
                    ->action(function (AttendanceCorrection $record) use ($service): void {
                        $service->approveStep($record, auth()->user());

                        Notification::make()
                            ->title('Approval Koreksi Berhasil!')
                            ->body('Pengajuan koreksi absensi berhasil disetujui.')
                            ->success()
                            ->send();
                    }),

                Action::make('rejectStep')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (AttendanceCorrection $record) => $service->isUserAuthorizedToApprove($record, auth()->user()))
                    ->schema([
                        Textarea::make('rejection_reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (AttendanceCorrection $record, array $data) use ($service): void {
                        $service->rejectStep($record, auth()->user(), $data['rejection_reason']);

                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->body('Pengajuan telah ditolak.')
                            ->danger()
                            ->send();
                    }),

                EditAction::make()->visible(fn (AttendanceCorrection $record): bool => static::canEdit($record)),
                DeleteAction::make()->visible(fn (AttendanceCorrection $record): bool => static::canDelete($record)),
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
            'index' => ListAttendanceCorrections::route('/'),
            'create' => CreateAttendanceCorrection::route('/create'),
            'edit' => EditAttendanceCorrection::route('/{record}/edit'),
        ];
    }
}
