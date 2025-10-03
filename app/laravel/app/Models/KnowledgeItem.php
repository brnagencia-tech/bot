<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'type', 'title', 'content', 'vector_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

