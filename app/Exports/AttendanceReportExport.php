<?php

namespace App\Exports;

use App\Enums\AttendanceStatus;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceReportExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    protected int $totalColumns = 0;

    public function __construct(
        protected Collection $records,
        protected ?Carbon $dateFrom = null,
        protected ?Carbon $dateTo = null,
        protected ?string $companyName = null,
        protected ?string $divisionName = null,
        protected ?string $workerType = null,
    ) {}

    public function view(): View
    {
        $start = $this->dateFrom?->copy()
            ?? ($this->records->min('date') ? Carbon::parse($this->records->min('date')) : today()->startOfMonth());
        $end = $this->dateTo?->copy()
            ?? ($this->records->max('date') ? Carbon::parse($this->records->max('date')) : today()->endOfMonth());

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        // 1. Pecah rentang tanggal per bulan (berakhir di akhir bulan / batas akhir tanggal)
        $months = [];
        $curr = $start->copy();
        $currMonthDates = [];
        $allDates = [];

        while ($curr->lte($end)) {
            $dayCopy = $curr->copy();
            $currMonthDates[] = $dayCopy;
            $allDates[] = $dayCopy;

            if ($curr->isLastOfMonth() || $curr->isSameDay($end)) {
                $firstD = $currMonthDates[0];
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
                $monthLabel = ($monthNames[$firstD->month] ?? $firstD->format('F')).' '.$firstD->year;

                $months[] = [
                    'label' => strtoupper($monthLabel),
                    'dates' => $currMonthDates,
                    'col_span' => count($currMonthDates) * 2,
                ];
                $currMonthDates = [];
            }
            $curr->addDay();
        }

        $totalDays = count($allDates);
        $this->totalColumns = 2 + ($totalDays * 2) + 5;

        // 2. Ambil data hari libur nasional dalam rentang tanggal
        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($h) => $h->date->format('Y-m-d'));

        // 3. Petakan record presensi per individu (Employee, Intern, Freelancer)
        $workersMap = [];

        foreach ($this->records as $record) {
            $worker = $record->employee ?? $record->intern ?? $record->freelancer;
            if (! $worker) {
                continue;
            }

            $type = $record->employee ? 'employee' : ($record->intern ? 'intern' : 'freelancer');

            // Filter tipe pekerja jika ditentukan
            if ($this->workerType && $type !== $this->workerType) {
                continue;
            }

            $typeLabel = match ($type) {
                'employee' => 'Karyawan',
                'intern' => 'Magang',
                'freelancer' => 'Freelancer',
            };

            $key = "{$type}_{$worker->id}";
            if (! isset($workersMap[$key])) {
                $workersMap[$key] = [
                    'worker' => $worker,
                    'type' => $type,
                    'type_label' => $typeLabel,
                    'name' => $worker->full_name ?? '-',
                    'nip' => $record->employee?->nip ?? $record->intern?->nis ?? $record->freelancer?->freelancer_number ?? '-',
                    'company' => $worker->company?->name ?? '-',
                    'division' => $worker->division?->name ?? '-',
                    'attendances' => [],
                ];
            }

            $dateStr = $record->date?->format('Y-m-d');
            if ($dateStr) {
                $workersMap[$key]['attendances'][$dateStr] = $record;
            }
        }

        // 4. Susun baris matriks untuk setiap orang
        $rows = [];

        foreach ($workersMap as $w) {
            $days = [];
            $summary = [
                'hadir' => 0,
                'telat' => 0,
                'cuti' => 0,
                'libur' => 0,
                'alpa' => 0,
            ];

            foreach ($allDates as $date) {
                $dStr = $date->format('Y-m-d');
                $att = $w['attendances'][$dStr] ?? null;

                $isNationalHoliday = $holidays->has($dStr);

                // Aturan Libur Akhir Pekan:
                // - Magang: Libur hari Sabtu & Minggu
                // - Karyawan / Freelancer: Libur hari Minggu (Sabtu hari kerja)
                $isWeekend = ($w['type'] === 'intern')
                    ? ($date->isSaturday() || $date->isSunday())
                    : $date->isSunday();

                if ($att) {
                    $statusStr = $att->status instanceof AttendanceStatus ? $att->status->value : (string) $att->status;

                    if ($statusStr === 'leave') {
                        $days[$dStr] = ['in' => 'Cuti', 'out' => 'Cuti', 'type' => 'leave'];
                        $summary['cuti']++;
                    } elseif ($statusStr === 'absent') {
                        $days[$dStr] = ['in' => 'Alpa', 'out' => 'Alpa', 'type' => 'absent'];
                        $summary['alpa']++;
                    } elseif (in_array($statusStr, ['holiday', 'off'])) {
                        $days[$dStr] = ['in' => 'Libur', 'out' => 'Libur', 'type' => 'holiday'];
                        $summary['libur']++;
                    } else {
                        $inStr = $att->check_in ? $att->check_in->format('H:i') : '-';
                        $outStr = $att->check_out ? $att->check_out->format('H:i') : '-';
                        $isLate = ($statusStr === 'late') || (($att->late_minutes ?? 0) > 0);

                        $days[$dStr] = [
                            'in' => $inStr,
                            'out' => $outStr,
                            'type' => $isLate ? 'late' : 'present',
                        ];

                        $summary['hadir']++;
                        if ($isLate) {
                            $summary['telat']++;
                        }
                    }
                } else {
                    if ($isNationalHoliday || $isWeekend) {
                        $days[$dStr] = ['in' => 'Libur', 'out' => 'Libur', 'type' => 'holiday'];
                        $summary['libur']++;
                    } else {
                        $days[$dStr] = ['in' => '-', 'out' => '-', 'type' => 'absent'];
                        $summary['alpa']++;
                    }
                }
            }

            $rows[] = [
                'name' => $w['name'],
                'nip' => $w['nip'],
                'type_label' => $w['type_label'],
                'days' => $days,
                'summary' => $summary,
            ];
        }

        // Urutkan nama karyawan secara alfabetis
        usort($rows, fn ($a, $b) => strcmp((string) $a['name'], (string) $b['name']));

        $periodLabel = $start->format('d/m/Y').' - '.$end->format('d/m/Y');
        $workerTypeLabel = match ($this->workerType) {
            'employee' => 'Karyawan',
            'intern' => 'Magang',
            'freelancer' => 'Freelancer',
            default => 'Semua Tipe (Karyawan, Magang, Freelancer)',
        };

        return view('exports.attendance-report-matrix', [
            'months' => $months,
            'rows' => $rows,
            'totalDays' => $totalDays,
            'periodLabel' => $periodLabel,
            'companyName' => $this->companyName,
            'divisionName' => $this->divisionName,
            'workerTypeLabel' => $workerTypeLabel,
        ]);
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getParent()?->getDefaultStyle()->getFont()->setName('Arial');

        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(26);
        $sheet->getRowDimension(4)->setRowHeight(24);
        $sheet->getRowDimension(5)->setRowHeight(20);

        $highestRow = $sheet->getHighestRow();
        $lastCol = $this->totalColumns > 0
            ? Coordinate::stringFromColumnIndex($this->totalColumns)
            : $sheet->getHighestColumn();

        if ($highestRow >= 3) {
            $sheet->getStyle("A3:{$lastCol}{$highestRow}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);
        }

        return [];
    }

    public function title(): string
    {
        return 'Rekap Absensi Bulanan';
    }
}
