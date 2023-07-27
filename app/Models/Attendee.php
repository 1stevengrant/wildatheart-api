<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendee extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getFullnameAttribute(): string
    {
        return "{$this->given_name} {$this->family_name}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campfire(): BelongsTo
    {
        return $this->belongsTo(Campfire::class);
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
            get: fn (string $value) => strtolower($value),
        );
    }

    protected function city(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucwords(strtolower($value)),
        );
    }

    protected function contactNumber(): Attribute
    {
        return Attribute::make(
            get: fn () => '+'.$this->country_code.$this->phone,
        );
    }

    public function hasBeenContacted(): bool
    {
        return $this->contacted_on !== null;
    }
}
