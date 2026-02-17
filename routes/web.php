<?php

use App\Http\Controllers\PdfFileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/login', '/admin/login')->name('login');

Route::middleware(['signed:relative', 'throttle:30,1'])->group(function (): void {
    Route::get('/share/quotations/{id}', [PdfFileController::class, 'quotationSharedPreview'])->name('share.quotations.preview');
    Route::get('/share/invoices/{id}', [PdfFileController::class, 'invoiceSharedPreview'])->name('share.invoices.preview');
    Route::get('/share/receipts/{id}', [PdfFileController::class, 'receiptSharedPreview'])->name('share.receipts.preview');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/quotations/{id}/pdf/preview', [PdfFileController::class, 'quotationPreview'])->name('quotations.pdf.preview');
    Route::get('/quotations/{id}/pdf/download', [PdfFileController::class, 'quotationDownload'])->name('quotations.pdf.download');
    Route::get('/invoices/{id}/pdf/preview', [PdfFileController::class, 'invoicePreview'])->name('invoices.pdf.preview');
    Route::get('/invoices/{id}/pdf/download', [PdfFileController::class, 'invoiceDownload'])->name('invoices.pdf.download');
    Route::get('/invoices/{id}/receipt/preview', [PdfFileController::class, 'receiptPreview'])->name('invoices.receipt.preview');
    Route::get('/invoices/{id}/receipt/download', [PdfFileController::class, 'receiptDownload'])->name('invoices.receipt.download');
});
