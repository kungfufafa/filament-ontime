<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AttendanceStatus: string implements HasColor, HasIcon, HasLabel
{
    case OnTime = 'on_time';
    case Late = 'late';
    case Absent = 'absent';
    case Leave = 'leave';
    case Holiday = 'holiday';
    case Off = 'off';
    case PendingApproval = 'pending_approval';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::OnTime => 'Tepat Waktu',
            self::Late => 'Terlambat',
            self::Absent => 'Tidak Hadir',
            self::Leave => 'Cuti / Izin',
            self::Holiday => 'Hari Libur',
            self::Off => 'Libur',
            self::PendingApproval => 'Menunggu Persetujuan',
            self::Rejected => 'Ditolak',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::OnTime => 'success',
            self::Late => 'warning',
            self::Absent => 'danger',
            self::Leave => 'info',
            self::Holiday => 'gray',
            self::Off => 'gray',
            self::PendingApproval => 'warning',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::OnTime => 'heroicon-o-check-circle',
            self::Late => 'heroicon-o-clock',
            self::Absent => 'heroicon-o-x-circle',
            self::Leave => 'heroicon-o-calendar-days',
            self::Holiday => 'heroicon-o-sun',
            self::Off => 'heroicon-o-moon',
            self::PendingApproval => 'heroicon-o-clock',
            self::Rejected => 'heroicon-o-x-circle',
        };
    }
}
