<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'customers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['tenant_id', 'code', 'name', 'npwp', 'billing_address', 'payment_terms_days', 'notes'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function pics(): HasMany
    {
        return $this->hasMany(CustomerPic::class);
    }
}
