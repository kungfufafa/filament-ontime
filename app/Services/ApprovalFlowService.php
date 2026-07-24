<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use App\Models\ApprovalRequestStep;
use App\Models\Approver;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ApprovalFlowService
{
    /**
     * Reusable query method to retrieve pending requests requiring action from the given User.
     */
    public function getPendingRequestsForUser(User $user, string $modelClass): Collection
    {
        return $modelClass::query()
            ->with(['employee.company', 'employee.division', 'approvalSteps'])
            ->where('status', 'pending')
            ->get()
            ->filter(fn (Model $item) => $this->isUserAuthorizedToApprove($item, $user));
    }

    /**
     * Generate approval steps for a request based on Company's configured approval flows.
     */
    public function generateSteps(Model $requestModel, string $requestType): array
    {
        if (method_exists($requestModel, 'employee') && ! $requestModel->relationLoaded('employee')) {
            $requestModel->load('employee');
        }

        $companyId = $requestModel->employee?->company_id ?? $requestModel->company_id ?? null;

        if (! $companyId) {
            throw ValidationException::withMessages([
                'company' => 'Badan Usaha (Company) tidak ditemukan pada data pengajuan.',
            ]);
        }

        $flows = ApprovalFlow::where('company_id', $companyId)
            ->where('request_type', $requestType)
            ->orderBy('step_order')
            ->get();

        if ($flows->isEmpty()) {
            throw ValidationException::withMessages([
                'approval_flow' => 'Alur approval untuk jenis pengajuan ini belum dikonfigurasi oleh Perusahaan.',
            ]);
        }

        $generatedSteps = [];

        foreach ($flows as $flow) {
            $step = ApprovalRequestStep::create([
                'approvable_type' => $requestModel->getMorphClass(),
                'approvable_id' => $requestModel->id,
                'step_order' => $flow->step_order,
                'step_name' => $flow->name,
                'approver_type' => $flow->approver_type,
                'approver_role' => $flow->approver_role,
                'user_id' => $flow->user_id,
                'status' => 'pending',
            ]);

            $generatedSteps[] = $step;
        }

        $requestModel->update([
            'current_step' => 1,
            'status' => 'pending',
        ]);

        // Send In-App Notifications to Authorized Approvers
        foreach ($this->getUsersAuthorizedForCurrentStep($requestModel) as $approverUser) {
            Notification::make()
                ->title('Pengajuan Baru Membutuhkan Persetujuan Anda')
                ->body("Pengajuan {$requestType} dari {$requestModel->employee?->full_name} menunggu persetujuan Anda.")
                ->icon('heroicon-o-bell')
                ->color('warning')
                ->sendToDatabase($approverUser);
        }

        return $generatedSteps;
    }

    /**
     * Check if a User is authorized to approve the current active step of a request.
     * Parallel Approver Rule (OR semantics): If multiple approvers exist for a step,
     * ANY one authorized approver satisfies the step.
     */
    public function isUserAuthorizedToApprove(Model $requestModel, User $user): bool
    {
        if ($requestModel->status !== 'pending') {
            return false;
        }

        $currentStepOrder = (int) ($requestModel->current_step ?? 1);
        $step = $requestModel->approvalSteps()
            ->where('step_order', $currentStepOrder)
            ->first();

        if (! $step) {
            return false;
        }

        // 1. Direct User Assignment
        if ($step->approver_type === 'user') {
            return (int) $user->id === (int) $step->user_id;
        }

        // 2. Role + Scope Mapping Check (Approver Table Lookup)
        if ($step->approver_type === 'role') {
            $requiredRole = $step->approver_role ?? 'Approver';
            if (! $user->hasRole($requiredRole)) {
                return false;
            }

            if ($requiredRole === 'Superadmin') {
                return true;
            }

            $employee = $requestModel->employee;
            if (! $employee) {
                return false;
            }

            return Approver::query()
                ->where('user_id', $user->id)
                ->where(function ($query) use ($employee) {
                    $query->where('division_id', $employee->division_id)
                        ->orWhere(function ($q) use ($employee) {
                            $q->whereNull('division_id')
                                ->where('company_id', $employee->company_id);
                        });
                })
                ->exists();
        }

        return false;
    }

    /**
     * Approve the current step for a request.
     */
    public function approveStep(Model $requestModel, User $user, ?string $notes = null): void
    {
        if (! $this->isUserAuthorizedToApprove($requestModel, $user)) {
            throw ValidationException::withMessages([
                'approve' => 'Anda tidak memiliki hak akses untuk menyetujui tahap approval ini.',
            ]);
        }

        $currentStepOrder = (int) ($requestModel->current_step ?? 1);
        $step = $requestModel->approvalSteps()
            ->where('step_order', $currentStepOrder)
            ->first();

        if ($step) {
            $step->update([
                'status' => 'approved',
                'actioned_by' => $user->id,
                'actioned_at' => now(),
                'notes' => $notes,
            ]);
        }

        $maxSteps = (int) $requestModel->approvalSteps()->max('step_order');

        if ($currentStepOrder < $maxSteps) {
            $requestModel->update([
                'current_step' => $currentStepOrder + 1,
            ]);

            // Notify next step approvers
            foreach ($this->getUsersAuthorizedForCurrentStep($requestModel) as $nextApprover) {
                Notification::make()
                    ->title("Pengajuan Membutuhkan Persetujuan Tahap {$requestModel->current_step}")
                    ->body("Pengajuan dari {$requestModel->employee?->full_name} telah diproses ke tahap Anda.")
                    ->icon('heroicon-o-bell')
                    ->color('info')
                    ->sendToDatabase($nextApprover);
            }
        } else {
            $requestModel->update([
                'status' => 'approved',
            ]);

            if (method_exists($requestModel, 'applyCorrection')) {
                $requestModel->applyCorrection();
            }

            if (method_exists($requestModel, 'applyLeave')) {
                $requestModel->applyLeave();
            }
        }
    }

    /**
     * Reject the current step for a request.
     */
    public function rejectStep(Model $requestModel, User $user, string $reason): void
    {
        if (! $this->isUserAuthorizedToApprove($requestModel, $user)) {
            throw ValidationException::withMessages([
                'reject' => 'Anda tidak memiliki hak akses untuk menolak tahap approval ini.',
            ]);
        }

        $currentStepOrder = (int) ($requestModel->current_step ?? 1);
        $step = $requestModel->approvalSteps()
            ->where('step_order', $currentStepOrder)
            ->first();

        if ($step) {
            $step->update([
                'status' => 'rejected',
                'actioned_by' => $user->id,
                'actioned_at' => now(),
                'notes' => $reason,
            ]);
        }

        $requestModel->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Get users who are eligible to act on the request's active approval step.
     */
    private function getUsersAuthorizedForCurrentStep(Model $requestModel): Collection
    {
        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['Approver', 'BOD', 'Superadmin']))
            ->get()
            ->filter(fn (User $user) => $this->isUserAuthorizedToApprove($requestModel, $user));
    }

    /**
     * Determine whether the configured flow uses the mandatory approval sequence.
     *
     * @param  Collection<int, ApprovalFlow>  $flows
     */
    private function isStandardApprovalFlow(Collection $flows): bool
    {
        $expectedRoles = ['Approver', 'BOD', 'Superadmin'];

        return $flows->count() === 3
            && $flows->values()->every(fn (ApprovalFlow $flow, int $index): bool => $flow->step_order === $index + 1
                && $flow->approver_type === 'role'
                && $flow->approver_role === $expectedRoles[$index]
            );
    }
}
