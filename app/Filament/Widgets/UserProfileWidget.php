<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class UserProfileWidget extends Widget
{
    protected static ?int $sort = 0;

    protected string $view = 'filament.widgets.user-profile-widget';

    protected int|string|array $columnSpan = 'full';

    public function getUserInfo(): array
    {
        $user = auth()->user();
        $employee = $user?->employee;
        $intern = $user?->intern;
        $freelancer = $user?->freelancer;

        if ($employee) {
            return [
                'name' => $employee->full_name,
                'email' => $user?->email,
                'nip' => $employee->nip ?? '-',
                'division' => $employee->division?->name ?? 'Tanpa Divisi',
                'job_title' => $employee->jobTitle?->name ?? 'Staff',
                'job_level' => $employee->jobLevel?->name ?? 'Employee',
                'company' => $employee->company?->name ?? '-',
                'roles' => $user?->getRoleNames()->implode(', ') ?: 'Employee',
                'status' => ucfirst($employee->status ?? 'Active'),
                'join_date' => $employee->join_date?->format('d M Y') ?? '-',
                'tipe' => 'Karyawan',
            ];
        }

        if ($intern) {
            return [
                'name' => $intern->full_name,
                'email' => $user?->email,
                'nip' => $intern->nis ?? '-',
                'division' => $intern->division?->name ?? '-',
                'job_title' => 'Peserta Magang',
                'job_level' => 'Intern',
                'company' => $intern->company?->name ?? '-',
                'roles' => $user?->getRoleNames()->implode(', ') ?: 'Intern',
                'status' => ucfirst($intern->status ?? 'Active'),
                'join_date' => $intern->start_date?->format('d M Y') ?? '-',
                'tipe' => 'Magang',
            ];
        }

        if ($freelancer) {
            return [
                'name' => $freelancer->full_name,
                'email' => $user?->email,
                'nip' => $freelancer->freelancer_number ?? '-',
                'division' => $freelancer->division?->name ?? '-',
                'job_title' => 'Mitra Kerja Freelance',
                'job_level' => 'Freelancer',
                'company' => $freelancer->company?->name ?? '-',
                'roles' => $user?->getRoleNames()->implode(', ') ?: 'Freelancer',
                'status' => ucfirst($freelancer->status ?? 'Active'),
                'join_date' => $freelancer->start_date?->format('d M Y') ?? '-',
                'tipe' => 'Freelancer',
            ];
        }

        // Fallback hanya untuk Superadmin yang memang tidak punya profil karyawan
        return [
            'name' => $user?->name ?? 'Administrator',
            'email' => $user?->email,
            'nip' => '-',
            'division' => '-',
            'job_title' => '-',
            'job_level' => '-',
            'company' => '-',
            'roles' => $user?->getRoleNames()->implode(', ') ?: 'Superadmin',
            'status' => 'Active',
            'join_date' => '-',
            'tipe' => 'Admin',
        ];
    }
}
