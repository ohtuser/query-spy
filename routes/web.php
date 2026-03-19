<?php

use Illuminate\Support\Facades\Route;
use QuerySpy\Http\Controllers\DashboardController;

$url = rtrim(config('queryspy.dashboard_url', '/queryspy'), '/');

Route::get($url,                 [DashboardController::class, 'index'])->name('queryspy.dashboard');
Route::post($url . '/clear',     [DashboardController::class, 'clear'])->name('queryspy.clear');
Route::get($url . '/api',        [DashboardController::class, 'api'])->name('queryspy.api');
Route::get($url . '/export/csv', [DashboardController::class, 'exportCsv'])->name('queryspy.export.csv');
