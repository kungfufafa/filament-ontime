<?php

namespace App\Filament\Pages;

use App\Exports\AttendanceReportExport;
use App\Models\Approver;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Division;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use UnitEnum;

class LaporanAbsensi extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $title = 'Laporan Absensi';

    protected static ?string $navigationLabel = 'Laporan Absensi';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static string|UnitEnum|null $navigationGroup = 'Presensi & Pengajuan';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.laporan-absensi';

    public ?array $filterData = [];

    public function mount(): void
    {
        $this->form->fill([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => today()->toDateString(),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperadmin = $user?->hasRole('Superadmin');
        $isApprover = $user?->hasAnyRole(['Approver', 'BOD']);

        $allowedCompanyIds = [];
        $allowedDivisionIds = [];

        if ($user && ! $isSuperadmin && $isApprover) {
            $allowedDivisionIds = Approver::where('user_id', $user->id)
                ->whereNotNull('division_id')
                ->pluck('division_id')
                ->toArray();

            $allowedCompanyIds = Approver::where('user_id', $user->id)
                ->whereNull('division_id')
                ->pluck('company_id')
                ->toArray();
        }

        return $schema
            ->statePath('filterData')
            ->components([
                Section::make('Filter Laporan')
                    ->schema([
                        Grid::make(4)->schema([
                            DatePicker::make('date_from')
                                ->label('Dari Tanggal')
                                ->required()
                                ->live(),

                            DatePicker::make('date_to')
                                ->label('Sampai Tanggal')
                                ->required()
                                ->live(),

                            Select::make('company_id')
                                ->label('Badan Usaha')
                                ->options(function () use ($isSuperadmin, $isApprover, $allowedCompanyIds, $allowedDivisionIds) {
                                    $query = Company::query()->where('is_active', true);
                                    if (! $isSuperadmin && $isApprover) {
                                        $query->where(function ($q) use ($allowedCompanyIds, $allowedDivisionIds) {
                                            $q->whereIn('id', $allowedCompanyIds)
                                                ->orWhereHas('divisions', fn ($d) => $d->whereIn('id', $allowedDivisionIds));
                                        });
                                    }

                                    return $query->pluck('name', 'id');
                                })
                                ->live()
                                ->placeholder('Semua Company'),

                            Select::make('division_id')
                                ->label('Divisi')
                                ->options(function (Get $get) use ($isSuperadmin, $allowedDivisionIds, $isApprover) {
                                    $companyId = $get('company_id');
                                    $query = Division::query()->where('is_active', true);

                                    if ($companyId) {
                                        $query->where('company_id', $companyId);
                                    }

                                    if (! $isSuperadmin && $isApprover && ! empty($allowedDivisionIds)) {
                                        $query->whereIn('id', $allowedDivisionIds);
                                    }

                                    return $query->pluck('name', 'id');
                                })
                                ->live()
                                ->placeholder('Semua Divisi'),
                        ]),
                    ]),
            ]);
    }

    public function getReportQuery(): Builder
    {
        $user = auth()->user();
        $isSuperadmin = $user?->hasRole('Superadmin');

        $query = Attendance::query()->with([
            'employee.company',
            'employee.division',
            'intern.company',
            'intern.division',
            'freelancer.company',
            'freelancer.division',
        ]);

        // Scope Enforcement
        if ($user && ! $isSuperadmin) {
            $isApprover = $user->hasAnyRole(['Approver', 'BOD']);
            $employee = $user->employee;
            $intern = $user->intern;
            $freelancer = $user->freelancer;

            if ($isApprover) {
                $divisionIds = Approver::where('user_id', $user->id)
                    ->whereNotNull('division_id')
                    ->pluck('division_id')
                    ->toArray();

                $companyIds = Approver::where('user_id', $user->id)
                    ->whereNull('division_id')
                    ->pluck('company_id')
                    ->toArray();

                $query->where(function ($q) use ($divisionIds, $companyIds) {
                    $q->whereHas('employee', function ($sub) use ($divisionIds, $companyIds) {
                        $sub->where(function ($s) use ($divisionIds, $companyIds) {
                            if (! empty($divisionIds)) {
                                $s->whereIn('division_id', $divisionIds);
                            }
                            if (! empty($companyIds)) {
                                $s->orWhereIn('company_id', $companyIds);
                            }
                            if (empty($divisionIds) && empty($companyIds)) {
                                $s->whereRaw('1 = 0');
                            }
                        });
                    })
                        ->orWhereHas('intern', function ($sub) use ($divisionIds, $companyIds) {
                            $sub->where(function ($s) use ($divisionIds, $companyIds) {
                                if (! empty($divisionIds)) {
                                    $s->whereIn('division_id', $divisionIds);
                                }
                                if (! empty($companyIds)) {
                                    $s->orWhereIn('company_id', $companyIds);
                                }
                            });
                        })
                        ->orWhereHas('freelancer', function ($sub) use ($divisionIds, $companyIds) {
                            $sub->where(function ($s) use ($divisionIds, $companyIds) {
                                if (! empty($divisionIds)) {
                                    $s->whereIn('division_id', $divisionIds);
                                }
                                if (! empty($companyIds)) {
                                    $s->orWhereIn('company_id', $companyIds);
                                }
                            });
                        });
                });
            } elseif ($employee) {
                $query->where('employee_id', $employee->id);
            } elseif ($intern) {
                $query->where('intern_id', $intern->id);
            } elseif ($freelancer) {
                $query->where('freelancer_id', $freelancer->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        // Apply Form Filters
        $data = $this->filterData;

        if (! empty($data['date_from'])) {
            $query->whereDate('date', '>=', $data['date_from']);
        }
        if (! empty($data['date_to'])) {
            $query->whereDate('date', '<=', $data['date_to']);
        }
        if (! empty($data['company_id'])) {
            $cid = $data['company_id'];
            $query->where(function ($q) use ($cid) {
                $q->whereHas('employee', fn ($s) => $s->where('company_id', $cid))
                    ->orWhereHas('intern', fn ($s) => $s->where('company_id', $cid))
                    ->orWhereHas('freelancer', fn ($s) => $s->where('company_id', $cid));
            });
        }
        if (! empty($data['division_id'])) {
            $did = $data['division_id'];
            $query->where(function ($q) use ($did) {
                $q->whereHas('employee', fn ($s) => $s->where('division_id', $did))
                    ->orWhereHas('intern', fn ($s) => $s->where('division_id', $did))
                    ->orWhereHas('freelancer', fn ($s) => $s->where('division_id', $did));
            });
        }

        return $query->orderBy('date', 'desc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->getReportQuery())
            ->columns([
                TextColumn::make('perusahaan_divisi')
                    ->label('Perusahaan & Divisi')
                    ->state(fn (Attendance $record): string => $record->employee?->company?->name
                        ?? $record->intern?->company?->name
                        ?? $record->freelancer?->company?->name
                        ?? '-'
                    )
                    ->description(fn (Attendance $record): string => $record->employee?->division?->name
                        ?? $record->intern?->division?->name
                        ?? $record->freelancer?->division?->name
                        ?? '-'
                    ),

                TextColumn::make('nama_peserta')
                    ->label('Nama')
                    ->state(fn (Attendance $record): string => $record->employee?->full_name
                        ?? $record->intern?->full_name
                        ?? $record->freelancer?->full_name
                        ?? '-'
                    )
                    ->description(fn (Attendance $record): string => 'NIP/ID: '.(
                        $record->employee?->nip
                        ?? $record->intern?->nis
                        ?? $record->freelancer?->freelancer_number
                        ?? '-'
                    )
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('employee', fn ($s) => $s->where('full_name', 'like', "%{$search}%"))
                                ->orWhereHas('intern', fn ($s) => $s->where('full_name', 'like', "%{$search}%"))
                                ->orWhereHas('freelancer', fn ($s) => $s->where('full_name', 'like', "%{$search}%"));
                        });
                    }),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('check_in')
                    ->label('Check In')
                    ->dateTime('H:i:s')
                    ->suffix(' WIB')
                    ->placeholder('-'),

                TextColumn::make('check_out')
                    ->label('Check Out')
                    ->dateTime('H:i:s')
                    ->suffix(' WIB')
                    ->placeholder('-'),

                ImageColumn::make('check_in_photo')
                    ->label('Foto Check-In')
                    ->disk('s3')
                    ->square()
                    ->size(36)
                    ->defaultImageUrl(null),

                ImageColumn::make('check_out_photo')
                    ->label('Foto Check-Out')
                    ->disk('s3')
                    ->square()
                    ->size(36)
                    ->defaultImageUrl(null),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'late' => 'warning',
                        'leave' => 'info',
                        default => 'success',
                    })
                    ->formatStateUsing(fn (Attendance $record): string => match ($record->status) {
                        'late' => "Terlambat ({$record->late_minutes}m)",
                        'leave' => 'Cuti / Izin',
                        default => 'Hadir',
                    }),

                TextColumn::make('notes')
                    ->label('Keterangan')
                    ->state(fn (Attendance $record): string => $record->notes ?? ($record->is_corrected ? 'Koreksi Absensi' : '-')),
            ])
            ->actions([
                Action::make('viewPhoto')
                    ->label('Foto Selfie')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (Attendance $record): string => 'Foto Absensi: '.(
                        $record->employee?->full_name
                        ?? $record->intern?->full_name
                        ?? $record->freelancer?->full_name
                        ?? '-'
                    ))
                    ->modalContent(fn (Attendance $record) => view('filament.components.attendance-photo-modal', ['record' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->visible(fn (Attendance $record): bool => ! empty($record->check_in_photo) || ! empty($record->check_out_photo)),
            ]);
    }

    public function getReportDataProperty(): Collection
    {
        return $this->getReportQuery()->get();
    }

    public function exportExcel(): BinaryFileResponse
    {
        $records = $this->getReportDataProperty();

        return Excel::download(new AttendanceReportExport($records), 'laporan-rekap-absensi.xlsx');
    }
}
