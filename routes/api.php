<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\PPIDRequestController;
use App\Http\Controllers\Api\PpidDocumentController;
use App\Http\Controllers\Api\HeroController;
use App\Http\Controllers\Api\AnnouncementController;

Route::get('/news', [NewsController::class, 'index']);

Route::get('/announcement-popup', [NewsController::class, 'popup']);

Route::get('/contact', [\App\Http\Controllers\Api\ContactController::class, 'index']);

Route::get('/heroes', [HeroController::class, 'index']);

Route::get('/announcements', [AnnouncementController::class, 'index']);

Route::post('/complaints', [ComplaintController::class, 'store']);

Route::post('/ppid-requests', [PPIDRequestController::class, 'store']);

Route::get('/ppid-documents', [PpidDocumentController::class, 'index']);

Route::get('/officials', [\App\Http\Controllers\Api\OfficialController::class, 'index']);

Route::get('/police-stations', [\App\Http\Controllers\Api\PoliceStationController::class, 'index']);
