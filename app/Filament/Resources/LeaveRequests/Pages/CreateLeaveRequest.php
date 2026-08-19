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
        $intern = $user?->intern;
        $freelancer = $user?->freelancer;

        // Resolve the active worker profile
        $workerProfile = $employee ?? $intern ?? $freelancer;

        if (! $user?->hasAnyRole(['Employee', 'Intern', 'Freelancer', 'BOD']) || ! $workerProfile) {
            throw ValidationException::withMessages([
                'leave_type' => 'Akun pengguna Anda belum terhubung ke data Karyawan, Magang, atau Freelance.',
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

        // Quota check: applies to employees AND interns (both use company policy)
        // Freelancers are exempt from quota — they can only submit permission/sick, not annual_leave
        if ($data['leave_type'] === 'annual_leave') {
            if ($freelancer) {
                throw ValidationException::withMessages([
                    'leave_type' => 'Freelancer tidak dapat mengajukan cuti tahunan. Gunakan jenis Izin Tidak Masuk.',
                ]);
            }

            $policy = $workerProfile->company?->policy;
            $maxQuota = $policy?->annual_leave_quota ?? 12;

            // Build quota query per worker type
            $usedQuota = LeaveRequest::where('leave_type', 'annual_leave')
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->where(function ($q) use ($employee, $intern) {
                    if ($employee) {
                        $q->where('employee_id', $employee->id);
                    } elseif ($intern) {
                        $q->where('intern_id', $intern->id);
                    }
                })
                ->sum('days_count');

            $remaining = $maxQuota - $usedQuota;

            if ($daysCount > $remaining) {
                throw ValidationException::withMessages([
                    'leave_type' => "Kuota cuti tahunan Anda tidak mencukupi (Sisa: {$remaining} hari, Pengajuan: {$daysCount} hari).",
                ]);
            }
        }

        // Assign exactly one FK
        $data['employee_id'] = $employee?->id;
        $data['intern_id'] = $intern?->id;
        $data['freelancer_id'] = $freelancer?->id;
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
