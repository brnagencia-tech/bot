<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'actor_id', 'action', 'target_type', 'target_id', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

