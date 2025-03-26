<?php

namespace App\Models;

use App\Observers\RedirectObserver;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

// Redirect.php
#[ObservedBy([RedirectObserver::class])]
class Redirect extends BaseModel
{
    /** @use HasFactory<\Database\Factories\RedirectFactory> */
    use HasFactory, HasRunwayResource;
}
