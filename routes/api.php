<?php

use App\Http\Controllers\Api\EmployeeWebhookController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MasterDataApiController;
use App\Http\Controllers\Api\V1\ReportAndCalendarApiController;
use App\Http\Controllers\Api\V1\ShieldApiController;
use Illuminate\Support\Facades\Route;

Route::post('/webhooks/employees', [EmployeeWebhookController::class, 'handle']);

Route::prefix('v1')->group(function () {

    // Auth Routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Shield Permissions & Roles Inspection
        Route::get('/shield/roles', [ShieldApiController::class, 'indexRoles'])->middleware('can:ViewAny:Role');
        Route::get('/shield/permissions', [ShieldApiController::class, 'indexPermissions'])->middleware('can:ViewAny:Role');

        // Attendance Presensi
        Route::get('/attendance/today', [AttendanceApiController::class, 'today']);
        Route::post('/attendance/check-in', [AttendanceApiController::class, 'checkIn']);
        Route::post('/attendance/check-out', [AttendanceApiController::class, 'checkOut']);
        Route::post('/attendance/master-face', [AttendanceApiController::class, 'registerMasterFace']);
        Route::delete('/attendance/master-face', [AttendanceApiController::class, 'deleteMasterFace']);
        Route::get('/attendances', [AttendanceApiController::class, 'index']);
        Route::get('/attendances/{id}', [AttendanceApiController::class, 'show']);

        // Master Data APIs
        Route::get('/master/companies', [MasterDataApiController::class, 'companies'])->middleware('can:ViewAny:Company');
        Route::get('/master/companies/{id}', [MasterDataApiController::class, 'showCompany'])->middleware('can:ViewAny:Company');

        Route::get('/master/company-locations', [MasterDataApiController::class, 'companyLocations'])->middleware('can:ViewAny:CompanyLocation');
        Route::get('/master/company-locations/{id}', [MasterDataApiController::class, 'showCompanyLocation'])->middleware('can:ViewAny:CompanyLocation');

        Route::get('/master/company-policies', [MasterDataApiController::class, 'companyPolicies'])->middleware('can:ViewAny:CompanyPolicy');
        Route::get('/master/company-policies/{id}', [MasterDataApiController::class, 'showCompanyPolicy'])->middleware('can:ViewAny:CompanyPolicy');

        Route::get('/master/divisions', [MasterDataApiController::class, 'divisions'])->middleware('can:ViewAny:Division');
        Route::get('/master/divisions/{id}', [MasterDataApiController::class, 'showDivision'])->middleware('can:ViewAny:Division');

        Route::get('/master/job-titles', [MasterDataApiController::class, 'jobTitles'])->middleware('can:ViewAny:JobTitle');
        Route::get('/master/job-titles/{id}', [MasterDataApiController::class, 'showJobTitle'])->middleware('can:ViewAny:JobTitle');

        Route::get('/master/job-levels', [MasterDataApiController::class, 'jobLevels'])->middleware('can:ViewAny:JobLevel');
        Route::get('/master/job-levels/{id}', [MasterDataApiController::class, 'showJobLevel'])->middleware('can:ViewAny:JobLevel');

        Route::get('/master/employees', [MasterDataApiController::class, 'employees'])->middleware('can:ViewAny:Employee');
        Route::get('/master/employees/{id}', [MasterDataApiController::class, 'showEmployee'])->middleware('can:ViewAny:Employee');

        Route::get('/master/interns', [MasterDataApiController::class, 'interns'])->middleware('can:ViewAny:Intern');
        Route::get('/master/interns/{id}', [MasterDataApiController::class, 'showIntern'])->middleware('can:ViewAny:Intern');

        Route::get('/master/freelancers', [MasterDataApiController::class, 'freelancers'])->middleware('can:ViewAny:Freelancer');
        Route::get('/master/freelancers/{id}', [MasterDataApiController::class, 'showFreelancer'])->middleware('can:ViewAny:Freelancer');

        Route::get('/master/holidays', [MasterDataApiController::class, 'holidays'])->middleware('can:ViewAny:Holiday');
        Route::get('/master/holidays/{id}', [MasterDataApiController::class, 'showHoliday'])->middleware('can:ViewAny:Holiday');

        Route::get('/master/users', [MasterDataApiController::class, 'users'])->middleware('can:ViewAny:User');
        Route::get('/master/users/{id}', [MasterDataApiController::class, 'showUser'])->middleware('can:ViewAny:User');

        // Reports APIs
        Route::get('/laporan-absensi', [ReportAndCalendarApiController::class, 'laporanAbsensi']);
    });
});
