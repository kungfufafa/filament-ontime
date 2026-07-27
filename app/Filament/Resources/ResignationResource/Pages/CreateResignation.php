<?php

namespace App\Filament\Resources\ResignationResource\Pages;

use App\Filament\Resources\ResignationResource;
use App\Services\ApprovalFlowService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateResignation extends CreateRecord
{
    protected static string $resource = ResignationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if (empty($data['employee_id'])) {
            $employee = $user?->employee;

            if (! $employee) {
                throw ValidationException::withMessages([
                    'employee_id' => 'Silakan pilih Karyawan yang mengajukan resign.',
                ]);
            }

            $data['employee_id'] = $employee->id;
        }

        $resignationDate = Carbon::parse($data['resignation_date']);
        $lastWorkingDay = Carbon::parse($data['last_working_day']);

        if ($lastWorkingDay->lessThan($resignationDate)) {
            throw ValidationException::withMessages([
                'last_working_day' => 'Hari kerja terakhir harus sama atau setelah tanggal pengajuan resign.',
            ]);
        }

        $data['status'] = 'pending';

        return $data;
    }

    protected function afterCreate(): void
    {
        $service = app(ApprovalFlowService::class);
        $service->generateSteps($this->record, 'resignation');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
