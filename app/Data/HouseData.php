<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class HouseData extends Data
{
    /**
     * @param  list<StatData>  $houseStats
     * @param  list<array{value: string, label: string}>  $roleOptions
     * @param  list<HouseMemberData>  $members
     * @param  list<RoleSummaryData>  $roles
     * @param  list<HouseSettingData>  $settings
     */
    public function __construct(
        public string $houseName,
        public string $houseAddress,
        public string $houseCreated,
        public array $houseStats,
        public int $memberCount,
        public array $roleOptions,
        public array $members,
        public array $roles,
        public array $settings,
    ) {}
}
