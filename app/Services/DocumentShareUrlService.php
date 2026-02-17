<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class DocumentShareUrlService
{
    public function quotation(string $quotationId, bool $download = false): string
    {
        return $this->signedUrl('share.quotations.preview', $quotationId, $download);
    }

    public function invoice(string $invoiceId, bool $download = false): string
    {
        return $this->signedUrl('share.invoices.preview', $invoiceId, $download);
    }

    public function receipt(string $invoiceId, bool $download = false): string
    {
        return $this->signedUrl('share.receipts.preview', $invoiceId, $download);
    }

    private function signedUrl(string $routeName, string $id, bool $download = false): string
    {
        $relativeUrl = URL::temporarySignedRoute(
            $routeName,
            Carbon::now()->addMinutes($this->ttlMinutes()),
            [
                'id' => $id,
                'd' => $download ? 1 : 0,
                'v' => 1,
            ],
            absolute: false,
        );

        $appUrl = rtrim((string) config('app.url'), '/');

        return filled($appUrl) ? $appUrl.$relativeUrl : URL::to($relativeUrl);
    }

    private function ttlMinutes(): int
    {
        $raw = (int) env('DOCUMENT_SHARE_TTL_MINUTES', 10080); // 7 days

        return max($raw, 1);
    }
}
