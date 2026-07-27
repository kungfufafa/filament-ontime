<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceReportExport implements FromCollection, WithColumnWidths, WithDrawings, WithHeadings, WithMapping, WithStyles
{
    public function __construct(protected Collection $records) {}

    public function collection(): Collection
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Badan Usaha',
            'Divisi',
            'NIP / ID',
            'Nama',
            'Tipe',
            'Tanggal',
            'Jam Check In',
            'Jam Check Out',
            'Foto Check-In',
            'Foto Check-Out',
            'Status Kehadiran',
            'Menit Telat',
            'Keterangan / Notes',
        ];
    }

    public function map($row): array
    {
        $company = $row->employee?->company?->name
            ?? $row->intern?->company?->name
            ?? $row->freelancer?->company?->name
            ?? '-';

        $division = $row->employee?->division?->name
            ?? $row->intern?->division?->name
            ?? $row->freelancer?->division?->name
            ?? '-';

        $nip = $row->employee?->nip
            ?? $row->intern?->nis
            ?? $row->freelancer?->freelancer_number
            ?? '-';

        $name = $row->employee?->full_name
            ?? $row->intern?->full_name
            ?? $row->freelancer?->full_name
            ?? '-';

        $tipe = $row->employee ? 'Karyawan'
            : ($row->intern ? 'Magang'
            : ($row->freelancer ? 'Freelancer' : '-'));

        return [
            $company,
            $division,
            $nip,
            $name,
            $tipe,
            $row->date ? $row->date->format('d/m/Y') : '-',
            $row->check_in ? $row->check_in->format('H:i:s') : '-',
            $row->check_out ? $row->check_out->format('H:i:s') : '-',
            '',
            '',
            match ($row->status) {
                'on_time', 'present' => 'Tepat Waktu / Hadir',
                'late' => 'Terlambat',
                'leave' => 'Cuti / Izin',
                'absent' => 'Alpa',
                default => $row->status ?? '-',
            },
            $row->late_minutes ?? 0,
            $row->notes ?? ($row->is_corrected ? 'Koreksi Absensi' : '-'),
        ];
    }

    public function drawings(): array
    {
        $drawings = [];
        $rowNumber = 2;

        foreach ($this->records as $row) {
            if (! empty($row->check_in_photo) && Storage::disk('public')->exists($row->check_in_photo)) {
                $path = Storage::disk('public')->path($row->check_in_photo);
                if (file_exists($path)) {
                    $drawing = new Drawing;
                    $drawing->setName('Foto Check-In');
                    $drawing->setDescription('Foto Check-In');
                    $drawing->setPath($path);
                    $drawing->setHeight(40);
                    $drawing->setCoordinates('H'.$rowNumber);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(4);
                    $drawings[] = $drawing;
                }
            }

            if (! empty($row->check_out_photo) && Storage::disk('public')->exists($row->check_out_photo)) {
                $path = Storage::disk('public')->path($row->check_out_photo);
                if (file_exists($path)) {
                    $drawing = new Drawing;
                    $drawing->setName('Foto Check-Out');
                    $drawing->setDescription('Foto Check-Out');
                    $drawing->setPath($path);
                    $drawing->setHeight(40);
                    $drawing->setCoordinates('I'.$rowNumber);
                    $drawing->setOffsetX(10);
                    $drawing->setOffsetY(4);
                    $drawings[] = $drawing;
                }
            }

            $rowNumber++;
        }

        return $drawings;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 24,
            'B' => 20,
            'C' => 18,
            'D' => 26,
            'E' => 14,  // Tipe
            'F' => 14,  // Tanggal
            'G' => 14,  // Check In
            'H' => 14,  // Check Out
            'I' => 16,  // Foto Check-In
            'J' => 16,  // Foto Check-Out
            'K' => 22,  // Status
            'L' => 14,  // Menit Telat
            'M' => 28,  // Keterangan
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $totalRows = count($this->records) + 1;

        for ($i = 2; $i <= $totalRows; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(42);
        }

        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->getStyle("E2:K{$totalRows}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E2:K{$totalRows}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A2:D{$totalRows}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("L2:L{$totalRows}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F2937'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
