<?php

namespace App\Filament\Resources\CompanyLocationResource\Pages;

use App\Filament\Resources\CompanyLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyLocations extends ListRecords
{
    protected static string $resource = CompanyLocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
