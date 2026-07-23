<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
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
            'NIP',
            'Nama Karyawan',
            'Tanggal',
            'Jam Check In',
            'Jam Check Out',
            'Status Kehadiran',
            'Menit Telat',
            'Keterangan / Notes',
        ];
    }

    public function map($row): array
    {
        return [
            $row->employee?->company?->name ?? '-',
            $row->employee?->division?->name ?? '-',
            $row->employee?->nip ?? '-',
            $row->employee?->full_name ?? '-',
            $row->date ? $row->date->format('Y-m-d') : '-',
            $row->check_in ? $row->check_in->format('H:i:s') : '-',
            $row->check_out ? $row->check_out->format('H:i:s') : '-',
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
}
