<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'payments';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id', 'number', 'date', 'customer_id', 'method', 'amount', 'unapplied_amount', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'unapplied_amount' => 'decimal:2',
            'status' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
