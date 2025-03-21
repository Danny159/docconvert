<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentConverterController;
use App\Http\Controllers\FileConversionController;

// File conversion routes
Route::get('/', [FileConversionController::class, 'index'])
    ->name('file.index');

Route::post('/', [FileConversionController::class, 'convert'])
    ->name('file.convert');

Route::get('/download', [FileConversionController::class, 'download'])
    ->name('file.download');
