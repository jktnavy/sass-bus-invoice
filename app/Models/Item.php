<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'items';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['tenant_id', 'code', 'name', 'type', 'uom', 'default_price', 'tax_id', 'metadata'];

    protected function casts(): array
    {
        return ['default_price' => 'decimal:2', 'metadata' => 'array', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }
}
