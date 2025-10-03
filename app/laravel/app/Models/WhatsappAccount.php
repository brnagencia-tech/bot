<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id', 'phone_id', 'waba_id', 'access_token', 'status',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

