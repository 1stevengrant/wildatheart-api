<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fire extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function scopeFuture($query)
    {
        return $query->where('date', '>=', now());
    }
}
