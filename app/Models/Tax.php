<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'taxes';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['tenant_id', 'name', 'rate', 'is_active', 'applies_to'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'rate' => 'decimal:4', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }
}
