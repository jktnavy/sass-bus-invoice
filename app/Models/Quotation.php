<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'quotations';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id', 'number', 'date', 'valid_until', 'customer_id', 'status', 'currency', 'notes',
        'sub_total', 'discount_total', 'tax_total', 'grand_total',
        'city', 'recipient_title_line1', 'recipient_company_line2', 'attachment_text', 'subject_text',
        'opening_paragraph', 'vehicle_type_text', 'service_route_text', 'fare_text_label', 'fare_amount',
        'usage_date', 'included_text', 'facilities_text', 'payment_method_text', 'closing_paragraph',
        'signatory_name', 'signatory_title',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'valid_until' => 'date',
            'status' => 'integer',
            'sub_total' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'tax_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'fare_amount' => 'decimal:2',
            'usage_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'source_quotation_id');
    }
}
