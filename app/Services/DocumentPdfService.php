<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DocumentPdfService
{
    public function renderQuotation(Quotation $quotation): array
    {
        $quotation->load(['customer.pics', 'items.tax']);
        $tenant = Tenant::query()->find($quotation->tenant_id);

        $bytes = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'tenant' => $tenant,
            'branding' => $this->resolveBranding($tenant),
        ])->setPaper('legal', 'portrait')->output();

        return [
            'bytes' => $bytes,
            'mime' => 'application/pdf',
            'filename' => "quotation-{$quotation->number}.pdf",
        ];
    }

    public function renderInvoice(Invoice $invoice): array
    {
        $invoice->load(['customer', 'items.tax']);
        $tenant = Tenant::query()->find($invoice->tenant_id);

        $bytes = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $tenant,
            'branding' => $this->resolveBranding($tenant),
        ])->setPaper('legal', 'portrait')->output();

        return [
            'bytes' => $bytes,
            'mime' => 'application/pdf',
            'filename' => "invoice-{$invoice->number}.pdf",
        ];
    }

    private function resolveBranding(?Tenant $tenant): array
    {
        $settings = is_array($tenant?->settings) ? $tenant->settings : [];

        return [
            'logo_data_uri' => $this->toDataUri($settings['company_logo_path'] ?? null),
            'stamp_signature_data_uri' => $this->toDataUri($settings['stamp_signature_merged_path'] ?? null),
            'signatory_name' => $settings['signatory_name'] ?? null,
            'signatory_position' => $settings['signatory_position'] ?? null,
            'company_website' => $settings['company_website'] ?? null,
            'document_notes' => $settings['document_notes'] ?? null,
        ];
    }

    private function toDataUri(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $bytes = $disk->get($path);
        $mime = $disk->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }
}
