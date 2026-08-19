<?php

namespace App\Filament\Resources\OvertimeRequests\Pages;

use App\Filament\Resources\OvertimeRequests\OvertimeRequestResource;
use App\Services\ApprovalFlowService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateOvertimeRequest extends CreateRecord
{
    protected static string $resource = OvertimeRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $employee = $user?->employee;
        $intern = $user?->intern;
        $freelancer = $user?->freelancer;

        $workerProfile = $employee ?? $intern ?? $freelancer;

        if (! $user?->hasAnyRole(['Employee', 'Intern', 'Freelancer', 'BOD']) || ! $workerProfile) {
            throw ValidationException::withMessages([
                'reason' => 'Akun pengguna Anda belum terhubung ke data Karyawan, Magang, atau Freelance.',
            ]);
        }

        $data['employee_id'] = $employee?->id;
        $data['intern_id'] = $intern?->id;
        $data['freelancer_id'] = $freelancer?->id;
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        $service = new ApprovalFlowService;
        $service->generateSteps($this->record, 'overtime');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
