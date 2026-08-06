<script setup lang="ts">
import HouseHubLayout from '@/layouts/HouseHubLayout.vue';
import { dial, tickStyle } from '@/lib/househub';
import type { ChoreColumn, ChoreStatus, MemberScore } from '@/types/househub';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    columns: ChoreColumn[];
    scores: MemberScore[];
}>();

const draggingId = ref<number | null>(null);

function startDrag(event: DragEvent, id: number): void {
    draggingId.value = id;

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
}

function drop(status: ChoreStatus): void {
    const chore = draggingId.value;
    draggingId.value = null;

    if (chore === null) {
        return;
    }

    router.patch(route('chores.move', { chore }), { status }, { preserveScroll: true });
}

function toggle(id: number): void {
    router.patch(route('chores.toggle', { chore: id }), {}, { preserveScroll: true });
}
</script>

<template>
    <HouseHubLayout title="Chores" subtitle="This week">
        <div class="flex animate-hh-rise flex-col gap-[18px]">
            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="score in scores"
                    :key="score.member.id"
                    class="flex items-center gap-3.5 rounded-[20px] border border-hh-line bg-hh-card px-[18px] py-4"
                >
                    <div
                        class="grid h-[60px] w-[60px] flex-none place-items-center rounded-full transition-[background] duration-500"
                        :style="{ background: dial(score.percentage, score.member.colour) }"
                    >
                        <div class="grid h-[46px] w-[46px] place-items-center rounded-full bg-hh-card text-xs font-extrabold">
                            {{ score.member.initials }}
                        </div>
                    </div>
                    <div>
                        <div class="text-[14.5px] font-extrabold">{{ score.member.name.split(' ')[0] }}</div>
                        <div class="mt-[3px] text-[12.5px] text-hh-ink3">{{ score.done }} of {{ score.total }} this week</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    v-for="column in columns"
                    :key="column.status"
                    class="flex min-h-[420px] flex-col gap-2.5 rounded-[20px] border border-dashed p-3.5 transition"
                    :style="{
                        background: draggingId !== null ? 'var(--hh-sunk)' : 'var(--hh-panel)',
                        borderColor: draggingId !== null ? 'var(--hh-line)' : 'transparent',
                    }"
                    @dragover.prevent
                    @drop.prevent="drop(column.status)"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-[13.5px] font-extrabold">{{ column.title }}</span>
                        <span class="rounded-md bg-hh-soft px-1.5 py-0.5 font-mono text-[11px] text-hh-ink2">{{ column.count }}</span>
                    </div>

                    <div
                        v-for="chore in column.items"
                        :key="chore.id"
                        draggable="true"
                        class="cursor-grab rounded-2xl border border-hh-line bg-hh-card px-3.5 py-3 transition hover:-translate-y-0.5 hover:shadow-hh"
                        :style="{ opacity: draggingId === chore.id ? 0.4 : 1 }"
                        @dragstart="startDrag($event, chore.id)"
                        @dragend="draggingId = null"
                    >
                        <div class="flex items-start gap-2.5">
                            <!-- Ticking lives on the box alone so it can't fire at the end of a drag. -->
                            <button
                                type="button"
                                :aria-label="chore.done ? `Mark ${chore.name} as not done` : `Mark ${chore.name} as done`"
                                class="mt-px grid h-[19px] w-[19px] flex-none place-items-center rounded-full border-[1.5px] text-[11px] text-[#0E1A2B]"
                                :style="tickStyle(chore.done)"
                                @click="toggle(chore.id)"
                            >
                                {{ chore.done ? '✓' : '' }}
                            </button>
                            <span
                                class="flex-1 text-sm font-medium leading-snug"
                                :class="chore.done ? 'text-hh-ink3 line-through' : 'text-hh-ink'"
                            >
                                {{ chore.name }}
                            </span>
                        </div>
                        <div class="mt-3 flex items-center gap-1.5">
                            <span
                                v-if="chore.assignee"
                                class="grid h-6 w-6 place-items-center rounded-lg text-[9.5px] font-extrabold text-[#0E1A2B]"
                                :style="{ background: chore.assignee.colour }"
                            >
                                {{ chore.assignee.initials }}
                            </span>
                            <span class="text-[11.5px] text-hh-ink3">{{ chore.dueLabel }}</span>
                            <span
                                v-if="chore.repeatLabel"
                                class="ml-auto rounded-[7px] bg-hh-soft px-2 py-[3px] text-[11px] text-hh-ink2"
                            >
                                {{ chore.repeatLabel }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HouseHubLayout>
</template>
