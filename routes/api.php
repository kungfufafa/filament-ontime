<?php

use App\Http\Controllers\Api\V1\ApprovalApiController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\AttendanceCorrectionApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LeaveRequestApiController;
use App\Http\Controllers\Api\V1\OvertimeRequestApiController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/employees', [\App\Http\Controllers\Api\EmployeeWebhookController::class, 'handle']);

Route::prefix('v1')->group(function () {

    // Auth Routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Attendance Presensi (POST)
        Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut']);

        // Leave Requests (GET, POST, PUT)
        Route::get('/leave-requests', [LeaveRequestApiController::class, 'index']);
        Route::post('/leave-requests', [LeaveRequestApiController::class, 'store']);
        Route::put('/leave-requests/{id}', [LeaveRequestApiController::class, 'update']);

        // Overtime Requests (GET, POST, PUT)
        Route::get('/overtime-requests', [OvertimeRequestApiController::class, 'index']);
        Route::post('/overtime-requests', [OvertimeRequestApiController::class, 'store']);
        Route::put('/overtime-requests/{id}', [OvertimeRequestApiController::class, 'update']);

        // Attendance Corrections (GET, POST, PUT)
        Route::get('/attendance-corrections', [AttendanceCorrectionApiController::class, 'index']);
        Route::post('/attendance-corrections', [AttendanceCorrectionApiController::class, 'store']);
        Route::put('/attendance-corrections/{id}', [AttendanceCorrectionApiController::class, 'update']);

        // Approval Actions (GET pending, PUT process)
        Route::get('/approvals/pending', [ApprovalApiController::class, 'pending']);
        Route::put('/approvals/{type}/{id}/process', [ApprovalApiController::class, 'process']);
    });
});
