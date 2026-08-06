<script setup lang="ts">
import HouseHubLayout from '@/layouts/HouseHubLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const ICONS: Record<string, string> = {
    calendar: '📅',
    budget: '💰',
    house: '🏡',
    documents: '📂',
    maintenance: '🔧',
};

const props = defineProps<{ screen: string }>();

const title = computed(() => props.screen.charAt(0).toUpperCase() + props.screen.slice(1));
</script>

<template>
    <HouseHubLayout :title="title">
        <div class="flex h-[520px] animate-hh-rise flex-col items-center justify-center gap-3.5 text-center">
            <div class="grid h-[84px] w-[84px] place-items-center rounded-[26px] bg-hh-soft text-3xl">
                {{ ICONS[screen] ?? '🏠' }}
            </div>
            <div class="font-serif text-[28px]">{{ title }}</div>
            <p class="m-0 max-w-[340px] text-sm leading-relaxed text-hh-ink3">
                Not designed yet. Dashboard, Meal Planner, Shopping Lists and Chores are the four screens in this pass.
            </p>
            <Link
                :href="route('dashboard')"
                class="mt-1.5 flex h-11 items-center rounded-[13px] bg-hh-coral px-[22px] text-[13.5px] font-bold text-white"
            >
                Back to dashboard
            </Link>
        </div>
    </HouseHubLayout>
</template>
