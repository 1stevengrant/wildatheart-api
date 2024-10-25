<?php

namespace App\Models;

use App\Builders\UserBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Nova\Auth\Impersonatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Impersonatable, Notifiable, SoftDeletes;

    const ROLE_ADMIN = 'admin';

    const ROLE_HOST = 'host';

    const ROLE_ATTENDEE = 'attendee';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'id' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });

        static::deleting(function ($user) {
            $user->events->each->delete();
            $user->addresses->each->delete();
        });
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

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function isAttending(Event $event): bool
    {
        return $this->attendees->contains('event_id', $event->id);
    }

    public function getFullnameAttribute(): string
    {
        if ($this->given_name === null || $this->family_name === null) {
            return $this->email;
        }

        return "{$this->given_name} {$this->family_name}";
    }

    public function emailVerifications(): HasMany
    {
        return $this->hasMany(EmailVerify::class);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function profilePhoto(): ?string
    {
        if ($this->image) {
            makeImagePublic($this->image);

            return $this->image;
        }

        return null;

    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower(str_replace(' ', '', $value)),
        );
    }

    public function contacts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Attendee::class,
            Event::class,
        );
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
        $this->forceFill([
            'image' => $photo->storePublicly(
                $storagePath,
                ['disk' => 's3']
            ),
        ])->save();
    }

    public function generalContacts(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

    public function hasAgreedToHostAgreement(): bool
    {
        return $this->agreed_to_host_agreement_date !== null;
    }

    public function address(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function getMd5EmailAttribute(): string
    {
        return md5($this->email);
    }

    public function isSuperAdmin(): bool
    {
        return $this->super;
    }
}
