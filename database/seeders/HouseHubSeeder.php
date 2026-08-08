<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ChoreStatus;
use App\Enums\MealSlot;
use App\Enums\Palette;
use App\Enums\ShoppingCategory;
use App\Models\Household;
use App\Models\Recipe;
use App\Models\ShoppingList;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * The Parker household — the demo data the HouseHub design was drawn against.
 */
class HouseHubSeeder extends Seeder
{
    public function run(): void
    {
        $household = Household::create([
            'name' => 'The Parkers',
            'location' => 'Bristol',
            'streak_days' => 12,
        ]);

        $members = $this->seedMembers($household);
        $this->seedShopping($household);
        $this->seedChores($household, $members);
        $this->seedMeals($household, $members);
        $this->seedCalendar($household, $members);
        $this->seedBudget($household);
    }

    /** @return array<string, User> */
    private function seedMembers(Household $household): array
    {
        $people = [
            ['sarah', 'Sarah Parker', 'SP', Palette::Mint, 'WFH today'],
            ['james', 'James Parker', 'JP', Palette::Lilac, 'Office · back 6pm'],
            ['mia', 'Mia Parker', 'MP', Palette::Sun, 'School · swim club'],
            ['noah', 'Noah Parker', 'NP', Palette::Sky, 'School · football'],
        ];

        $members = [];

        foreach ($people as [$key, $name, $initials, $colour, $status]) {
            $members[$key] = User::create([
                'household_id' => $household->id,
                'name' => $name,
                'initials' => $initials,
                'colour' => $colour,
                'status_line' => $status,
                'email' => "{$key}@househub.test",
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]);
        }

        return $members;
    }

    private function seedShopping(Household $household): void
    {
        $lists = [
            ['Tesco', Palette::Mint, [
                ['Chicken thighs', '1kg', ShoppingCategory::Fresh, false],
                ['New potatoes', '750g', ShoppingCategory::Vegetables, false],
                ['Lemons', 'x3', ShoppingCategory::Fruit, true],
                ['Oat milk', 'x2', ShoppingCategory::Fresh, false],
                ['Frozen peas', '1 bag', ShoppingCategory::Frozen, false],
                ['Sourdough', '1 loaf', ShoppingCategory::Bakery, false],
                ['Kitchen roll', 'x4', ShoppingCategory::Household, true],
                ['Bin bags', '1 box', ShoppingCategory::Household, false],
                ['Bananas', 'x6', ShoppingCategory::Fruit, false],
                ['Fish fingers', 'x12', ShoppingCategory::Frozen, false],
            ]],
            ['Aldi', Palette::Sky, [
                ['Greek yoghurt', 'x2', ShoppingCategory::Fresh, false],
                ['Carrots', '1kg', ShoppingCategory::Vegetables, false],
                ['Croissants', 'x6', ShoppingCategory::Bakery, true],
            ]],
            ['Costco', Palette::Lilac, [
                ['Washing tabs', 'x60', ShoppingCategory::Household, false],
                ['Coffee beans', '2kg', ShoppingCategory::Fresh, false],
            ]],
            ['DIY', Palette::Coral, [
                ['Masking tape', 'x2', ShoppingCategory::Household, false],
                ['Filler', '1 tub', ShoppingCategory::Household, true],
                ['Sandpaper', 'x5', ShoppingCategory::Household, false],
            ]],
            ['Christmas', Palette::Sun, [
                ['Wrapping paper', 'x4', ShoppingCategory::Household, false],
                ['Mince pies', 'x2', ShoppingCategory::Bakery, false],
            ]],
        ];

        foreach ($lists as $position => [$name, $colour, $items]) {
            $list = ShoppingList::create([
                'household_id' => $household->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'colour' => $colour,
                'position' => $position,
            ]);

            foreach ($items as $itemPosition => [$itemName, $quantity, $category, $done]) {
                $list->items()->create([
                    'name' => $itemName,
                    'quantity' => $quantity,
                    'category' => $category,
                    'completed_at' => $done ? now() : null,
                    'position' => $itemPosition,
                ]);
            }
        }
    }

    /** @param array<string, User> $members */
    private function seedChores(Household $household, array $members): void
    {
        $chores = [
            ['Empty dishwasher', 'noah', ChoreStatus::Today, 'Due today', 'Daily'],
            ['Put the recycling out', 'james', ChoreStatus::Today, 'Before 7am Thu', 'Weekly'],
            ['Hoover upstairs', 'sarah', ChoreStatus::Today, 'Due today', null],
            ['Tidy bedroom', 'mia', ChoreStatus::Today, 'Due today', 'Daily'],
            ['Water the plants', 'mia', ChoreStatus::Upcoming, 'Wed', null],
            ['Change bed sheets', 'sarah', ChoreStatus::Upcoming, 'Sat', 'Fortnightly'],
            ['Wash the car', 'james', ChoreStatus::Upcoming, 'Sun', 'Monthly'],
            ['Feed the cat', 'noah', ChoreStatus::Done, '7:20am', 'Daily'],
            ['Homework check', 'sarah', ChoreStatus::Done, 'Yesterday', 'Weekdays'],
            ['Bins in from kerb', 'noah', ChoreStatus::Recurring, 'Every Thu pm', 'Weekly'],
            ['Clean bathroom', 'sarah', ChoreStatus::Recurring, 'Every Sat', 'Weekly'],
            ['Mow the lawn', 'james', ChoreStatus::Recurring, 'Every other Sun', 'Fortnightly'],
        ];

        foreach ($chores as $position => [$name, $who, $status, $due, $repeat]) {
            $household->chores()->create([
                'assigned_to' => $members[$who]->id,
                'name' => $name,
                'status' => $status,
                'due_label' => $due,
                'repeat_label' => $repeat,
                'position' => $position,
            ]);
        }
    }

    /** @param array<string, User> $members */
    private function seedMeals(Household $household, array $members): void
    {
        $recipes = [
            ['Lemon chicken traybake', '45 min', 'Easy', 0, ['Healthy'], true,
                'One tray, one oven. Chicken thighs, new potatoes, lemon and thyme. Serves four with leftovers for Mia\'s packed lunch.'],
            ['Halloumi wraps', '15 min', 'Easy', 1, ['Quick', 'Vegetarian'], false, null],
            ['Slow cooker chilli', '6 hrs', 'Easy', 3, ['Slow Cooker'], false, null],
            ['Salmon & greens', '25 min', 'Medium', 2, ['Healthy'], false, null],
            ['Fish finger sarnies', '12 min', 'Easy', 4, ['Quick'], false, null],
            ['Veg lasagne', '1 hr 10', 'Medium', 0, ['Vegetarian'], false, null],
            ['BBQ burgers', '35 min', 'Easy', 1, ['BBQ'], false, null],
            ['Roast chicken', '1 hr 45', 'Medium', 2, [], false, null],
            ['Pancakes', '20 min', 'Easy', 3, ['Desserts'], false, null],
            ['Katsu curry', '50 min', 'Medium', 1, [], true, null],
            ['Tomato orzo', '25 min', 'Easy', 0, ['Quick'], true, null],
            ['Beef stew', '3 hrs', 'Slow cooker', 4, ['Slow Cooker'], true, null],
            ['Chickpea curry', '30 min', 'Vegetarian', 2, ['Vegetarian'], true, null],
            ['Sticky toffee pud', '55 min', 'Dessert', 3, ['Desserts'], true, null],
            ['Greek salad', '10 min', 'Quick', 0, ['Quick', 'Healthy'], true, null],
        ];

        $saved = [];

        foreach ($recipes as [$name, $duration, $difficulty, $tint, $tags, $favourite, $description]) {
            $saved[$name] = Recipe::create([
                'household_id' => $household->id,
                'name' => $name,
                'description' => $description,
                'duration_label' => $duration,
                'difficulty' => $difficulty,
                'tags' => $tags,
                'tint' => $tint,
                'is_favourite' => $favourite,
            ]);
        }

        $monday = CarbonImmutable::now()->startOfWeek();

        $plan = [
            ['Lemon chicken traybake', 0, MealSlot::Dinner, 'james', 0],
            ['Halloumi wraps', 0, MealSlot::Lunch, 'sarah', 2],
            ['Slow cooker chilli', 1, MealSlot::Dinner, 'sarah', 1],
            ['Salmon & greens', 2, MealSlot::Dinner, 'james', 3],
            ['Fish finger sarnies', 3, MealSlot::Dinner, 'mia', 0],
            ['Veg lasagne', 4, MealSlot::Dinner, 'sarah', 4],
            ['BBQ burgers', 5, MealSlot::Dinner, 'james', 2],
            ['Roast chicken', 6, MealSlot::Dinner, 'james', 0],
            ['Pancakes', 6, MealSlot::Breakfast, 'noah', 0],
        ];

        foreach ($plan as [$recipe, $dayOffset, $slot, $cook, $missing]) {
            $household->plannedMeals()->create([
                'recipe_id' => $saved[$recipe]->id,
                'cook_id' => $members[$cook]->id,
                'planned_on' => $monday->addDays($dayOffset)->toDateString(),
                'slot' => $slot,
                'missing_ingredients' => $missing,
            ]);
        }
    }

    /**
     * Spread across the whole of the current month so the month grid has something
     * to show. `who` lists the members an event is for — empty means whole house.
     *
     * @param  array<string, User>  $members
     */
    private function seedCalendar(Household $household, array $members): void
    {
        $today = CarbonImmutable::today();

        // [day of month, start, end, title, who, location, colour, all day]
        $events = [
            [2, '19:30', '22:00', 'Dinner at The Ivy', [], 'The Ivy, Clifton', Palette::Coral, false],
            [3, '18:00', '19:30', 'Football practice', ['noah'], 'Beckett Park', Palette::Sky, false],
            [4, '16:00', '17:00', 'Swim club', ['mia'], 'Horfield Leisure Centre', Palette::Sun, false],
            [5, '13:00', '14:00', 'Client call', ['sarah'], null, Palette::Mint, false],
            [6, '09:00', null, 'Recycling collection', [], null, Palette::Mint, false],
            [8, '10:00', '11:00', 'Dentist', ['noah'], 'Whiteladies Dental', Palette::Sky, false],
            [9, '00:00', null, "Grandma's birthday", [], null, Palette::Coral, true],
            [10, '18:00', '19:30', 'Football practice', ['noah'], 'Beckett Park', Palette::Sky, false],
            [11, '16:00', '17:00', 'Swim club', ['mia'], 'Horfield Leisure Centre', Palette::Sun, false],
            [12, '08:45', '12:00', 'Boiler service', [], null, Palette::Lilac, false],
            [13, '19:00', null, 'Council tax due', [], null, Palette::Coral, false],
            [14, '11:00', '15:00', 'Parents evening', ['sarah', 'james'], 'Redland Green School', Palette::Lilac, false],
            [16, '09:00', null, 'Recycling collection', [], null, Palette::Mint, false],
            [17, '18:00', '19:30', 'Football practice', ['noah'], 'Beckett Park', Palette::Sky, false],
            [18, '16:00', '17:00', 'Swim club', ['mia'], 'Horfield Leisure Centre', Palette::Sun, false],
            [19, '09:30', '17:00', 'Sarah in London', ['sarah'], null, Palette::Mint, false],
            [21, '00:00', null, 'Bank holiday', [], null, Palette::Coral, true],
            [22, '14:00', '16:00', 'Mia at Lucy’s', ['mia'], null, Palette::Sun, false],
            [24, '18:00', '19:30', 'Football practice', ['noah'], 'Beckett Park', Palette::Sky, false],
            [26, '19:00', '23:00', 'James five-a-side', ['james'], 'Goals Bristol', Palette::Lilac, false],
            [28, '12:30', '15:00', 'Family lunch', [], 'Nan’s', Palette::Coral, false],
        ];

        foreach ($events as [$dayOfMonth, $start, $end, $title, $who, $location, $colour, $allDay]) {
            $day = $today->startOfMonth()->addDays($dayOfMonth - 1);

            $event = $household->calendarEvents()->create([
                'title' => $title,
                'starts_at' => $day->setTimeFromTimeString($start),
                'ends_at' => $end !== null ? $day->setTimeFromTimeString($end) : null,
                'is_all_day' => $allDay,
                'location' => $location,
                'colour' => $colour,
            ]);

            $event->attendees()->sync(array_map(fn (string $key) => $members[$key]->id, $who));
        }
    }

    private function seedBudget(Household $household): void
    {
        $month = CarbonImmutable::today()->startOfMonth();

        // [label, colour, budgeted £, spent £] — the four categories from the design.
        $categories = [
            ['Food & shopping', Palette::Coral, 900, 412],
            ['Utilities', Palette::Mint, 600, 238],
            ['Subscriptions', Palette::Sun, 150, 86],
            ['Fuel', Palette::Sky, 500, 124],
        ];

        foreach ($categories as $position => [$label, $colour, $budgeted, $spent]) {
            $household->budgetCategories()->create([
                'label' => $label,
                'colour' => $colour,
                'budgeted_pence' => $budgeted * 100,
                'spent_pence' => $spent * 100,
                'month' => $month->toDateString(),
                'position' => $position,
            ]);
        }
    }
}
