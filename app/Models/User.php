<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Nova\Auth\Impersonatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Impersonatable, Notifiable;

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
    }

    public function attendees(): HasManyThrough
    {
        return $this->hasManyThrough(
            Attendee::class,
            Campfire::class,
            'user_id',
            'campfire_id'
        );
    }

    public function campfires(): HasMany
    {
        return $this->hasMany(Campfire::class);
    }

    public function addresses(): HasManyThrough
    {
        return $this->hasManyThrough(
            Address::class,
            Campfire::class,
        );
    }

    public function isAttending(Campfire $campfire): bool
    {
        return $this->attendees->contains('campfire_id', $campfire->id);
    }

    public function getFullnameAttribute(): string
    {
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

    public function profilePhoto(): string
    {
        if ($this->image) {
            makeImagePublic($this->image);

            return bunnyImage($this->image, [
                'width' => 200,
            ]);
        }

        return asset('svgs/user.svg');

    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => strtolower($value),
            set: fn (string $value) => strtolower(str_replace(' ', '', $value)),
        );
    }

    public function connections(): HasManyThrough
    {
        return $this->hasManyThrough(
            Attendee::class,
            Campfire::class,
        );
    }

    public function generalConnections(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }
}
