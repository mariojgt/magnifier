<?php

use Illuminate\Support\Facades\Route;
use Mariojgt\Magnifier\Controllers\HomeContoller;
use Mariojgt\Magnifier\Controllers\MediaController;
use Mariojgt\Magnifier\Controllers\DashboardController;
use Mariojgt\Magnifier\Controllers\Auth\LoginController;
use Mariojgt\Magnifier\Controllers\MediaFolderController;

// Standard
Route::group([
    'middleware' => ['web']
], function () {
    // example page not required to be login
    Route::get('/magnifier', [HomeContoller::class, 'index'])->name('magnifier');

    // Media folder api
    Route::post('/folder/create', [MediaFolderController::class, 'createFolder'])->name('folder.create');
    Route::delete('/folder/delete/{folder}', [MediaFolderController::class, 'deleteFolder'])
        ->name('folder.delete');
    Route::get('/folder/list', [MediaFolderController::class, 'folderList'])->name('folder.list');
    Route::get('/folder/load/{folder}', [MediaFolderController::class, 'folderChildren'])
        ->name('folder.load');
    Route::get('/folder/files/{folder}', [MediaFolderController::class, 'folderFiles'])
        ->name('folder.files');
    Route::post('/folder/rename/{folder}', [MediaFolderController::class, 'renameFolder'])
        ->name('folder.rename');

    // Media upload
    Route::post('/file/upload/{folder}', [MediaController::class, 'upload'])->name('file.upload');
    Route::get('media/display/{media}/{size}/{file?}', [MediaController::class, 'mediaRender'])
        ->name('media.render');

});

// Auth Route
Route::group([
    'middleware' => ['web', 'auth', 'verified']
], function () {
    // Logout function
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
    // Example page required to be login
    Route::get('/home_dashboard', [DashboardController::class, 'index'])->name('home_dashboard');
});
