<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HouseholdFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Cashier\Billable;

/**
 * @property int $id
 * @property string $name
 * @property string|null $join_code
 * @property bool $join_code_enabled
 * @property string|null $location
 * @property string|null $address
 * @property int $streak_days
 */
class Household extends Model
{
    use Billable;

    /** @use HasFactory<HouseholdFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'join_code', 'join_code_enabled', 'location', 'address', 'streak_days'];

    protected function casts(): array
    {
        return ['streak_days' => 'integer', 'join_code_enabled' => 'boolean'];
    }

    protected static function booted(): void
    {
        // Every household needs a join code to be found by, whichever path
        // created it — the wizard, a seeder, a factory in a test.
        static::creating(function (Household $household): void {
            if (blank($household->join_code)) {
                $household->join_code = self::generateJoinCode($household->name);
            }
        });
    }

    public static function generateJoinCode(string $householdName): string
    {
        $letters = Str::of($householdName)->upper()->replaceMatches('/[^A-Z]/', '');
        $prefix = $letters->isEmpty() ? 'HOME' : $letters->substr(0, 8)->value();

        do {
            $code = $prefix.'-'.Str::upper(Str::random(6));
        } while (static::where('join_code', $code)->exists());

        return $code;
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

    /** @return HasMany<DocumentFolder, $this> */
    public function documentFolders(): HasMany
    {
        return $this->hasMany(DocumentFolder::class)->orderBy('position');
    }

    /** @return HasMany<Document, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /** The config('plans') slug for this household's active subscription, or 'free' if unsubscribed. */
    public function planSlug(): string
    {
        $subscription = $this->subscription('default');
        $activePrice = $subscription?->active() === true ? $subscription->stripe_price : null;

        if ($activePrice === null) {
            return 'free';
        }

        foreach (config('plans') as $slug => $plan) {
            if (in_array($activePrice, $plan['stripe_price'], true)) {
                return $slug;
            }
        }

        return 'free';
    }
}
