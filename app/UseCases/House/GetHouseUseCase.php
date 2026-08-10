<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Contracts\Repositories\HouseholdRepositoryInterface;
use App\Data\HouseData;
use App\Data\HouseMemberData;
use App\Data\HouseSettingData;
use App\Data\RoleSummaryData;
use App\Data\StatData;
use App\Enums\HouseholdRole;
use App\Models\Household;
use App\Models\User;

class GetHouseUseCase
{
    /** @var list<array{key: string, title: string, body: string}> */
    private const SETTINGS = [
        ['key' => 'approve', 'title' => 'Approve new members', 'body' => 'An owner must confirm before an invite becomes active.'],
        ['key' => 'kidsSpend', 'title' => 'Hide budget from under-18s', 'body' => 'Teens and children never see household money.'],
        ['key' => 'choreNudge', 'title' => 'Morning chore nudge', 'body' => 'A single 7:30am reminder, not one per task.'],
        ['key' => 'guest', 'title' => 'Guest access', 'body' => 'Time-limited logins for sitters, cleaners or family staying over.'],
    ];

    public function __construct(
        private readonly HouseholdRepositoryInterface $households,
    ) {}

    public function execute(Household $household, int $viewerId): HouseData
    {
        $members = $this->households->members($household);
        $activeCount = $members->where('pending', false)->count();
        $pendingCount = $members->where('pending', true)->count();
        $settings = $household->settings ?? [];

        return new HouseData(
            houseName: $household->name,
            houseAddress: $household->address ?? '',
            houseCreated: $household->created_at->format('F Y'),
            houseStats: [
                new StatData('Members', (string) $activeCount),
                new StatData('Invites out', (string) $pendingCount),
                new StatData('Plan', 'Home'),
            ],
            memberCount: $members->count(),
            roleOptions: array_map(
                fn (HouseholdRole $role) => ['value' => $role->value, 'label' => $role->label()],
                HouseholdRole::cases(),
            ),
            members: $members->map(fn (User $user) => HouseMemberData::fromModel($user, $viewerId))->values()->all(),
            roles: array_map(
                fn (HouseholdRole $role) => new RoleSummaryData(
                    name: $role->label(),
                    colour: $role->colour()->cssVar(),
                    body: $role->description(),
                    count: $members->where('role', $role)->count(),
                ),
                HouseholdRole::cases(),
            ),
            settings: array_map(
                fn (array $setting) => new HouseSettingData(
                    key: $setting['key'],
                    title: $setting['title'],
                    body: $setting['body'],
                    enabled: (bool) ($settings[$setting['key']] ?? false),
                ),
                self::SETTINGS,
            ),
        );
    }
}
