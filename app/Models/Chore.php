<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChoreStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $household_id
 * @property int|null $assigned_to
 * @property string $name
 * @property \App\Enums\ChoreStatus $status
 * @property string|null $due_label
 * @property string|null $repeat_label
 * @property int $position
 * @property User|null $assignee
 */
class Chore extends Model
{
    /** @use HasFactory<\Database\Factories\ChoreFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['household_id', 'assigned_to', 'name', 'status', 'due_label', 'repeat_label', 'position'];

    protected function casts(): array
    {
        return ['status' => ChoreStatus::class];
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
