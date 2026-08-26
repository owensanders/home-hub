<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\HouseholdRole;
use App\Enums\PendingReason;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $household_id
 * @property int $user_id
 * @property HouseholdRole $role
 * @property bool $pending
 * @property PendingReason|null $pending_reason
 */
class HouseholdUser extends Pivot
{
    protected $table = 'household_user';

    protected function casts(): array
    {
        return [
            'role' => HouseholdRole::class,
            'pending' => 'boolean',
            'pending_reason' => PendingReason::class,
        ];
    }
}
