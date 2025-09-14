<?php

use Illuminate\Support\Facades\Route;
use Mariojgt\Magnifier\Controllers\HomeContoller;
use Mariojgt\Magnifier\Controllers\MediaController;
use Mariojgt\Magnifier\Controllers\MediaFolderController;

// Standard
Route::group([
    'middleware' => config('media.magnifier_middleware')
], function () {
    // magnifier
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

    // Media upload api
    Route::post('/file/upload/{folder}', [MediaController::class, 'upload'])->name('file.upload');
    Route::delete('/file/delete/{media}', [MediaController::class, 'mediaDelete'])->name('file.delete');
    Route::post('/file/update/{media}', [MediaController::class, 'mediaUpdate'])->name('file.update');
    // Global media search
    Route::get('/media/search', [MediaController::class, 'search'])->name('media.search');
});
