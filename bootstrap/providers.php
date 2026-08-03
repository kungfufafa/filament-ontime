<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use Laravel\Socialite\SocialiteServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    SocialiteServiceProvider::class,
];
