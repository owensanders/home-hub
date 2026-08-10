<?php

declare(strict_types=1);

namespace App\Enums;

/** The four household roles from the design, and what each is allowed to see/do. */
enum HouseholdRole: string
{
    case Owner = 'owner';
    case Adult = 'adult';
    case Teen = 'teen';
    case Child = 'child';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Adult => 'Adult',
            self::Teen => 'Teen',
            self::Child => 'Child',
        };
    }

    public function colour(): Palette
    {
        return match ($this) {
            self::Owner => Palette::Coral,
            self::Adult => Palette::Mint,
            self::Teen => Palette::Sun,
            self::Child => Palette::Sky,
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Owner => 'Full access, billing, and the only role that can delete the household.',
            self::Adult => 'Everything except billing and removing the owner. Sees budget and documents.',
            self::Teen => 'Meals, calendar, lists and chores. No money or documents.',
            self::Child => 'Simplified view: their own chores and the family calendar.',
        };
    }
}
