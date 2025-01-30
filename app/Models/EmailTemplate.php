<?php

namespace App\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmailTemplate extends BaseModel
{
    use HasFactory, HasRunwayResource, LogsActivity;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function boot(): void
    {
        parent::boot();
        static::creating(function ($emailTemplate) {
            $emailTemplate->slug = Str::slug($emailTemplate->name);
        });
        static::updating(function ($emailTemplate) {
            $emailTemplate->slug = Str::slug($emailTemplate->name);
            $emailTemplate->last_updated_by = auth()->id();
        });
    }

    public function lastUpdatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('email_templates')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                $userName = $user ? $user->full_name : 'System';

                return "Email Template has been {$eventName} by {$userName}";
            });
    }
}
