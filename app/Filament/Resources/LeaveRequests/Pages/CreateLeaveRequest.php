<?php

namespace App\Filament\Resources\LeaveRequests\Pages;

use App\Filament\Resources\LeaveRequests\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\ApprovalFlowService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateLeaveRequest extends CreateRecord
{
    protected static string $resource = LeaveRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $employee = $user?->employee;

        if (! $user?->hasAnyRole(['Employee', 'BOD']) || ! $employee) {
            throw ValidationException::withMessages([
                'leave_type' => 'Akun pengguna Anda belum terhubung ke data Employee.',
            ]);
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lessThan($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]);
        }

        $daysCount = $startDate->diffInDays($endDate) + 1;

        if ($data['leave_type'] === 'annual_leave') {
            $policy = $employee->company?->policy;
            $maxQuota = $policy?->annual_leave_quota ?? 12;

            $usedQuota = (int) LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type', 'annual_leave')
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('days_count');

            $remaining = $maxQuota - $usedQuota;

            if ($daysCount > $remaining) {
                throw ValidationException::withMessages([
                    'leave_type' => "Kuota cuti tahunan Anda tidak mencukupi (Sisa: {$remaining} hari, Pengajuan: {$daysCount} hari).",
                ]);
            }
        }

        $data['employee_id'] = $employee->id;
        $data['days_count'] = $daysCount;
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        $service = new ApprovalFlowService;
        $service->generateSteps($this->record, 'leave');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
