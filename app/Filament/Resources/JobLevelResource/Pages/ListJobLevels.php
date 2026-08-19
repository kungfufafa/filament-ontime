<?php

namespace App\Filament\Resources\JobLevelResource\Pages;

use App\Filament\Resources\JobLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJobLevels extends ListRecords
{
    protected static string $resource = JobLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
