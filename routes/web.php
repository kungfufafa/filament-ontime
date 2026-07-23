<?php

use App\Http\Controllers\AttendancePhotoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::post('/upload-selfie', [AttendancePhotoController::class, 'upload'])->name('upload.selfie');
