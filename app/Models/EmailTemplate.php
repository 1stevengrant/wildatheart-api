<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function boot(): void
    {
        parent::boot();
        static::creating(function ($emailTemplate) {
            $emailTemplate->slug = Str::slug($emailTemplate->name);
        });
        static::updating(function ($emailTemplate) {
            $emailTemplate->slug = Str::slug($emailTemplate->name);
        });
    }
}
