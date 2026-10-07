<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\PPIDRequestController;
use App\Http\Controllers\Api\PpidDocumentController;
use App\Http\Controllers\Api\HeroController;
use App\Http\Controllers\Api\AnnouncementController;

Route::get('/news', [NewsController::class, 'index'])->middleware('throttle:public-read');

Route::get('/announcement-popup', [NewsController::class, 'popup'])->middleware('throttle:public-read');

Route::get('/contact', [\App\Http\Controllers\Api\ContactController::class, 'index'])->middleware('throttle:public-read');

Route::get('/heroes', [HeroController::class, 'index'])->middleware('throttle:public-read');

Route::get('/announcements', [AnnouncementController::class, 'index'])->middleware('throttle:public-read');

Route::post('/complaints', [ComplaintController::class, 'store'])->middleware('throttle:public-complaints');

Route::post('/ppid-requests', [PPIDRequestController::class, 'store'])->middleware('throttle:public-ppid');

Route::get('/ppid-documents', [PpidDocumentController::class, 'index'])->middleware('throttle:public-read');

Route::get('/officials', [\App\Http\Controllers\Api\OfficialController::class, 'index'])->middleware('throttle:public-read');

Route::get('/police-stations', [\App\Http\Controllers\Api\PoliceStationController::class, 'index'])->middleware('throttle:public-read');

Route::get('/galleries', [\App\Http\Controllers\Api\GalleryController::class, 'index'])->middleware('throttle:public-read');
