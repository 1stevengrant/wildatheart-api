<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookShipment extends Model
{
    protected $guarded = ['id'];

    public function campFire(): BelongsTo
    {
        return $this->belongsTo(Campfire::class);
    }
}
