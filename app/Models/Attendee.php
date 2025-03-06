<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(\App\Observers\AttendeeObserver::class)]
class Attendee extends BaseModel
{
    use HasFactory, HasRunwayResource, LogsActivity, SoftDeletes;

    public function getFullnameAttribute(): string
    {
        return "{$this->given_name} {$this->family_name}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function hasUser(): bool
    {
        return $this->user_id !== null;
    }

    protected function givenName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
        );
    }

    protected function familyName(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value),
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => mb_strtolower($value),
        );
    }

    protected function city(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords(mb_strtolower($value)),
        );
    }

    protected function contactNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => '+' . $this->country_code . $this->phone,
        );
    }

    public function hasBeenContacted(): bool
    {
        return $this->contacted_on !== null;
    }

    public function scopeOrderByName($query): void
    {
        $query->orderBy('family_name')->orderBy('given_name');
    }

    public function scopeWhereEventId($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('given_name', 'like', '%' . $search . '%')
                    ->orWhere('family_name_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        })->when($filters['event_id'] ?? null, function ($query, $eventId) {
            $query->whereEventId($eventId);
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll() // Log all attributes since you're using $guarded
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('attendees')
            ->setDescriptionForEvent(function (string $eventName) {
                $user = auth()->user();
                $userName = $user ? $user->fullname : 'System';

                return "Attendee has been {$eventName} by {$userName}";
            });
    }
}
