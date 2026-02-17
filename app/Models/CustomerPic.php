<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerPic extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'customer_pics';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'name',
        'phone',
        'email',
        'position',
        'notes',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (! empty($model->tenant_id) || empty($model->customer_id)) {
                return;
            }

            $model->tenant_id = Customer::query()
                ->withoutGlobalScopes()
                ->whereKey($model->customer_id)
                ->value('tenant_id');
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
