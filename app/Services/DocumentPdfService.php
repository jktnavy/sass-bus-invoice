<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentPdfService
{
    public function generateQuotation(Quotation $quotation): Document
    {
        $quotation->load(['customer', 'items.tax']);
        $tenant = Tenant::query()->find($quotation->tenant_id);

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
            'tenant' => $tenant,
            'branding' => $this->resolveBranding($tenant),
        ])->setPaper('legal', 'portrait');

        return $this->store('quotations', $quotation, $pdf->output(), "quotation-{$quotation->number}.pdf");
    }

    public function generateInvoice(Invoice $invoice): Document
    {
        $invoice->load(['customer', 'items.tax']);
        $tenant = Tenant::query()->find($invoice->tenant_id);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'tenant' => $tenant,
            'branding' => $this->resolveBranding($tenant),
        ])->setPaper('legal', 'portrait');

        return $this->store('invoices', $invoice, $pdf->output(), "invoice-{$invoice->number}.pdf");
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

    private function store(string $folder, Model $owner, string $bytes, string $filename): Document
    {
        $path = $folder.'/'.Str::uuid().'-'.$filename;

        return DB::transaction(function () use ($path, $bytes, $owner, $filename): Document {
            Storage::disk('local')->put($path, $bytes);

            $payload = [
                'tenant_id' => $owner->tenant_id,
                'owner_table' => $owner->getTable(),
                'owner_id' => $owner->getKey(),
                'filename' => $filename,
                'mime' => 'application/pdf',
                'size' => strlen($bytes),
                'path' => $path,
            ];

            if (Schema::hasColumn('documents', 'storage_path')) {
                $payload['storage_path'] = $path;
            }

            return Document::create($payload);
        });
    }
}
