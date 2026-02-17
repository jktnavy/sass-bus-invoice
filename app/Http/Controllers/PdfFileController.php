<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Services\DocumentPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PdfFileController extends Controller
{
    public function quotationSharedPreview(Request $request, string $id)
    {
        abort_unless($this->hasValidShareSignature($request), 403, 'Invalid signature.');

        $quotation = $this->resolveSharedQuotation($id);
        $pdf = app(DocumentPdfService::class)->renderQuotation($quotation);

        return $this->pdfResponse($pdf, $this->wantsDownload($request));
    }

    public function invoiceSharedPreview(Request $request, string $id)
    {
        abort_unless($this->hasValidShareSignature($request), 403, 'Invalid signature.');

        $invoice = $this->resolveSharedInvoice($id);
        $pdf = app(DocumentPdfService::class)->renderInvoice($invoice);

        return $this->pdfResponse($pdf, $this->wantsDownload($request));
    }

    public function receiptSharedPreview(Request $request, string $id)
    {
        abort_unless($this->hasValidShareSignature($request), 403, 'Invalid signature.');

        $invoice = $this->resolveSharedInvoice($id);
        abort_unless((float) $invoice->balance_total <= 0 || (int) $invoice->status === 3, 422, 'Kwitansi hanya tersedia untuk invoice lunas.');

        $pdf = app(DocumentPdfService::class)->renderReceipt($invoice);

        return $this->pdfResponse($pdf, $this->wantsDownload($request));
    }

    public function quotationPreview(Request $request, string $id)
    {
        $quotation = $this->resolveQuotation($request, $id);
        $pdf = app(DocumentPdfService::class)->renderQuotation($quotation);

        return $this->pdfResponse($pdf);
    }

    public function quotationDownload(Request $request, string $id)
    {
        $quotation = $this->resolveQuotation($request, $id);
        $pdf = app(DocumentPdfService::class)->renderQuotation($quotation);

        return $this->pdfResponse($pdf, true);
    }

    public function invoicePreview(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        $pdf = app(DocumentPdfService::class)->renderInvoice($invoice);

        return $this->pdfResponse($pdf);
    }

    public function invoiceDownload(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        $pdf = app(DocumentPdfService::class)->renderInvoice($invoice);

        return $this->pdfResponse($pdf, true);
    }

    public function receiptPreview(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        abort_unless((float) $invoice->balance_total <= 0 || (int) $invoice->status === 3, 422, 'Kwitansi hanya tersedia untuk invoice lunas.');

        $pdf = app(DocumentPdfService::class)->renderReceipt($invoice);

        return $this->pdfResponse($pdf);
    }

    public function receiptDownload(Request $request, string $id)
    {
        $invoice = $this->resolveInvoice($request, $id);
        abort_unless((float) $invoice->balance_total <= 0 || (int) $invoice->status === 3, 422, 'Kwitansi hanya tersedia untuk invoice lunas.');

        $pdf = app(DocumentPdfService::class)->renderReceipt($invoice);

        return $this->pdfResponse($pdf, true);
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

    private function wantsDownload(Request $request): bool
    {
        return (string) $request->query('d', '0') === '1';
    }

    private function pdfResponse(array $pdf, bool $download = false)
    {
        $disposition = $download ? 'attachment' : 'inline';

        return response($pdf['bytes'], 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => $disposition.'; filename="'.$pdf['filename'].'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            'Referrer-Policy' => 'no-referrer',
            'X-Frame-Options' => 'DENY',
        ]);
    }

    private function hasValidShareSignature(Request $request): bool
    {
        $absolute = URL::hasValidSignature($request);
        $relative = URL::hasValidSignature($request, false);

        if ($absolute || $relative) {
            return true;
        }

        // Fallback for copied links that accidentally contain HTML-escaped query keys (amp;expires, amp;signature, etc.).
        $normalized = [];

        foreach ($request->query() as $key => $value) {
            $normalized[preg_replace('/^amp;+/i', '', (string) $key)] = $value;
        }

        if ($normalized === $request->query()) {
            return false;
        }

        $normalizedUrl = $request->url();
        if ($normalized !== []) {
            $normalizedUrl .= '?'.http_build_query($normalized);
        }

        $normalizedRequest = Request::create($normalizedUrl, 'GET');

        return URL::hasValidSignature($normalizedRequest) || URL::hasValidSignature($normalizedRequest, false);
    }

    private function resolveSharedQuotation(string $id): Quotation
    {
        return $this->runWithShareDbContext(fn (): Quotation => Quotation::query()->withoutGlobalScopes()->findOrFail($id));
    }

    private function resolveSharedInvoice(string $id): Invoice
    {
        return $this->runWithShareDbContext(fn (): Invoice => Invoice::query()->withoutGlobalScopes()->findOrFail($id));
    }

    private function runWithShareDbContext(callable $callback): mixed
    {
        DB::statement('EXEC sec.sp_set_context @tenant_id = ?, @is_superadmin = ?', [null, 1]);

        try {
            return $callback();
        } finally {
            DB::statement('EXEC sec.sp_set_context @tenant_id = ?, @is_superadmin = ?', [null, 0]);
        }
    }
}
