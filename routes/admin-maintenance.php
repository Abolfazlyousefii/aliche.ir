<?php

use App\Http\Controllers\Admin\MaintenanceController;
use Illuminate\Support\Facades\Route;

Route::get('maintenance/union-member-image', [MaintenanceController::class, 'unionMemberImage'])
    ->name('union-member-image');

Route::post('maintenance/union-member-image/run', [MaintenanceController::class, 'runUnionMemberImage'])
    ->middleware('throttle:3,1')
    ->name('union-member-image.run');

Route::get('maintenance/union-manager-profile', [MaintenanceController::class, 'unionManagerProfile'])
    ->name('union-manager-profile');

Route::post('maintenance/union-manager-profile/run', [MaintenanceController::class, 'runUnionManagerProfile'])
    ->middleware('throttle:3,1')
    ->name('union-manager-profile.run');
