<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'payment_allocations';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['tenant_id', 'payment_id', 'invoice_id', 'allocated_amount'];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
