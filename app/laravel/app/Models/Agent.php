<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'role', 'prompt', 'tools_enabled', 'temperature', 'language',
    ];

    protected $casts = [
        'tools_enabled' => 'array',
        'temperature' => 'float',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

