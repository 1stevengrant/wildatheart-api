<?php

namespace App\Models;

use App\Observers\RedirectObserver;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([RedirectObserver::class])]
class Redirect extends BaseModel
{
    use HasRunwayResource;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
