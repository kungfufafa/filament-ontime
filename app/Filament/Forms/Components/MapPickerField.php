<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class MapPickerField extends Field
{
    protected string $view = 'filament.forms.components.map-picker-field';

    protected string $latField = 'latitude';

    protected string $lngField = 'longitude';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrated(false);
        $this->label('Pilih Koordinat di Peta');
        $this->columnSpanFull();
    }

    public function latField(string $field): static
    {
        $this->latField = $field;

        return $this;
    }

    public function lngField(string $field): static
    {
        $this->lngField = $field;

        return $this;
    }

    public function getLatField(): string
    {
        return $this->latField;
    }

    public function getLngField(): string
    {
        return $this->lngField;
    }
}
