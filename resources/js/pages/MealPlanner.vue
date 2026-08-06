<script setup lang="ts">
import HouseHubLayout from '@/layouts/HouseHubLayout.vue';
import { tint } from '@/lib/househub';
import type { PlannerDay, Recipe } from '@/types/househub';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    weekOf: string;
    days: PlannerDay[];
    library: Recipe[];
    recipeCount: number;
}>();

const draggingId = ref<number | null>(null);
const dropTarget = ref<string | null>(null);

function startDrag(event: DragEvent, id: number): void {
    draggingId.value = id;

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
}

function endDrag(): void {
    draggingId.value = null;
    dropTarget.value = null;
}

function drop(date: string): void {
    const meal = draggingId.value;
    endDrag();

    if (meal === null) {
        return;
    }

    router.patch(
        route('meals.reschedule', { meal }),
        { planned_on: date },
        { preserveScroll: true },
    );
}
</script>

<template>
    <HouseHubLayout title="Meal Planner" :subtitle="`Week of ${weekOf}`">
        <div class="flex animate-hh-rise flex-col gap-4">
            <div class="text-[13px] text-hh-ink3">Drag any meal card onto another day to reschedule.</div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                <div
                    v-for="day in days"
                    :key="day.date"
                    class="flex min-h-[400px] flex-col gap-2.5 rounded-[18px] border border-dashed p-3 transition"
                    :style="{
                        borderColor: dropTarget === day.date ? 'var(--hh-mint)' : 'var(--hh-line)',
                        background: dropTarget === day.date ? 'var(--hh-soft)' : day.isToday ? 'var(--hh-sunk)' : 'transparent',
                    }"
                    @dragover.prevent="dropTarget = day.date"
                    @dragleave="dropTarget === day.date && (dropTarget = null)"
                    @drop.prevent="drop(day.date)"
                >
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-[13px] font-extrabold">{{ day.dayLabel }}</span>
                        <span class="font-mono text-[11px] text-hh-ink3">{{ day.dateLabel }}</span>
                    </div>

                    <div
                        v-for="meal in day.meals"
                        :key="meal.id"
                        draggable="true"
                        class="cursor-grab overflow-hidden rounded-2xl border border-hh-line bg-hh-card transition hover:-translate-y-[3px] hover:shadow-hh"
                        :style="{ opacity: draggingId === meal.id ? 0.35 : 1 }"
                        @dragstart="startDrag($event, meal.id)"
                        @dragend="endDrag"
                    >
                        <div class="relative flex h-[76px] items-end px-3 py-2.5" :style="{ background: tint(meal.tint) }">
                            <span class="absolute right-2.5 top-2 text-[10px] font-extrabold uppercase tracking-[0.08em] text-hh-ontint opacity-60">
                                {{ meal.slot }}
                            </span>
                            <span class="text-[17px] font-extrabold leading-[1.15] tracking-[-0.02em] text-hh-ontint">{{ meal.name }}</span>
                        </div>
                        <div class="flex flex-col gap-[7px] px-3 py-2.5">
                            <div class="flex flex-wrap gap-1.5">
                                <span class="rounded-md bg-hh-soft px-1.5 py-[3px] font-mono text-[10.5px] text-hh-ink2">
                                    {{ meal.duration }}
                                </span>
                                <span class="rounded-md bg-hh-soft px-1.5 py-[3px] text-[10.5px] font-semibold text-hh-ink2">
                                    {{ meal.difficulty }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span
                                    v-if="meal.cook"
                                    class="grid h-[21px] w-[21px] place-items-center rounded-[7px] text-[9px] font-extrabold text-[#0E1A2B]"
                                    :style="{ background: meal.cook.colour }"
                                >
                                    {{ meal.cook.initials }}
                                </span>
                                <span class="text-[11px] text-hh-ink3">{{ meal.missingLabel }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-[22px] border border-hh-line bg-hh-panel p-[22px]">
                <div class="mb-3.5 flex items-baseline gap-2.5">
                    <h3 class="text-[15px] font-extrabold">Recipe library</h3>
                    <span class="text-xs text-hh-ink3">Favourites · {{ recipeCount }} saved</span>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                    <div
                        v-for="recipe in library"
                        :key="recipe.id"
                        class="cursor-pointer rounded-2xl border border-hh-line bg-hh-card p-3.5 transition hover:-translate-y-[3px]"
                    >
                        <div class="mb-2.5 h-[58px] rounded-xl" :style="{ background: tint(recipe.tint) }"></div>
                        <div class="text-[13.5px] font-bold leading-tight">{{ recipe.name }}</div>
                        <div class="mt-1.5 text-[11.5px] text-hh-ink3">{{ recipe.meta }}</div>
                    </div>
                </div>
            </div>
        </div>
    </HouseHubLayout>
</template>
