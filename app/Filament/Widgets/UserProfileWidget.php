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

        if ($employee) {
            return [
                'name' => $employee->full_name,
                'email' => $user?->email,
                'nip' => $employee->nip ?? '-',
                'division' => $employee->division?->name ?? 'Tanpa Divisi',
                'job_title' => $employee->jobTitle?->name ?? 'Staff',
                'job_level' => $employee->jobLevel?->name ?? 'Employee',
                'company' => $employee->company?->name ?? 'PT OnTime Indonesia',
                'roles' => $user?->getRoleNames()->implode(', ') ?: 'User',
                'status' => ucfirst($employee->status ?? 'Active'),
                'join_date' => $employee->join_date?->format('d M Y') ?? '-',
            ];
        }

        return [
            'name' => $user?->name ?? 'User Administrator',
            'email' => $user?->email,
            'nip' => 'ADM-001',
            'division' => 'Management / System',
            'job_title' => 'Super Administrator',
            'job_level' => 'Executive',
            'company' => 'PT OnTime Indonesia',
            'roles' => $user?->getRoleNames()->implode(', ') ?: 'Superadmin',
            'status' => 'Active',
            'join_date' => '-',
        ];
    }
}
