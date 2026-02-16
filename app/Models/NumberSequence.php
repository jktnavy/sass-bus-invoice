<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NumberSequence extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'number_sequences';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id', 'doc_type', 'prefix', 'suffix', 'padding', 'current_value', 'reset_policy', 'branch_id',
    ];

    protected function casts(): array
    {
        return ['padding' => 'integer', 'current_value' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
    }
}
