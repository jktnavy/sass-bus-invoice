<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'customers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['tenant_id', 'code', 'name', 'npwp', 'billing_address', 'email', 'payment_terms_days', 'pic_name', 'pic_phone', 'notes'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    }
}
