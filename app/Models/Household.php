<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string|null $location
 * @property int $streak_days
 */
class Household extends Model
{
    /** @use HasFactory<\Database\Factories\HouseholdFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'location', 'streak_days'];

    protected function casts(): array
    {
        return ['streak_days' => 'integer'];
    }

    /** @return HasMany<User, $this> */
    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return HasMany<ShoppingList, $this> */
    public function shoppingLists(): HasMany
    {
        return $this->hasMany(ShoppingList::class)->orderBy('position');
    }

    /** @return HasMany<Chore, $this> */
    public function chores(): HasMany
    {
        return $this->hasMany(Chore::class);
    }

    /** @return HasMany<PlannedMeal, $this> */
    public function plannedMeals(): HasMany
    {
        return $this->hasMany(PlannedMeal::class);
    }

    /** @return HasMany<Recipe, $this> */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /** @return HasMany<CalendarEvent, $this> */
    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    /** @return HasMany<BudgetCategory, $this> */
    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class)->orderBy('position');
    }

    /** @return HasMany<IncomeSource, $this> */
    public function incomeSources(): HasMany
    {
        return $this->hasMany(IncomeSource::class)->orderBy('position');
    }

    /** @return HasMany<BudgetTransaction, $this> */
    public function budgetTransactions(): HasMany
    {
        return $this->hasMany(BudgetTransaction::class);
    }
}
