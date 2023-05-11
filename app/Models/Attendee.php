<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getFullnameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
