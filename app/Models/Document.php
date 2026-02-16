<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use BelongsToTenant;
    use HasUuids;

    protected $table = 'documents';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = ['tenant_id', 'owner_table', 'owner_id', 'filename', 'mime', 'size', 'path', 'storage_path', 'created_at'];

    protected function casts(): array
    {
        return ['size' => 'integer', 'created_at' => 'datetime'];
    }
}
