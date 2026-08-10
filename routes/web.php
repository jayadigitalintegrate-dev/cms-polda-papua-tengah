<?php

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\HeroController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PPIDRequestController;
use App\Http\Controllers\PpidCategoryController;
use App\Http\Controllers\PpidDocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | BERITA
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/news/bulk-delete',
        [NewsController::class, 'bulkDelete']
    )->name('news.bulk-delete');

    Route::post(
        '/news/bulk-publish',
        [NewsController::class, 'bulkPublish']
    )->name('news.bulk-publish');

    Route::post(
        '/news/export-pdf',
        [NewsController::class, 'exportPdf']
    )->name('news.export-pdf');

    Route::resource('news', NewsController::class);


    /*
    |--------------------------------------------------------------------------
    | HERO WEBSITE
    |--------------------------------------------------------------------------
    |
    | Hero digunakan untuk slider utama pada website publik Polda Papua Tengah.
    |
    */

    Route::resource('heroes', HeroController::class);


    /*
    |--------------------------------------------------------------------------
    | PENGADUAN
    |--------------------------------------------------------------------------
    */

    Route::resource('complaints', ComplaintController::class)
        ->only([
            'index',
            'show',
        ]);


    /*
    |--------------------------------------------------------------------------
    | PPID REQUESTS
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/ppid-requests/bulk-delete',
        [PPIDRequestController::class, 'bulkDelete']
    )->name('ppid-requests.bulk-delete');

    Route::post(
        '/ppid-requests/export-pdf',
        [PPIDRequestController::class, 'exportPdf']
    )->name('ppid-requests.export-pdf');

    Route::resource('ppid-requests', PPIDRequestController::class)
        ->only([
            'index',
            'show',
            'update',
        ]);


    /*
    |--------------------------------------------------------------------------
    | PPID CATEGORIES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'ppid-categories',
        PpidCategoryController::class
    );


    /*
    |--------------------------------------------------------------------------
    | PPID DOCUMENTS
    |--------------------------------------------------------------------------
    */

    Route::delete(
        '/ppid-documents/bulk-delete',
        [PpidDocumentController::class, 'bulkDelete']
    )->name('ppid-documents.bulk-delete');

    Route::get(
        '/ppid-documents/{ppid_document}/download',
        [PpidDocumentController::class, 'download']
    )->name('ppid-documents.download');

    Route::resource(
        'ppid-documents',
        PpidDocumentController::class
    );


    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    |
    | Profile digunakan untuk:
    | - informasi nama/email
    | - mengganti password
    | - menghapus akun
    |
    | Foto profile nantinya dikelola melalui Settings.
    |
    */

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    |
    | Pengaturan profile photo:
    | - setiap user dapat mengatur foto akunnya sendiri
    |
    | Pengaturan logo Polda:
    | - HANYA superadmin
    |
    */

    Route::get(
        '/settings',
        [SettingsController::class, 'edit']
    )->name('settings.edit');


    /*
    |--------------------------------------------------------------------------
    | PROFILE PHOTO
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/settings/profile-photo',
        [SettingsController::class, 'updateProfilePhoto']
    )->name('settings.profile-photo.update');

    Route::delete(
        '/settings/profile-photo',
        [SettingsController::class, 'deleteProfilePhoto']
    )->name('settings.profile-photo.delete');


    /*
    |--------------------------------------------------------------------------
    | SITE LOGO - SUPERADMIN ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('superadmin')->group(function () {

        Route::post(
            '/settings/logo',
            [SettingsController::class, 'updateLogo']
        )->name('settings.logo.update');

        Route::delete(
            '/settings/logo',
            [SettingsController::class, 'deleteLogo']
        )->name('settings.logo.delete');


        /*
        |--------------------------------------------------------------------------
        | USER MANAGEMENT - SUPERADMIN ONLY
        |--------------------------------------------------------------------------
        */

        Route::resource('users', UserController::class)
            ->only([
                'index',
                'create',
                'store',
                'show',
                'edit',
                'update',
                'destroy',
            ]);
    });
});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';