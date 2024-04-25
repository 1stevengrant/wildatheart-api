<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Str;

class Page extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'content' => 'array',
    ];

    // create slug from title on boot
    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($page): void {
            $page->slug = Str::slug($page->title);
            $page->last_updated_by = auth()->id();
            $page->created_by = auth()->id();
        });

        static::updating(static function ($page): void {
            $page->last_updated_by = auth()->id();
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
