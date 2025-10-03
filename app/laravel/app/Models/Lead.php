<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'name', 'phone', 'email', 'source', 'stage_id', 'owner_id', 'last_contact_at', 'score', 'tags',
    ];

    protected $casts = [
        'tags' => 'array',
        'last_contact_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }
}

