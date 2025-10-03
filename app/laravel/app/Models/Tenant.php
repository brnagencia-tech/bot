<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'domain', 'status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function pipelines()
    {
        return $this->hasMany(Pipeline::class);
    }

    public function whatsappAccounts()
    {
        return $this->hasMany(WhatsappAccount::class);
    }
}

