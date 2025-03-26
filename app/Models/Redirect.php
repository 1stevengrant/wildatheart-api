<?php

namespace App\Models;

use App\Observers\RedirectObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use StatamicRadPack\Runway\Traits\HasRunwayResource;

// Redirect.php
#[ObservedBy([RedirectObserver::class])]
class Redirect extends BaseModel
{
    /** @use HasFactory<\Database\Factories\RedirectFactory> */
    use HasFactory, HasRunwayResource;
}
