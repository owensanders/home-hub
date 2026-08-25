<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChoreRepeat;
use App\Enums\ChoreStatus;
use Database\Factories\ChoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $household_id
 * @property int|null $assigned_to
 * @property string $name
 * @property ChoreStatus $status
 * @property Carbon $due_date
 * @property ChoreRepeat $repeat
 * @property int $position
 * @property User|null $assignee
 */
class Chore extends Model
{
    /** @use HasFactory<ChoreFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['household_id', 'assigned_to', 'name', 'status', 'due_date', 'repeat', 'position'];

    protected function casts(): array
    {
        return ['status' => ChoreStatus::class, 'due_date' => 'date', 'repeat' => ChoreRepeat::class];
    }

    /** @return BelongsTo<Household, $this> */
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
