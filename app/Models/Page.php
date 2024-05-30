<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Str;

class Page extends Model
{
    use HasRunwayResource;

    protected $guarded = ['id'];

    protected $casts = [
        'content' => 'array',
    ];

    protected $hidden = [
        'created_by',
        'last_updated_by',
        'notes',
        'id',
        'is_active',
        'created_at',
        'updated_at',
    ];

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

    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug)->first();
    }
}
