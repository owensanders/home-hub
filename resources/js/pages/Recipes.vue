<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useHouseholdRole } from '@/composables/useHouseholdRole';
import HouseHubLayout from '@/layouts/HouseHubLayout.vue';
import { tint } from '@/lib/househub';
import type { Recipe } from '@/types/househub';
import { router, useForm } from '@inertiajs/vue3';
import { Plus, Star } from 'lucide-vue-next';
import { computed, ref } from 'vue';

defineProps<{ recipes: Recipe[] }>();

const TINTS = [0, 1, 2, 3, 4];

const { canManage } = useHouseholdRole();

const open = ref(false);
const editing = ref<Recipe | null>(null);

const form = useForm({
    name: '',
    description: '',
    duration_label: '',
    difficulty: '',
    tags: '',
    tint: 0,
    is_favourite: false as boolean,
});

const dialogTitle = computed(() => (editing.value ? 'Edit recipe' : 'New recipe'));

function openNew(): void {
    editing.value = null;
    form.clearErrors();
    form.defaults({
        name: '',
        description: '',
        duration_label: '',
        difficulty: '',
        tags: '',
        tint: 0,
        is_favourite: false,
    });
    form.reset();
    open.value = true;
}

function openEdit(recipe: Recipe): void {
    editing.value = recipe;
    form.clearErrors();
    form.defaults({
        name: recipe.name,
        description: recipe.description ?? '',
        duration_label: recipe.durationLabel ?? '',
        difficulty: recipe.difficulty ?? '',
        tags: recipe.tags.join(', '),
        tint: recipe.tint,
        is_favourite: recipe.isFavourite,
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

    form.transform((data) => ({
        ...data,
        tags: data.tags
            .split(',')
            .map((tag) => tag.trim())
            .filter(Boolean),
    }));

    if (editing.value) {
        form.patch(route('recipes.update', { recipe: editing.value.id }), options);
    } else {
        form.post(route('recipes.store'), options);
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

    router.delete(route('recipes.destroy', { recipe: editing.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <HouseHubLayout title="Recipe Library" :subtitle="`${recipes.length} saved`">
        <div class="flex animate-hh-rise flex-col gap-4">
            <div v-if="canManage" class="flex items-center gap-2">
                <button type="button" class="hh-btn w-auto bg-hh-coral text-white" @click="openNew">
                    <Plus class="h-4 w-4" :stroke-width="2.5" />
                    New recipe
                </button>
            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
                <button
                    v-for="recipe in recipes"
                    :key="recipe.id"
                    type="button"
                    class="rounded-2xl border border-hh-line bg-hh-card p-3.5 text-left transition hover:-translate-y-[3px] hover:shadow-hh"
                    @click="canManage && openEdit(recipe)"
                >
                    <div class="relative mb-2.5 h-[58px] rounded-xl" :style="{ background: tint(recipe.tint) }">
                        <Star
                            v-if="recipe.isFavourite"
                            class="absolute right-1.5 top-1.5 h-4 w-4 text-hh-ontint"
                            :stroke-width="2.5"
                            fill="currentColor"
                        />
                    </div>
                    <div class="text-[13.5px] font-bold leading-tight">{{ recipe.name }}</div>
                    <div class="mt-1.5 text-[11.5px] text-hh-ink3">{{ recipe.meta || 'No details yet' }}</div>
                </button>
            </div>

            <p v-if="recipes.length === 0" class="text-[13px] text-hh-ink3">No recipes yet — add your first one above.</p>
        </div>

        <!-- Add / edit -->
        <Dialog :open="open" @update:open="open = $event">
            <DialogContent class="rounded-[22px] border-hh-line bg-hh-card text-hh-ink sm:rounded-[22px]">
                <DialogHeader>
                    <DialogTitle class="text-[16px] font-extrabold tracking-tight">{{ dialogTitle }}</DialogTitle>
                </DialogHeader>

                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="hh-label">Name</label>
                        <input id="name" v-model="form.name" type="text" class="hh-input" required placeholder="What's cooking?" />
                        <p v-if="form.errors.name" class="text-[12.5px] text-hh-coral">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label for="duration_label" class="hh-label">Duration</label>
                            <input id="duration_label" v-model="form.duration_label" type="text" class="hh-input" placeholder="30 min" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="difficulty" class="hh-label">Difficulty</label>
                            <input id="difficulty" v-model="form.difficulty" type="text" class="hh-input" placeholder="Easy" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="tags" class="hh-label">Tags</label>
                        <input id="tags" v-model="form.tags" type="text" class="hh-input" placeholder="Comma separated, e.g. veggie, quick" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="description" class="hh-label">Notes</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="2"
                            maxlength="2000"
                            class="hh-input h-auto py-2.5"
                            placeholder="Optional"
                        ></textarea>
                        <p v-if="form.errors.description" class="text-[12.5px] text-hh-coral">{{ form.errors.description }}</p>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <span class="hh-label">Colour</span>
                        <div class="flex gap-2">
                            <button
                                v-for="t in TINTS"
                                :key="t"
                                type="button"
                                :aria-label="`Colour ${t + 1}`"
                                :aria-pressed="form.tint === t"
                                class="h-7 w-7 rounded-[9px] transition"
                                :class="form.tint === t ? 'ring-2 ring-hh-ink ring-offset-2 ring-offset-hh-card' : ''"
                                :style="{ background: tint(t) }"
                                @click="form.tint = t"
                            ></button>
                        </div>
                    </div>

                    <label for="is_favourite" class="flex items-center gap-2.5 text-[13px] text-hh-ink2">
                        <input id="is_favourite" v-model="form.is_favourite" type="checkbox" class="h-4 w-4 accent-hh-coral" />
                        Show in the planner's quick-pick favourites
                    </label>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="hh-btn w-auto bg-hh-coral text-white" :disabled="form.processing">
                            {{ editing ? 'Save changes' : 'Add recipe' }}
                        </button>
                        <button type="button" class="hh-btn w-auto bg-hh-soft text-hh-ink" @click="open = false">Cancel</button>
                        <button v-if="editing" type="button" class="hh-btn ml-auto w-auto bg-hh-soft text-hh-coral" @click="destroy">Delete</button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :open="confirmOpen"
            message="Delete this recipe? Any planned meals using it will be removed too."
            @update:open="confirmOpen = $event"
            @confirm="confirmDestroy"
        />
    </HouseHubLayout>
</template>
