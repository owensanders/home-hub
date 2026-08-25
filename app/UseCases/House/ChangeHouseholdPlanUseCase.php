<?php

declare(strict_types=1);

namespace App\UseCases\House;

use App\Models\Household;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ChangeHouseholdPlanUseCase
{
    /**
     * Free means "no subscription" — downgrading cancels it immediately (no
     * grace period, since the UI has no concept of "cancels at period end"),
     * and there is nothing to swap to when the household was never subscribed.
     *
     * @return string|null A Stripe Checkout URL to redirect to, or null when the change was applied in-app.
     */
    public function execute(Household $household, string $slug, string $cycle, string $successUrl, string $cancelUrl): ?string
    {
        if ($slug === 'free') {
            $household->subscription('default')?->cancelNow();

            return null;
        }

        $stripePrice = config("plans.{$slug}.stripe_price.{$cycle}");

        if (blank($stripePrice)) {
            Log::error("No Stripe price configured for plan [{$slug}/{$cycle}].");

            throw new RuntimeException('This plan is not available yet.');
        }

        if ($household->subscribed('default')) {
            $household->subscription('default')->swap($stripePrice);

            return null;
        }

        $checkout = $household->newSubscription('default', $stripePrice)->checkout([
            'success_url' => $successUrl.(str_contains($successUrl, '?') ? '&' : '?').'plan_checkout=success',
            'cancel_url' => $cancelUrl.(str_contains($cancelUrl, '?') ? '&' : '?').'plan_checkout=cancelled',
        ]);

        return $checkout->asStripeCheckoutSession()->url;
    }
}
