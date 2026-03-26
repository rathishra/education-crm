<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\SectionController;

Route::middleware(['auth', 'super_admin'])->group(function () {
    // Organizations
    Route::resource('organizations', OrganizationController::class);
    
    // Institutions
    Route::resource('institutions', InstitutionController::class);

    // Campuses
    Route::resource('campuses', CampusController::class);

    // Departments
    Route::resource('departments', DepartmentController::class);

    // Courses
    Route::resource('courses', CourseController::class);

    // Batches
    Route::resource('batches', BatchController::class);

    // Sections
    Route::resource('sections', SectionController::class);
    
    // Bulk Upload Routes (Example)
    Route::post('organizations/import', [OrganizationController::class, 'import'])->name('organizations.import');
});
