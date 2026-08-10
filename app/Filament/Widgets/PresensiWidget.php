<?php

namespace App\Filament\Widgets;

use App\Filament\Concerns\HasPresensiActions;
use Filament\Widgets\Widget;

class PresensiWidget extends Widget
{
    use HasPresensiActions;

    protected static ?int $sort = 1;

    protected string $view = 'filament.widgets.presensi-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('Superadmin')) {
            return false;
        }

        return (bool) ($user->employee || $user->intern || $user->freelancer);
    }
}
