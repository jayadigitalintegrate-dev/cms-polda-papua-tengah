<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\ComplaintController;
use App\Http\Controllers\Api\PPIDRequestController;
use App\Http\Controllers\Api\PpidDocumentController;
use App\Http\Controllers\Api\HeroController;

Route::get('/news', [NewsController::class, 'index']);

Route::get('/heroes', [HeroController::class, 'index']);

Route::post('/complaints', [ComplaintController::class, 'store']);

Route::post('/ppid-requests', [PPIDRequestController::class, 'store']);

Route::get('/ppid-documents', [PpidDocumentController::class, 'index']);
