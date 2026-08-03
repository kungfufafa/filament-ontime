<?php

use App\Http\Controllers\AttendancePhotoController;
use App\Http\Controllers\CoreSsoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/auth/redirect', [CoreSsoController::class, 'redirect'])->name('sso.redirect');
Route::get('/auth/callback', [CoreSsoController::class, 'callback'])->name('sso.callback');

Route::post('/upload-selfie', [AttendancePhotoController::class, 'upload'])->name('upload.selfie');
