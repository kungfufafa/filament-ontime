<?php

namespace App\Filament\Resources\AttendanceCorrections\Pages;

use App\Filament\Resources\AttendanceCorrections\AttendanceCorrectionResource;
use App\Services\ApprovalFlowService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateAttendanceCorrection extends CreateRecord
{
    protected static string $resource = AttendanceCorrectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();
        $employee = $user?->employee;

        if (! $user?->hasAnyRole(['Employee', 'BOD']) || ! $employee) {
            throw ValidationException::withMessages([
                'date' => 'Akun pengguna Anda belum terhubung ke data Employee Karyawan.',
            ]);
        }

        $data['employee_id'] = $employee->id;
        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        $service = new ApprovalFlowService;
        $service->generateSteps($this->record, 'correction');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
