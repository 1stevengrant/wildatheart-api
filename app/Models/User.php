<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Builders\UserBuilder;
use Illuminate\Http\UploadedFile;
use Spatie\Activitylog\LogOptions;
use Laravolt\Avatar\Facade as Avatar;
use App\Jobs\CheckIfUserShouldBeAdmin;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use StatamicRadPack\Runway\Traits\HasRunwayResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    use HasFactory, HasRoles, HasRunwayResource, LogsActivity, Notifiable, SoftDeletes;

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

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (User $user) {
            CheckIfUserShouldBeAdmin::dispatch($user);
        });

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });

        static::deleting(function ($user) {
            $user->events->each->delete();
            $user->addresses->each->delete();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'id' => 'integer',
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

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function getFullnameAttribute(): string
    {
        if ($this->given_name === null || $this->family_name === null) {
            return $this->email;
        }

        return "{$this->given_name} {$this->family_name}";
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null;
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => mb_strtolower($value),
            set: fn (string $value) => mb_strtolower(str_replace(' ', '', $value)),
        );
    }

    public function contacts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Attendee::class,
            Event::class,
        );
    }

    public function profilePhoto()
    {
        if ($this->image) {
            return $this->image;
        }

        return Avatar::create($this->email)->toBase64();
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
                $user = auth()->user();
                $userName = $user ? $user->fullname : 'System';

                return "User type has been {$eventName} by {$userName}";
            });
    }

    public function primaryAddress(): ?Model
    {
        return $this->addresses()->where('is_primary', true)->first();
    }

    public function hasCompleteProfile(): bool
    {
        return
            ! empty($this->family_name) &&
            ! empty($this->given_name) &&
            filter_var($this->email, FILTER_VALIDATE_EMAIL);
    }
}
