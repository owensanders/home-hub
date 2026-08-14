<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useHouseholdRole } from '@/composables/useHouseholdRole';
import HouseHubLayout from '@/layouts/HouseHubLayout.vue';
import { tint } from '@/lib/househub';
import type { Member, PlannedMeal, PlannerDay, Recipe } from '@/types/househub';
import { Link, router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{
    weekOf: string;
    days: PlannerDay[];
    library: Recipe[];
    recipeCount: number;
    recipes: Recipe[];
    members: Member[];
}>();

const SLOTS = ['breakfast', 'lunch', 'dinner'];

const { canManage } = useHouseholdRole();

const draggingId = ref<number | null>(null);
const dropTarget = ref<string | null>(null);

const open = ref(false);
const editing = ref<PlannedMeal | null>(null);

const form = useForm({
    recipe_id: null as number | null,
    new_recipe_name: '',
    new_recipe_description: '',
    planned_on: '',
    slot: 'dinner',
    cook_id: null as number | null,
});

const dialogTitle = computed(() => (editing.value ? 'Edit meal' : 'New meal'));

function openNew(date: string): void {
    editing.value = null;
    form.clearErrors();
    form.defaults({
        recipe_id: null,
        new_recipe_name: '',
        new_recipe_description: '',
        planned_on: date,
        slot: 'dinner',
        cook_id: null,
    });
    form.reset();
    open.value = true;
}

function openEdit(meal: PlannedMeal, date: string): void {
    editing.value = meal;
    form.clearErrors();
    form.defaults({
        recipe_id: meal.recipeId,
        new_recipe_name: '',
        new_recipe_description: '',
        planned_on: date,
        slot: meal.slotKey,
        cook_id: meal.cook?.id ?? null,
    });
    form.reset();
    open.value = true;
}

function submit(): void {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    };

    if (editing.value) {
        form.patch(route('meals.update', { meal: editing.value.id }), options);
    } else {
        form.post(route('meals.store'), options);
    }
}

const confirmOpen = ref(false);

function destroy(): void {
    if (editing.value === null) {
        return;
    }

    confirmOpen.value = true;
}

function confirmDestroy(): void {
    if (editing.value === null) {
        return;
    }

    confirmOpen.value = false;

    router.delete(route('meals.destroy', { meal: editing.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}

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

    router.patch(route('meals.reschedule', { meal }), { planned_on: date }, { preserveScroll: true });
}
</script>

<template>
    <HouseHubLayout title="Meal Planner" :subtitle="`Week of ${weekOf}`">
        <div class="flex animate-hh-rise flex-col gap-4">
            <div v-if="canManage" class="text-[13px] text-hh-ink3">Drag any meal card onto another day to reschedule, or click one to edit it.</div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                <div
                    v-for="day in days"
                    :key="day.date"
                    class="group flex min-h-[400px] flex-col gap-2.5 rounded-[18px] border border-dashed p-3 transition"
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
                        <button
                            v-if="canManage"
                            type="button"
                            class="ml-auto grid h-[22px] w-[22px] place-items-center rounded-md text-hh-ink3 opacity-0 transition hover:bg-hh-soft hover:text-hh-ink focus-visible:opacity-100 group-hover:opacity-100"
                            :aria-label="`Add a meal on ${day.dateLabel}`"
                            @click="openNew(day.date)"
                        >
                            <Plus class="h-3.5 w-3.5" :stroke-width="2.5" />
                        </button>
                    </div>

                    <button
                        v-for="meal in day.meals"
                        :key="meal.id"
                        type="button"
                        :draggable="canManage"
                        class="overflow-hidden rounded-2xl border border-hh-line bg-hh-card text-left transition hover:-translate-y-[3px] hover:shadow-hh"
                        :class="canManage ? 'cursor-grab' : ''"
                        :style="{ opacity: draggingId === meal.id ? 0.35 : 1 }"
                        @dragstart="startDrag($event, meal.id)"
                        @dragend="endDrag"
                        @click="canManage && openEdit(meal, day.date)"
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
                    </button>
                </div>
            </div>

            <div class="rounded-[22px] border border-hh-line bg-hh-panel p-[22px]">
                <div class="mb-3.5 flex items-baseline gap-2.5">
                    <h3 class="text-[15px] font-extrabold">Recipe library</h3>
                    <span class="text-xs text-hh-ink3">Favourites · {{ recipeCount }} saved</span>
                    <Link :href="route('recipes.index')" class="ml-auto text-[12.5px] font-semibold text-hh-coral hover:opacity-75">
                        Manage recipes
                    </Link>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                    <div v-for="recipe in library" :key="recipe.id" class="rounded-2xl border border-hh-line bg-hh-card p-3.5">
                        <div class="mb-2.5 h-[58px] rounded-xl" :style="{ background: tint(recipe.tint) }"></div>
                        <div class="text-[13.5px] font-bold leading-tight">{{ recipe.name }}</div>
                        <div class="mt-1.5 text-[11.5px] text-hh-ink3">{{ recipe.meta }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add / edit meal -->
        <Dialog :open="open" @update:open="open = $event">
            <DialogContent class="rounded-[22px] border-hh-line bg-hh-card text-hh-ink sm:rounded-[22px]">
                <DialogHeader>
                    <DialogTitle class="text-[16px] font-extrabold tracking-tight">{{ dialogTitle }}</DialogTitle>
                </DialogHeader>

                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <div class="flex flex-col gap-1.5">
                        <label for="recipe_id" class="hh-label">Recipe</label>
                        <select
                            id="recipe_id"
                            v-model="form.recipe_id"
                            class="hh-input"
                            @change="
                                form.new_recipe_name = '';
                                form.new_recipe_description = '';
                            "
                        >
                            <option :value="null">— Choose from the library —</option>
                            <option v-for="recipe in recipes" :key="recipe.id" :value="recipe.id">{{ recipe.name }}</option>
                        </select>
                        <p v-if="form.errors.recipe_id" class="text-[12.5px] text-hh-coral">{{ form.errors.recipe_id }}</p>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="new_recipe_name" class="hh-label">…or add a new recipe</label>
                        <input
                            id="new_recipe_name"
                            v-model="form.new_recipe_name"
                            type="text"
                            class="hh-input"
                            placeholder="Recipe name"
                            @input="form.recipe_id = null"
                        />
                        <p v-if="form.errors.new_recipe_name" class="text-[12.5px] text-hh-coral">{{ form.errors.new_recipe_name }}</p>
                    </div>

                    <div v-if="form.new_recipe_name" class="flex flex-col gap-1.5">
                        <label for="new_recipe_description" class="hh-label">Notes</label>
                        <textarea
                            id="new_recipe_description"
                            v-model="form.new_recipe_description"
                            rows="2"
                            maxlength="2000"
                            class="hh-input h-auto py-2.5"
                            placeholder="Optional"
                        ></textarea>
                        <p v-if="form.errors.new_recipe_description" class="text-[12.5px] text-hh-coral">
                            {{ form.errors.new_recipe_description }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label for="planned_on" class="hh-label">Day</label>
                            <input id="planned_on" v-model="form.planned_on" type="date" class="hh-input" required />
                            <p v-if="form.errors.planned_on" class="text-[12.5px] text-hh-coral">{{ form.errors.planned_on }}</p>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="slot" class="hh-label">Slot</label>
                            <select id="slot" v-model="form.slot" class="hh-input">
                                <option v-for="slot in SLOTS" :key="slot" :value="slot">{{ slot }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="cook_id" class="hh-label">Cook</label>
                        <select id="cook_id" v-model="form.cook_id" class="hh-input">
                            <option :value="null">Unassigned</option>
                            <option v-for="member in members" :key="member.id" :value="member.id">{{ member.name }}</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="hh-btn w-auto bg-hh-coral text-white" :disabled="form.processing">
                            {{ editing ? 'Save changes' : 'Add meal' }}
                        </button>
                        <button type="button" class="hh-btn w-auto bg-hh-soft text-hh-ink" @click="open = false">Cancel</button>
                        <button v-if="editing" type="button" class="hh-btn ml-auto w-auto bg-hh-soft text-hh-coral" @click="destroy">Remove</button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :open="confirmOpen"
            message="Remove this meal from the plan?"
            @update:open="confirmOpen = $event"
            @confirm="confirmDestroy"
        />
    </HouseHubLayout>
</template>
