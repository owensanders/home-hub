<script setup lang="ts">
import { formatPlanPrice } from '@/lib/househub';
import type { Plan } from '@/types/househub';
import { Head, Link, router } from '@inertiajs/vue3';
import { House } from 'lucide-vue-next';

const props = defineProps<{
    plan: Plan;
    isOwner: boolean;
    billingConfigured: boolean;
}>();

function subscribe(): void {
    router.post(route('billing.subscribe'));
}
</script>

<template>
    <Head title="Subscribe to continue" />

    <div class="flex min-h-svh items-center justify-center bg-hh-bg p-4 text-hh-ink transition-colors">
        <div class="w-full max-w-[440px] animate-hh-rise rounded-3xl border border-hh-line bg-hh-shell p-8 shadow-hh">
            <div class="flex items-center gap-2.5">
                <div class="grid h-9 w-9 flex-none place-items-center rounded-xl bg-hh-coral text-white">
                    <House class="h-[19px] w-[19px]" :stroke-width="2.5" />
                </div>
                <span class="text-[17px] font-extrabold tracking-tight">HouseHub</span>
            </div>

            <h1 class="mt-6 text-[26px] font-black leading-tight tracking-[-0.03em]">Your free month has ended</h1>

            <template v-if="!props.billingConfigured">
                <p class="mt-2.5 text-[14.5px] leading-relaxed text-hh-ink2">
                    Billing isn't set up yet on this household — please contact support to keep using HouseHub.
                </p>
            </template>
            <template v-else-if="props.isOwner">
                <p class="mt-2.5 text-[14.5px] leading-relaxed text-hh-ink2">
                    Subscribe for {{ formatPlanPrice(props.plan.price) }}/month to keep everyone in the house up and running.
                </p>
                <button
                    type="button"
                    class="mt-6 flex h-[50px] w-full items-center justify-center rounded-[14px] bg-hh-coral text-[15px] font-bold text-white transition hover:-translate-y-0.5"
                    @click="subscribe"
                >
                    Subscribe now
                </button>
            </template>
            <template v-else>
                <p class="mt-2.5 text-[14.5px] leading-relaxed text-hh-ink2">
                    Ask the household owner to subscribe for {{ formatPlanPrice(props.plan.price) }}/month to keep everyone up and running.
                </p>
            </template>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="mt-4 flex h-[44px] w-full items-center justify-center rounded-[14px] bg-hh-soft text-[14px] font-semibold text-hh-ink2 transition-colors hover:bg-hh-line"
            >
                Log out
            </Link>
        </div>
    </div>
</template>
