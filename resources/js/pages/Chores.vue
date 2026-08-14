<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useHouseholdRole } from '@/composables/useHouseholdRole';
import HouseHubLayout from '@/layouts/HouseHubLayout.vue';
import { dial, tickStyle } from '@/lib/househub';
import type { Chore, ChoreColumn, ChoreStatus, Member, MemberScore } from '@/types/househub';
import { router, useForm } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    columns: ChoreColumn[];
    scores: MemberScore[];
    members: Member[];
}>();

const { canManage, canToggleChore } = useHouseholdRole();

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

function toggle(chore: Chore): void {
    if (!canToggleChore(chore)) {
        return;
    }

    router.patch(route('chores.toggle', { chore: chore.id }), {}, { preserveScroll: true });
}

const open = ref(false);
const editing = ref<Chore | null>(null);

const form = useForm({
    name: '',
    assigned_to: '',
    status: 'today' as ChoreStatus,
    due_label: '',
    repeat_label: '',
});

const dialogTitle = computed(() => (editing.value ? 'Edit chore' : 'New chore'));
const statusOptions = computed(() => props.columns.map((column) => ({ value: column.status, label: column.title })));

function openNew(): void {
    editing.value = null;
    form.clearErrors();
    form.defaults({
        name: '',
        assigned_to: '',
        status: 'today',
        due_label: '',
        repeat_label: '',
    });
    form.reset();
    open.value = true;
}

function openEdit(chore: Chore): void {
    editing.value = chore;
    form.clearErrors();
    form.defaults({
        name: chore.name,
        assigned_to: chore.assignee ? String(chore.assignee.id) : '',
        status: chore.status,
        due_label: chore.dueLabel ?? '',
        repeat_label: chore.repeatLabel ?? '',
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
        assigned_to: data.assigned_to === '' ? null : Number(data.assigned_to),
    }));

    if (editing.value) {
        form.patch(route('chores.update', { chore: editing.value.id }), options);
    } else {
        form.post(route('chores.store'), options);
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

    router.delete(route('chores.destroy', { chore: editing.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            open.value = false;
        },
    });
}
</script>

<template>
    <HouseHubLayout title="Chores" subtitle="This week">
        <div class="flex animate-hh-rise flex-col gap-[18px]">
            <div v-if="canManage" class="flex items-center gap-2">
                <button type="button" class="hh-btn w-auto bg-hh-coral text-white" @click="openNew">
                    <Plus class="h-4 w-4" :stroke-width="2.5" />
                    New chore
                </button>
            </div>

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
                        :draggable="canManage"
                        class="rounded-2xl border border-hh-line bg-hh-card px-3.5 py-3 transition hover:-translate-y-0.5 hover:shadow-hh"
                        :class="canManage ? 'cursor-grab' : ''"
                        :style="{ opacity: draggingId === chore.id ? 0.4 : 1 }"
                        @dragstart="startDrag($event, chore.id)"
                        @dragend="draggingId = null"
                        @click="canManage && openEdit(chore)"
                    >
                        <div class="flex items-start gap-2.5">
                            <!-- Ticking lives on the box alone so it can't fire at the end of a drag. -->
                            <button
                                type="button"
                                :disabled="!canToggleChore(chore)"
                                :aria-label="chore.done ? `Mark ${chore.name} as not done` : `Mark ${chore.name} as done`"
                                class="mt-px grid h-[19px] w-[19px] flex-none place-items-center rounded-full border-[1.5px] text-[11px] text-[#0E1A2B] disabled:cursor-not-allowed"
                                :style="tickStyle(chore.done)"
                                @click.stop="toggle(chore)"
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

        <!-- Add / edit -->
        <Dialog :open="open" @update:open="open = $event">
            <DialogContent class="rounded-[22px] border-hh-line bg-hh-card text-hh-ink sm:rounded-[22px]">
                <DialogHeader>
                    <DialogTitle class="text-[16px] font-extrabold tracking-tight">{{ dialogTitle }}</DialogTitle>
                </DialogHeader>

                <form class="flex flex-col gap-4" @submit.prevent="submit">
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="hh-label">Name</label>
                        <input id="name" v-model="form.name" type="text" class="hh-input" required placeholder="What needs doing?" />
                        <p v-if="form.errors.name" class="text-[12.5px] text-hh-coral">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label for="status" class="hh-label">Status</label>
                            <select id="status" v-model="form.status" class="hh-input">
                                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="assigned_to" class="hh-label">Assignee</label>
                            <select id="assigned_to" v-model="form.assigned_to" class="hh-input">
                                <option value="">Unassigned</option>
                                <option v-for="member in members" :key="member.id" :value="String(member.id)">
                                    {{ member.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1.5">
                            <label for="due_label" class="hh-label">Due</label>
                            <input id="due_label" v-model="form.due_label" type="text" class="hh-input" placeholder="Today" />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="repeat_label" class="hh-label">Repeats</label>
                            <input id="repeat_label" v-model="form.repeat_label" type="text" class="hh-input" placeholder="Weekly" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="hh-btn w-auto bg-hh-coral text-white" :disabled="form.processing">
                            {{ editing ? 'Save changes' : 'Add chore' }}
                        </button>
                        <button type="button" class="hh-btn w-auto bg-hh-soft text-hh-ink" @click="open = false">Cancel</button>
                        <button v-if="editing" type="button" class="hh-btn ml-auto w-auto bg-hh-soft text-hh-coral" @click="destroy">Delete</button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>

        <ConfirmDialog
            :open="confirmOpen"
            message="Delete this chore?"
            @update:open="confirmOpen = $event"
            @confirm="confirmDestroy"
        />
    </HouseHubLayout>
</template>
