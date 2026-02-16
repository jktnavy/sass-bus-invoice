<?php

use App\Http\Controllers\DocumentFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/login', '/admin/login')->name('login');

Route::middleware('auth')->group(function (): void {
    Route::get('/documents/{id}/open', [DocumentFileController::class, 'show'])->name('documents.open');
    Route::get('/documents/{id}/download', [DocumentFileController::class, 'download'])->name('documents.download');
});
