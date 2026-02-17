<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'quotation_items';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id', 'quotation_id', 'item_id', 'name', 'description', 'qty', 'uom', 'price', 'discount', 'tax_id', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'price' => 'decimal:2',
            'discount' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (! empty($model->tenant_id) || empty($model->quotation_id)) {
                return;
            }

            $model->tenant_id = Quotation::query()
                ->withoutGlobalScopes()
                ->whereKey($model->quotation_id)
                ->value('tenant_id');
        });
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
