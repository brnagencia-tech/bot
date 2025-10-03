<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pipeline_id', 'name', 'order',
    ];

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}

