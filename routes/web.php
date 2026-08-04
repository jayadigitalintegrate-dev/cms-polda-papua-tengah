<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PPIDRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

   /*
|--------------------------------------------------------------------------
| BERITA
|--------------------------------------------------------------------------
*/

// Bulk Delete
Route::delete(
    '/news/bulk-delete',
    [NewsController::class, 'bulkDelete']
)->name('news.bulk-delete');

// Bulk Publish
Route::post(
    '/news/bulk-publish',
    [NewsController::class, 'bulkPublish']
)->name('news.bulk-publish');

// Export PDF
Route::post(
    '/news/export-pdf',
    [NewsController::class, 'exportPdf']
)->name('news.export-pdf');

// CRUD
Route::resource('news', NewsController::class);


    /*
    |--------------------------------------------------------------------------
    | PENGADUAN
    |--------------------------------------------------------------------------
    */

    Route::resource('complaints', ComplaintController::class)
        ->only(['index', 'show']);


   /*
|--------------------------------------------------------------------------
| PPID
|--------------------------------------------------------------------------
*/

// Bulk delete permohonan PPID.
Route::delete(
    '/ppid-requests/bulk-delete',
    [PPIDRequestController::class, 'bulkDelete']
)->name('ppid-requests.bulk-delete');

// Export permohonan PPID ke PDF.
Route::post(
    '/ppid-requests/export-pdf',
    [PPIDRequestController::class, 'exportPdf']
)->name('ppid-requests.export-pdf');

// Daftar, detail, dan update permohonan PPID.
Route::resource('ppid-requests', PPIDRequestController::class)
    ->only([
        'index',
        'show',
        'update',
    ]);

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';