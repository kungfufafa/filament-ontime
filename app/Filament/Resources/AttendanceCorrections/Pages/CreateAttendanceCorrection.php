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
        $intern = $user?->intern;
        $freelancer = $user?->freelancer;

        if (! $employee && ! $intern && ! $freelancer) {
            throw ValidationException::withMessages([
                'date' => 'Akun pengguna Anda belum terhubung ke data profil Karyawan/Magang.',
            ]);
        }

        if ($employee) {
            $data['employee_id'] = $employee->id;
        } elseif ($intern) {
            $data['intern_id'] = $intern->id;
        } elseif ($freelancer) {
            $data['freelancer_id'] = $freelancer->id;
        }

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
