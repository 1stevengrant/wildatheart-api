<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $guarded = ['id'];

    public function campfire(): BelongsTo
    {
        return $this->belongsTo(Campfire::class);
    }
}
