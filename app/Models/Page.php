<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasRunwayResource, LogsActivity;

    protected $guarded = ['id'];

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

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'new_content' => 'array',
            'is_active' => 'bool',
        ];
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('pages')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                $userName = $user ? $user->fullname : 'System';

                return "Page has been {$eventName} by {$userName}";
            });
    }
}
