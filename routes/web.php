<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentConverterController;

// Show conversion form
Route::get('/', function () {
    return view('convert-form');
})->name('convert.form');

// Document conversion routes
Route::post('/convert/pdf-to-word', [DocumentConverterController::class, 'pdfToWordDownload'])
    ->name('convert.pdf-to-word');

// API route for PDF to Word conversion
Route::post('/api/convert/pdf-to-word', [DocumentConverterController::class, 'apiPdfToWord'])
    ->name('api.convert.pdf-to-word');
