<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\LeaveRequest;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;

class KalenderCuti extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $title = 'Kalender Cuti Tim';

    protected static ?string $navigationLabel = 'Kalender Cuti Tim';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Presensi & Pengajuan';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.kalender-cuti';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if ($user?->intern !== null) {
            return false;
        }

        return true;
    }

    public ?int $selectedMonth = null;

    public ?int $selectedYear = null;

    public ?int $selectedCompanyId = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;

        $this->form->fill([
            'selectedMonth' => $this->selectedMonth,
            'selectedYear' => $this->selectedYear,
            'selectedCompanyId' => $this->selectedCompanyId,
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Kalender Cuti')
                    ->compact()
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('selectedMonth')
                                ->label('Bulan')
                                ->options([
                                    1 => 'Januari',
                                    2 => 'Februari',
                                    3 => 'Maret',
                                    4 => 'April',
                                    5 => 'Mei',
                                    6 => 'Juni',
                                    7 => 'Juli',
                                    8 => 'Agustus',
                                    9 => 'September',
                                    10 => 'Oktober',
                                    11 => 'November',
                                    12 => 'Desember',
                                ])
                                ->searchable(fn (Select $component): bool => count($component->getOptions()) > 5)
                                ->live()
                                ->required(),

                            Select::make('selectedYear')
                                ->label('Tahun')
                                ->options([
                                    2025 => '2025',
                                    2026 => '2026',
                                    2027 => '2027',
                                ])
                                ->searchable(fn (Select $component): bool => count($component->getOptions()) > 5)
                                ->live()
                                ->required(),

                            Select::make('selectedCompanyId')
                                ->label('Perusahaan (Opsional)')
                                ->options(Company::where('is_active', true)->pluck('name', 'id'))
                                ->placeholder('Semua Perusahaan')
                                ->searchable(fn (Select $component): bool => count($component->getOptions()) > 5)
                                ->live()
                                ->nullable(),
                        ]),
                    ]),
            ]);
    }

    public function getLeaveRequestsProperty()
    {
        $month = $this->selectedMonth ?: now()->month;
        $year = $this->selectedYear ?: now()->year;

        $query = LeaveRequest::with([
            'employee.company', 'employee.division',
            'intern.company', 'intern.division',
            'freelancer.company', 'freelancer.division',
        ])
            ->whereIn('status', ['approved', 'pending'])
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month);

        if ($this->selectedCompanyId) {
            $query->whereHas('employee', fn ($q) => $q->where('company_id', $this->selectedCompanyId));
        }

        return $query->orderBy('start_date')->get();
    }
}
