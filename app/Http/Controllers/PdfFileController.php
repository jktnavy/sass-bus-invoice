<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Services\DocumentPdfService;
use Illuminate\Http\Request;

class PdfFileController extends Controller
{
    public function quotationPreview(Request $request, string $id)
    {
        $quotation = $this->resolveQuotation($request, $id);
        $pdf = app(DocumentPdfService::class)->renderQuotation($quotation);

        return response($pdf['bytes'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$pdf['filename'].'"',
        ]);
    }

    public function quotationDownload(Request $request, string $id)
    {
        $quotation = $this->resolveQuotation($request, $id);
        $pdf = app(DocumentPdfService::class)->renderQuotation($quotation);

        return response($pdf['bytes'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf['filename'].'"',
        ]);
    }

    public function invoicePreview(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        $pdf = app(DocumentPdfService::class)->renderInvoice($invoice);

        return response($pdf['bytes'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$pdf['filename'].'"',
        ]);
    }

    public function invoiceDownload(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        $pdf = app(DocumentPdfService::class)->renderInvoice($invoice);

        return response($pdf['bytes'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf['filename'].'"',
        ]);
    }

    public function receiptPreview(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        abort_unless((float) $invoice->balance_total <= 0 || (int) $invoice->status === 3, 422, 'Kwitansi hanya tersedia untuk invoice lunas.');

        $pdf = app(DocumentPdfService::class)->renderReceipt($invoice);

        return response($pdf['bytes'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$pdf['filename'].'"',
        ]);
    }

    public function receiptDownload(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        abort_unless((float) $invoice->balance_total <= 0 || (int) $invoice->status === 3, 422, 'Kwitansi hanya tersedia untuk invoice lunas.');

        $pdf = app(DocumentPdfService::class)->renderReceipt($invoice);

        return response($pdf['bytes'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdf['filename'].'"',
        ]);
    }

    private function resolveQuotation(Request $request, string $id): Quotation
    {
        $user = $request->user();
        abort_unless($user, 403);

        $query = Quotation::query();
        if ($user->role === 'superadmin') {
            $query->withoutGlobalScopes();
        }

        $record = $query->findOrFail($id);

        if ($user->role !== 'superadmin' && $record->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        return $record;
    }

    private function resolveInvoice(Request $request, string $id): Invoice
    {
        $user = $request->user();
        abort_unless($user, 403);

        $query = Invoice::query();
        if ($user->role === 'superadmin') {
            $query->withoutGlobalScopes();
        }

        $record = $query->findOrFail($id);

        if ($user->role !== 'superadmin' && $record->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        return $record;
    }
}
