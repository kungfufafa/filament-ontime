<?php

use App\Http\Controllers\Api\V1\ApprovalApiController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\AttendanceCorrectionApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\LeaveRequestApiController;
use App\Http\Controllers\Api\V1\MasterDataApiController;
use App\Http\Controllers\Api\V1\OvertimeRequestApiController;
use App\Http\Controllers\Api\V1\ReportAndCalendarApiController;
use App\Http\Controllers\Api\V1\ResignationApiController;
use App\Http\Controllers\Api\V1\ShieldApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Auth Routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Shield Permissions & Roles Inspection
        Route::get('/shield/roles', [ShieldApiController::class, 'indexRoles'])->middleware('can:ViewAny:Role');
        Route::get('/shield/permissions', [ShieldApiController::class, 'indexPermissions'])->middleware('can:ViewAny:Role');

        // Attendance Presensi (POST)
        Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn'])->middleware('can:View:AbsenHariIni');
        Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut'])->middleware('can:View:AbsenHariIni');

        // Leave Requests (GET, POST, PUT)
        Route::get('/leave-requests', [LeaveRequestApiController::class, 'index'])->middleware('can:ViewAny:LeaveRequest');
        Route::post('/leave-requests', [LeaveRequestApiController::class, 'store'])->middleware('can:Create:LeaveRequest');
        Route::put('/leave-requests/{id}', [LeaveRequestApiController::class, 'update'])->middleware('can:Update:LeaveRequest');

        // Overtime Requests (GET, POST, PUT)
        Route::get('/overtime-requests', [OvertimeRequestApiController::class, 'index'])->middleware('can:ViewAny:OvertimeRequest');
        Route::post('/overtime-requests', [OvertimeRequestApiController::class, 'store'])->middleware('can:Create:OvertimeRequest');
        Route::put('/overtime-requests/{id}', [OvertimeRequestApiController::class, 'update'])->middleware('can:Update:OvertimeRequest');

        // Attendance Corrections (GET, POST, PUT)
        Route::get('/attendance-corrections', [AttendanceCorrectionApiController::class, 'index'])->middleware('can:ViewAny:AttendanceCorrection');
        Route::post('/attendance-corrections', [AttendanceCorrectionApiController::class, 'store'])->middleware('can:Create:AttendanceCorrection');
        Route::put('/attendance-corrections/{id}', [AttendanceCorrectionApiController::class, 'update'])->middleware('can:Update:AttendanceCorrection');

        // Resignation Requests (GET, POST)
        Route::get('/resignations', [ResignationApiController::class, 'index'])->middleware('can:ViewAny:Resignation');
        Route::post('/resignations', [ResignationApiController::class, 'store'])->middleware('can:Create:Resignation');
        Route::get('/resignations/{id}', [ResignationApiController::class, 'show'])->middleware('can:View:Resignation');

        // Approval Actions (GET pending, PUT process)
        Route::get('/approvals/pending', [ApprovalApiController::class, 'pending'])->middleware('can:View:ApprovalSaya');
        Route::put('/approvals/{type}/{id}/process', [ApprovalApiController::class, 'process'])->middleware('can:View:ApprovalSaya');

        // Master Data APIs
        Route::get('/master/companies', [MasterDataApiController::class, 'companies'])->middleware('can:ViewAny:Company');
        Route::get('/master/divisions', [MasterDataApiController::class, 'divisions'])->middleware('can:ViewAny:Division');
        Route::get('/master/job-titles', [MasterDataApiController::class, 'jobTitles'])->middleware('can:ViewAny:JobTitle');
        Route::get('/master/job-levels', [MasterDataApiController::class, 'jobLevels'])->middleware('can:ViewAny:JobLevel');
        Route::get('/master/employees', [MasterDataApiController::class, 'employees'])->middleware('can:ViewAny:Employee');
        Route::get('/master/interns', [MasterDataApiController::class, 'interns'])->middleware('can:ViewAny:Intern');
        Route::get('/master/freelancers', [MasterDataApiController::class, 'freelancers'])->middleware('can:ViewAny:Freelancer');

        // Reports & Calendar APIs
        Route::get('/kalender-cuti', [ReportAndCalendarApiController::class, 'kalenderCuti'])->middleware('can:View:KalenderCuti');
        Route::get('/laporan-absensi', [ReportAndCalendarApiController::class, 'laporanAbsensi'])->middleware('can:View:LaporanAbsensi');
    });
});
