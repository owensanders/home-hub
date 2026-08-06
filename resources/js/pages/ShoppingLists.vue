<script setup lang="ts">
import HouseHubLayout from '@/layouts/HouseHubLayout.vue';
import { tickStyle } from '@/lib/househub';
import type { ShoppingGroup, ShoppingList } from '@/types/househub';
import { Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    lists: ShoppingList[];
    active: ShoppingList | null;
    groups: ShoppingGroup[];
}>();

const draft = useForm({ name: '' });

function addItem(): void {
    if (!props.active || draft.name.trim() === '') {
        return;
    }

    draft.post(route('shopping.items.store', { slug: props.active.slug }), {
        preserveScroll: true,
        onSuccess: () => draft.reset(),
    });
}

function toggleItem(id: number): void {
    router.patch(route('shopping.items.toggle', { item: id }), {}, { preserveScroll: true });
}
</script>

<template>
    <HouseHubLayout title="Shopping Lists" :subtitle="`${lists.length} lists`">
        <div class="flex animate-hh-rise flex-col items-start gap-5 lg:flex-row">
            <div class="flex w-full flex-none flex-col gap-1.5 lg:w-[232px]">
                <Link
                    v-for="list in lists"
                    :key="list.id"
                    :href="route('shopping.index', { slug: list.slug })"
                    class="flex min-h-[48px] items-center gap-2.5 rounded-[14px] border px-3.5 text-left transition hover:bg-hh-card"
                    :class="active?.id === list.id ? 'border-hh-line bg-hh-card' : 'border-transparent bg-transparent'"
                >
                    <span class="h-2.5 w-2.5 flex-none rounded-[3px]" :style="{ background: list.colour }"></span>
                    <span class="flex-1 text-sm font-semibold">{{ list.name }}</span>
                    <span class="font-mono text-[11.5px] text-hh-ink3">{{ list.remaining }}/{{ list.total }}</span>
                </Link>
            </div>

            <div v-if="active" class="w-full min-w-0 flex-1 rounded-[22px] border border-hh-line bg-hh-card p-[22px]">
                <div class="flex items-center gap-3 border-b border-hh-line pb-4">
                    <div>
                        <h3 class="text-lg font-extrabold tracking-[-0.02em]">{{ active.name }}</h3>
                        <div class="mt-1 text-[13px] text-hh-ink3">{{ active.remaining }} items left</div>
                    </div>
                </div>

                <form class="my-4 flex gap-2" @submit.prevent="addItem">
                    <input
                        v-model="draft.name"
                        placeholder="Add to this list…"
                        aria-label="Add to this list"
                        class="h-12 flex-1 rounded-[14px] border border-hh-line bg-hh-sunk px-4 text-[14.5px] text-hh-ink placeholder:text-hh-ink3 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-hh-coral"
                    />
                    <button
                        type="submit"
                        :disabled="draft.processing"
                        class="h-12 rounded-[14px] bg-hh-coral px-[22px] text-sm font-bold text-white transition hover:-translate-y-0.5 disabled:opacity-50"
                    >
                        Add
                    </button>
                </form>
                <p v-if="draft.errors.name" class="-mt-2 mb-3 text-[13px] text-hh-coral">{{ draft.errors.name }}</p>

                <div class="grid grid-cols-1 gap-x-7 gap-y-2.5 md:grid-cols-2">
                    <div v-for="group in groups" :key="group.label">
                        <div class="mb-1 mt-2 text-[11px] font-bold uppercase tracking-[0.09em] text-hh-ink3">{{ group.label }}</div>
                        <button
                            v-for="item in group.items"
                            :key="item.id"
                            type="button"
                            class="flex min-h-[46px] w-full items-center gap-3 rounded-[13px] px-2.5 text-left transition-colors hover:bg-hh-soft"
                            @click="toggleItem(item.id)"
                        >
                            <span
                                class="grid h-[22px] w-[22px] flex-none place-items-center rounded-lg border-[1.5px] text-xs text-[#0E1A2B] transition"
                                :style="tickStyle(item.done)"
                            >
                                {{ item.done ? '✓' : '' }}
                            </span>
                            <span class="flex-1 text-[14.5px]" :class="item.done ? 'text-hh-ink3 line-through' : 'text-hh-ink'">
                                {{ item.name }}
                            </span>
                            <span class="font-mono text-[11.5px] text-hh-ink3">{{ item.quantity }}</span>
                        </button>
                    </div>
                </div>

                <p v-if="groups.length === 0" class="py-10 text-center text-sm text-hh-ink3">This list is empty.</p>
            </div>
        </div>
    </HouseHubLayout>
</template>
