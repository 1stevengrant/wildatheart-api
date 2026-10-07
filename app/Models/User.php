<?php

namespace App\Models;

use App\Builders\UserBuilder;
use App\Observers\UserObserver;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\LogOptions;
use Database\Factories\UserFactory;
use Laravolt\Avatar\Facade as Avatar;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ObservedBy(UserObserver::class)]
#[Fillable([
    'uuid',
    'name',
    'given_name',
    'family_name',
    'email',
    'phone',
    'country_code',
    'city',
    'state',
    'postal_code',
    'image',
    'optimized_image',
    'provider_id',
    'provider',
    'email_verified_at',
    'password',
    'last_login_at',
    'salesforce_id',
    'salesforce_contact_id',
    'wegive_donor_id',
    'profile_step',
    'opted_into_marketing',
    'date_opted_into_marketing',
    'super',
    'avatar',
    'preferences',
    'last_login',
    'agreed_to_host_agreement_date',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasRunwayResource, LogsActivity, Notifiable, SoftDeletes;

    public const int PROFILE_COMPLETE = 2;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'date_opted_into_marketing' => 'datetime',
            'opted_into_marketing' => 'boolean',
            'id' => 'integer',
            'updated_at' => 'datetime',
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
            'super' => 'boolean',
            'agreed_to_host_agreement_date' => 'datetime',
            'last_login' => 'datetime',
            'preferences' => 'json',
            'profile_step' => 'integer',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function attendees(): HasManyThrough
    {
        return $this->hasManyThrough(
            Attendee::class,
            Event::class,
            'user_id',
            'event_id'
        );
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function managedEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class)->withTimestamps();
    }

    public function interestedEvents(): BelongsToMany
    {
        return $this->belongsToMany(
            Event::class,
            'event_user_interests',
            'user_id',
            'event_id'
        )
            ->withTimestamps()
            ->withPivot('archived_at');
    }

    public function interestIn(string $eventId): ?EventUserInterest
    {
        return EventUserInterest::query()
            ->where('user_id', $this->id)
            ->where('event_id', $eventId)
            ->first();
    }

    protected function givenName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->normalizeUppercaseName($value),
            set: fn (?string $value): ?string => $this->normalizeUppercaseName($value),
        );
    }

    protected function familyName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->normalizeUppercaseName($value),
            set: fn (?string $value): ?string => $this->normalizeUppercaseName($value),
        );
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function primaryAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_primary', true);
    }

    protected function fullname(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $name = collect([$this->given_name, $this->family_name])
                    ->filter()
                    ->implode(' ');

                return $name ?: 'Member';
            }
        )->shouldCache();
    }

    protected function name(): Attribute
    {
        return $this->fullname();
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? mb_strtolower($value) : $value,
            set: fn (?string $value) => $value ? mb_strtolower(str_replace(' ', '', $value)) : $value,
        );
    }

    private function normalizeUppercaseName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        if (mb_strtoupper($name) !== $name) {
            return $name;
        }

        if (mb_strtolower($name) === $name) {
            return $name;
        }

        if (mb_strlen($name) <= 2) {
            return $name;
        }

        return mb_convert_case(mb_strtolower($name), MB_CASE_TITLE);
    }

    public function profilePhoto(): string
    {
        if ($this->image) {
            return bunnyImage($this->image, [
                'width' => 500,
                'quality' => 75,
                'format' => 'webp',
            ]);
        }

        try {
            return Avatar::create($this->fullname)->toBase64();
        } catch (\Throwable $exception) {
            report($exception);

            return '';
        }
    }

    public function deleteProfilePhoto(): void
    {
        if (is_null($this->image)) {
            return;
        }

        Storage::disk('s3')->delete($this->image);

        $this->forceFill([
            'image' => null,
        ])->save();
    }

    public function updateProfilePhoto(UploadedFile $photo, $storagePath = 'users'): void
    {
        $previousImage = $this->image;

        $this->forceFill([
            'image' => $photo->storePublicly(
                $storagePath,
                ['disk' => 's3']
            ),
        ])->save();

        if ($previousImage) {
            Storage::disk('s3')->delete($previousImage);
        }
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

    public function scopeRunway(Builder $query): void
    {
        $query->withTrashed();
    }

    public function hasAgreedToHostAgreement(): bool
    {
        return $this->agreed_to_host_agreement_date !== null;
    }

    /**
     * A host has agreed to the host agreement or owns or co-hosts at least one event (drafts included).
     */
    public function isHost(): bool
    {
        return $this->hasAgreedToHostAgreement()
            || $this->events()->exists()
            || $this->managedEvents()->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->super;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('users')
            ->setDescriptionForEvent(function (string $eventName) {
                $userName = auth()->user()?->fullname ?? 'System';

                return "User type has been {$eventName} by {$userName}";
            });
    }

    public function hasCompleteProfile(): bool
    {
        return $this->given_name
            && $this->family_name
            && $this->phone
            && filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }

    protected function md5Email(): Attribute
    {
        return Attribute::make(
            get: fn () => md5($this->email)
        )->shouldCache();
    }
}
