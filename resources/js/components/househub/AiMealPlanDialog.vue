<script setup lang="ts">
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import type { SharedData } from '@/types';
import type { AiMealSuggestion, PlannerDay, ShoppingList } from '@/types/househub';
import { router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    open: boolean;
    aiConfigured: boolean;
    aiAvailableFrom: string | null;
    days: PlannerDay[];
    shoppingLists: ShoppingList[];
}>();

const emit = defineEmits<{ 'update:open': [value: boolean] }>();

const DIET_OPTIONS = ['Vegetarian', 'Vegan', 'Pescatarian', 'Gluten free', 'Dairy free', 'Nut free'];
const GOAL_OPTIONS = ['Eat healthier', 'Spend less', 'Eat more veg', 'Quick weeknights', 'Batch cook'];

const page = usePage<SharedData>();

const stage = ref<'form' | 'loading' | 'result'>('form');
const pool = ref<AiMealSuggestion[]>([]);
const seed = ref(0);
const selectedDays = ref<number[]>([]);
const targetListId = ref<number | null>(null);
const accepting = ref(false);
const acceptIndex = ref(0);

const form = useForm({
    people: 4,
    budget: '' as string | number,
    diets: [] as string[],
    avoid: '',
    goals: [] as string[],
});

function freeDayIndexes(): number[] {
    const free = props.days.map((day, index) => ({ index, hasDinner: day.meals.some((m) => m.slotKey === 'dinner') })).filter((d) => !d.hasDinner);

    return free.length ? free.map((d) => d.index) : props.days.map((_, index) => index);
}

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) {
            return;
        }

        stage.value = 'form';
        pool.value = [];
        seed.value = 0;
        selectedDays.value = freeDayIndexes();
        targetListId.value = props.shoppingLists[0]?.id ?? null;
        form.clearErrors();
    },
);

function toggleDay(index: number): void {
    selectedDays.value = selectedDays.value.includes(index) ? selectedDays.value.filter((i) => i !== index) : [...selectedDays.value, index];
}

const assignment = computed(() => {
    if (pool.value.length === 0) {
        return [];
    }

    return [...selectedDays.value]
        .sort((a, b) => a - b)
        .map((dayIndex, position) => ({
            day: props.days[dayIndex],
            suggestion: pool.value[(position + seed.value) % pool.value.length],
        }))
        .filter((entry) => entry.day !== undefined);
});

function generate(): void {
    stage.value = 'loading';

    form.transform((data) => ({
        ...data,
        budget: data.budget === '' ? null : data.budget,
    })).post(route('meals.aiPlan'), {
        preserveScroll: true,
        onSuccess: () => {
            pool.value = page.props.flash?.aiMeals ?? [];
            stage.value = pool.value.length > 0 ? 'result' : 'form';
        },
        onError: () => {
            stage.value = 'form';
        },
    });
}

function regenerate(): void {
    seed.value++;
}

function acceptNext(entries: { day: PlannerDay; suggestion: AiMealSuggestion }[]): void {
    if (acceptIndex.value >= entries.length) {
        accepting.value = false;
        emit('update:open', false);

        return;
    }

    const entry = entries[acceptIndex.value];

    router.post(
        route('meals.store'),
        {
            recipe_id: null,
            new_recipe_name: entry.suggestion.name,
            new_recipe_description: entry.suggestion.description,
            new_recipe_duration_label: entry.suggestion.duration_label,
            new_recipe_difficulty: entry.suggestion.difficulty,
            new_recipe_tags: [],
            new_recipe_ingredients: entry.suggestion.ingredients.map((i) => ({ name: i.name, quantity: i.quantity ?? '' })),
            new_recipe_tint: acceptIndex.value % 5,
            new_recipe_is_favourite: false,
            new_recipe_shopping_list_id: targetListId.value,
            planned_on: entry.day.date,
            slot: 'dinner',
            cook_id: null,
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                acceptIndex.value++;
                acceptNext(entries);
            },
        },
    );
}

function accept(): void {
    if (assignment.value.length === 0) {
        return;
    }

    accepting.value = true;
    acceptIndex.value = 0;
    acceptNext(assignment.value);
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="max-w-lg rounded-[22px] border-hh-line bg-hh-card text-hh-ink sm:rounded-[22px]">
            <DialogHeader>
                <DialogTitle class="text-[16px] font-extrabold tracking-tight">
                    {{ stage === 'result' ? 'Your week, ready to go' : 'Plan my week' }}
                </DialogTitle>
            </DialogHeader>

            <div v-if="!aiConfigured" class="flex flex-col gap-2 text-[13.5px] text-hh-ink2">
                <p>AI meal planning isn't set up yet — ask the household owner to add an OpenAI API key.</p>
                <button type="button" class="hh-btn w-auto self-start bg-hh-soft text-hh-ink" @click="emit('update:open', false)">Close</button>
            </div>

            <div v-else-if="aiAvailableFrom" class="flex flex-col gap-2 text-[13.5px] text-hh-ink2">
                <p>Your household can generate one AI meal plan a week. Next one available from {{ aiAvailableFrom }}.</p>
                <button type="button" class="hh-btn w-auto self-start bg-hh-soft text-hh-ink" @click="emit('update:open', false)">Close</button>
            </div>

            <form v-else-if="stage === 'form'" class="flex flex-col gap-4" @submit.prevent="generate">
                <div class="flex flex-col gap-1.5">
                    <span class="hh-label">Which days?</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="(day, index) in days"
                            :key="day.date"
                            type="button"
                            class="rounded-lg border px-2.5 py-1.5 text-[12.5px] font-semibold"
                            :class="selectedDays.includes(index) ? 'border-hh-ink bg-hh-ink text-hh-shell' : 'border-hh-line bg-hh-card text-hh-ink2'"
                            @click="toggleDay(index)"
                        >
                            {{ day.dayLabel }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="flex flex-col gap-1.5">
                        <label for="ai_people" class="hh-label">People</label>
                        <input id="ai_people" v-model.number="form.people" type="number" min="1" max="12" class="hh-input" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label for="ai_budget" class="hh-label">Weekly budget (£)</label>
                        <input id="ai_budget" v-model="form.budget" type="text" inputmode="decimal" class="hh-input" placeholder="Optional" />
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <span class="hh-label">Diets</span>
                    <div class="flex flex-wrap gap-x-3 gap-y-1.5">
                        <label v-for="diet in DIET_OPTIONS" :key="diet" class="flex items-center gap-1.5 text-[12.5px] text-hh-ink2">
                            <input v-model="form.diets" type="checkbox" :value="diet" class="h-3.5 w-3.5 accent-hh-coral" />
                            {{ diet }}
                        </label>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="ai_avoid" class="hh-label">Avoid</label>
                    <input id="ai_avoid" v-model="form.avoid" type="text" class="hh-input" placeholder="e.g. mushrooms, shellfish" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <span class="hh-label">Goals</span>
                    <div class="flex flex-wrap gap-x-3 gap-y-1.5">
                        <label v-for="goal in GOAL_OPTIONS" :key="goal" class="flex items-center gap-1.5 text-[12.5px] text-hh-ink2">
                            <input v-model="form.goals" type="checkbox" :value="goal" class="h-3.5 w-3.5 accent-hh-coral" />
                            {{ goal }}
                        </label>
                    </div>
                </div>

                <p v-if="page.props.errors.ai" class="text-[12.5px] text-hh-coral">{{ page.props.errors.ai }}</p>

                <div class="flex items-center gap-2">
                    <button type="submit" class="hh-btn w-auto bg-hh-coral text-white" :disabled="selectedDays.length === 0 || form.processing">
                        Generate my week
                    </button>
                    <button type="button" class="hh-btn w-auto bg-hh-soft text-hh-ink" @click="emit('update:open', false)">Cancel</button>
                </div>
            </form>

            <div v-else-if="stage === 'loading'" class="flex flex-col items-center gap-3 py-8 text-[13.5px] text-hh-ink2">
                <span class="h-6 w-6 animate-spin rounded-full border-2 border-hh-line border-t-hh-coral"></span>
                Thinking about your week…
            </div>

            <div v-else class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <div v-for="entry in assignment" :key="entry.day.date" class="rounded-2xl border border-hh-line bg-hh-panel p-3">
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-[12px] font-extrabold">{{ entry.day.dayLabel }}</span>
                            <span class="text-[15px] font-extrabold tracking-tight">{{ entry.suggestion.name }}</span>
                        </div>
                        <p class="mt-1 text-[12.5px] text-hh-ink2">{{ entry.suggestion.description }}</p>
                        <div class="mt-1.5 flex flex-wrap gap-1.5">
                            <span class="rounded-md bg-hh-soft px-1.5 py-[3px] font-mono text-[10.5px] text-hh-ink2">
                                {{ entry.suggestion.duration_label }}
                            </span>
                            <span class="rounded-md bg-hh-soft px-1.5 py-[3px] text-[10.5px] font-semibold text-hh-ink2">
                                {{ entry.suggestion.difficulty }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="shoppingLists.length > 0" class="flex flex-col gap-1.5 border-t border-hh-line pt-4">
                    <label for="ai_shopping_list" class="hh-label">Add missing ingredients to</label>
                    <select id="ai_shopping_list" v-model="targetListId" class="hh-input">
                        <option :value="null">Don't add</option>
                        <option v-for="list in shoppingLists" :key="list.id" :value="list.id">{{ list.name }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" class="hh-btn w-auto bg-hh-coral text-white" :disabled="accepting" @click="accept">
                        {{ accepting ? `Adding ${acceptIndex + 1} of ${assignment.length}…` : 'Accept this week' }}
                    </button>
                    <button type="button" class="hh-btn w-auto bg-hh-soft text-hh-ink" :disabled="accepting" @click="regenerate">Regenerate</button>
                    <button
                        type="button"
                        class="hh-btn ml-auto w-auto bg-hh-soft text-hh-ink"
                        :disabled="accepting"
                        @click="emit('update:open', false)"
                    >
                        Close
                    </button>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>
