<?php

declare(strict_types=1);

namespace App\Data;

use Spatie\LaravelData\Data;

class PlanData extends Data
{
    /**
     * @param  array{monthly: float, annual: float}  $price
     * @param  list<PlanFeatureData>  $features
     */
    public function __construct(
        public string $slug,
        public string $name,
        public ?string $tag,
        public bool $highlighted,
        public array $price,
        public string $sub,
        public string $cta,
        public string $note,
        public array $features,
        public bool $current,
    ) {}

    /** @return list<self> */
    public static function catalog(?string $currentSlug = null): array
    {
        $plans = [];

        foreach (config('plans') as $slug => $plan) {
            $plans[] = new self(
                slug: $slug,
                name: $plan['name'],
                tag: $plan['tag'],
                highlighted: $plan['highlighted'],
                price: $plan['price'],
                sub: $plan['sub'],
                cta: $plan['cta'],
                note: $plan['note'],
                features: array_map(
                    fn (array $feature) => new PlanFeatureData($feature['text'], $feature['included']),
                    $plan['features'],
                ),
                current: $slug === $currentSlug,
            );
        }

        return $plans;
    }
}
