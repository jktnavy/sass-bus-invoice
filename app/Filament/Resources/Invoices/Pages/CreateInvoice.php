<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Quotation;
use App\Services\AccountingService;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount(): void
    {
        parent::mount();

        $sourceQuotationId = request()->query('source_quotation_id');

        if (! $sourceQuotationId) {
            return;
        }

        $quotation = Quotation::query()->with('items')->find($sourceQuotationId);

        if (! $quotation) {
            return;
        }

        $this->form->fill([
            'source_quotation_id' => $quotation->id,
            'date' => now()->toDateString(),
            'due_date' => $quotation->valid_until?->toDateString() ?? Carbon::now()->addDays(14)->toDateString(),
            'customer_id' => $quotation->customer_id,
            'currency' => $quotation->currency,
            'notes' => $quotation->notes,
            'items' => $quotation->items->map(fn ($item): array => [
                'item_id' => $item->item_id,
                'name' => $item->name,
                'description' => $item->description,
                'qty' => (float) $item->qty,
                'uom' => $item->uom,
                'price' => (float) $item->price,
                'discount' => (float) $item->discount,
                'tax_id' => $item->tax_id,
                'sort_order' => $item->sort_order,
            ])->values()->all(),
        ]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number'] = app(AccountingService::class)->nextNumber('invoice');

        return $data;
    }

    protected function afterCreate(): void
    {
        app(AuditLogService::class)->log('create', 'invoice', $this->record, null, $this->record->toArray());
    }
}
