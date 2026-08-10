<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Models\Household;

class ToggleHouseholdSettingUseCase
{
    public function __construct(private readonly HouseholdRepositoryInterface $households) {}

    public function execute(Household $household, string $key): Household
    {
        $settings = $household->settings ?? [];
        $settings[$key] = ! ($settings[$key] ?? false);

        return $this->households->updateSettings($household, $settings);
    }
}
