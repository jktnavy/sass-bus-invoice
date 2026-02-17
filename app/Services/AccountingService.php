<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\NumberSequence;
use App\Models\Payment;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AccountingService
{
    public function __construct(private readonly TenantContext $tenantContext)
    {
    }

    public function nextNumber(string $docType, ?string $branchId = null, ?string $tenantId = null): string
    {
        $tenantId ??= $this->tenantContext->requireTenantId();

        try {
            $row = DB::selectOne(
                "DECLARE @out NVARCHAR(80); EXEC dbo.sp_next_number @tenant_id = ?, @doc_type = ?, @branch_id = ?, @out_number = @out OUTPUT; SELECT number = @out;",
                [$tenantId, $docType, $branchId],
            );
        } catch (QueryException $exception) {
            $message = mb_strtolower($exception->getMessage());

            if (! str_contains($message, 'number sequence not found')) {
                throw $exception;
            }

            $this->ensureDefaultNumberSequence($tenantId, $docType, $branchId);

            $row = DB::selectOne(
                "DECLARE @out NVARCHAR(80); EXEC dbo.sp_next_number @tenant_id = ?, @doc_type = ?, @branch_id = ?, @out_number = @out OUTPUT; SELECT number = @out;",
                [$tenantId, $docType, $branchId],
            );
        }

        return (string) $row->number;
    }

    private function ensureDefaultNumberSequence(string $tenantId, string $docType, ?string $branchId = null): void
    {
        $existing = NumberSequence::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('doc_type', $docType)
            ->where('branch_id', $branchId)
            ->exists();

        if ($existing) {
            return;
        }

        $defaultPrefix = match ($docType) {
            'quotation' => 'QT-',
            'invoice' => 'INV-',
            'payment' => 'PAY-',
            'receipt' => 'RCT-',
            default => strtoupper(substr($docType, 0, 3)).'-',
        };

        NumberSequence::query()
            ->withoutGlobalScopes()
            ->create([
                'tenant_id' => $tenantId,
                'doc_type' => $docType,
                'prefix' => $defaultPrefix,
                'suffix' => null,
                'padding' => 5,
                'current_value' => 0,
                'reset_policy' => 'none',
                'branch_id' => $branchId,
            ]);
    }

    public function recalcQuotationTotals(string $quotationId): void
    {
        DB::statement('EXEC dbo.sp_recalc_quotation_totals @quotation_id = ?', [$quotationId]);
    }

    public function recalcInvoiceTotals(string $invoiceId): void
    {
        DB::statement('EXEC dbo.sp_recalc_invoice_totals @invoice_id = ?', [$invoiceId]);
    }

    public function convertQuotationToInvoice(Quotation $quotation, Carbon $dueDate): array
    {
        try {
            $row = DB::selectOne(
                'DECLARE @invoice_id UNIQUEIDENTIFIER, @invoice_number NVARCHAR(80); '
                .'EXEC dbo.sp_convert_quotation_to_invoice @quotation_id = ?, @due_date = ?, '
                .'@out_invoice_id = @invoice_id OUTPUT, @out_invoice_number = @invoice_number OUTPUT; '
                .'SELECT invoice_id = CAST(@invoice_id AS NVARCHAR(36)), invoice_number = @invoice_number;',
                [$quotation->id, $dueDate->toDateString()],
            );
        } catch (QueryException $exception) {
            $message = mb_strtolower($exception->getMessage());

            if (! str_contains($message, 'number sequence not found')) {
                throw $exception;
            }

            $this->ensureDefaultNumberSequence($quotation->tenant_id, 'invoice', null);

            $row = DB::selectOne(
                'DECLARE @invoice_id UNIQUEIDENTIFIER, @invoice_number NVARCHAR(80); '
                .'EXEC dbo.sp_convert_quotation_to_invoice @quotation_id = ?, @due_date = ?, '
                .'@out_invoice_id = @invoice_id OUTPUT, @out_invoice_number = @invoice_number OUTPUT; '
                .'SELECT invoice_id = CAST(@invoice_id AS NVARCHAR(36)), invoice_number = @invoice_number;',
                [$quotation->id, $dueDate->toDateString()],
            );
        }

        return [
            'invoice_id' => (string) $row->invoice_id,
            'invoice_number' => (string) $row->invoice_number,
        ];
    }

    public function postPayment(Payment $payment): void
    {
        DB::statement('EXEC dbo.sp_post_payment @payment_id = ?', [$payment->id]);
    }

    public function reversePayment(Payment $payment): void
    {
        DB::statement('EXEC dbo.sp_reverse_payment @payment_id = ?', [$payment->id]);
    }

    public function allocatePayment(Payment $payment, Invoice $invoice, float $amount): void
    {
        DB::statement(
            'EXEC dbo.sp_allocate_payment @payment_id = ?, @invoice_id = ?, @amount = ?',
            [$payment->id, $invoice->id, $amount],
        );
    }
}
