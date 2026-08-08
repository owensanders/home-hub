<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { House } from 'lucide-vue-next';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex min-h-svh items-center justify-center bg-hh-bg p-3 text-hh-ink transition-colors sm:p-7">
        <div class="w-full max-w-[420px] animate-hh-rise rounded-[22px] border border-hh-line bg-hh-card p-[22px] shadow-hh sm:p-7">
            <Link :href="route('home')" class="mb-6 flex items-center gap-3">
                <div class="grid h-9 w-9 flex-none place-items-center rounded-xl bg-hh-coral text-white">
                    <House class="h-[19px] w-[19px]" :stroke-width="2.5" />
                </div>
                <div class="flex flex-col gap-px">
                    <span class="text-[16.5px] font-extrabold tracking-tight">HouseHub</span>
                    <span class="text-[11px] text-hh-ink3">The home, organised.</span>
                </div>
            </Link>

            <h1 class="text-[19px] font-extrabold tracking-tight">{{ title }}</h1>
            <p v-if="description" class="mt-1 text-[13px] text-hh-ink3">{{ description }}</p>

            <div class="mt-6">
                <slot />
            </div>
        </div>
    </div>
</template>
