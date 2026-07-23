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

        if (! $user?->hasAnyRole(['Employee', 'BOD']) || ! $employee) {
            throw ValidationException::withMessages([
                'reason' => 'Akun pengguna Anda belum terhubung ke data Employee.',
            ]);
        }

        $data['employee_id'] = $employee->id;
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
