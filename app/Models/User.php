<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HouseholdRole;
use App\Enums\Palette;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property int|null $household_id
 * @property string $name
 * @property string|null $initials
 * @property \App\Enums\Palette $colour
 * @property string|null $status_line
 * @property \App\Enums\HouseholdRole $role
 * @property bool $pending
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'household_id',
        'name',
        'initials',
        'colour',
        'status_line',
        'role',
        'pending',
        'email',
        'password',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'colour' => Palette::class,
            'role' => HouseholdRole::class,
            'pending' => 'boolean',
        ];
    }

    /** @return BelongsTo<Household, $this> */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** @return HasMany<Chore, $this> */
    public function assignedChores(): HasMany
    {
        return $this->hasMany(Chore::class, 'assigned_to');
    }

    /** @return BelongsToMany<CalendarEvent, $this> */
    public function attendingEvents(): BelongsToMany
    {
        return $this->belongsToMany(CalendarEvent::class)->orderBy('id');
    }
}
