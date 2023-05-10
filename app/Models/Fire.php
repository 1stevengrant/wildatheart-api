<?php

namespace App\Models;

use App\Builders\FireBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fire extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function newEloquentBuilder($query): FireBuilder
    {
        return new FireBuilder($query);
    }
}
