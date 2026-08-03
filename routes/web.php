<?php
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\PPIDRequestController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::resource('news', NewsController::class);

    Route::resource('complaints', ComplaintController::class)->only(['index', 'show']);
    Route::resource('ppid-requests', PPIDRequestController::class)
        ->only(['index', 'show', 'update']);
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

});

require __DIR__ . '/auth.php';


