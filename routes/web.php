<?php

use App\Http\Controllers\MobilityDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->prefix('mobility-documents')->group(function (): void {
    Route::post('{document}/upload', [MobilityDocumentController::class, 'upload'])->name('mobility-documents.upload');
    Route::patch('{document}/validation', [MobilityDocumentController::class, 'validateDocument'])->name('mobility-documents.validate');
    Route::get('{document}/download', [MobilityDocumentController::class, 'download'])->name('mobility-documents.download');
});
