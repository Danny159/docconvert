<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentConverterController;

Route::get('/', [DocumentConverterController::class, 'index'])->name('document.index');
Route::post('/document-converter/convert', [DocumentConverterController::class, 'convert'])->name('document.convert');
