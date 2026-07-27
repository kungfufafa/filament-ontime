<?php

use App\Http\Controllers\AttendancePhotoController;
use App\Livewire\Auth\PhoneLogin;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/phone-login', PhoneLogin::class)
    ->middleware([
        DisableBladeIconComponents::class,
        DispatchServingFilamentEvent::class,
    ])
    ->name('login.phone');

Route::post('/upload-selfie', [AttendancePhotoController::class, 'upload'])->name('upload.selfie');
