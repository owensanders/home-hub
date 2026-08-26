<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';

const props = defineProps<{
    householdId: number;
    householdName: string;
}>();

const form = useForm({
    household_id: props.householdId,
    confirm_household_deletion: false,
});

const leaveHousehold = (e: Event) => {
    e.preventDefault();

    form.delete(route('households.leave'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onFinish: () => {
            form.reset();
        },
    });
};

const closeModal = () => {
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <Dialog>
        <DialogTrigger class="hh-btn w-auto bg-hh-sunk px-4 text-hh-coral"> Leave </DialogTrigger>
        <DialogContent class="rounded-[22px] border-hh-line bg-hh-card text-hh-ink sm:rounded-[22px]">
            <form class="flex flex-col gap-5" @submit="leaveHousehold">
                <DialogHeader class="space-y-2">
                    <DialogTitle class="text-[16px] font-extrabold tracking-tight">Leave {{ props.householdName }}?</DialogTitle>
                    <DialogDescription class="text-[13px] text-hh-ink3">
                        You will lose access to its meals, lists, chores, calendar and documents until someone invites you back.
                    </DialogDescription>
                </DialogHeader>

                <div v-if="form.errors.household" class="rounded-[13px] bg-[color-mix(in_srgb,var(--hh-coral)_10%,transparent)] p-3.5">
                    <p class="text-[13px] text-hh-ink2">{{ form.errors.household }}</p>
                    <a :href="route('house.index')" class="mt-1.5 inline-block text-[13px] font-bold text-hh-coral underline">
                        Go promote another member to Owner instead
                    </a>
                    <label class="mt-3 flex items-start gap-2 text-[13px] text-hh-ink2">
                        <input v-model="form.confirm_household_deletion" type="checkbox" class="mt-0.5" />
                        I understand — delete this household and all its data
                    </label>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose class="hh-btn w-auto bg-hh-soft text-hh-ink" @click="closeModal"> Cancel </DialogClose>

                    <button
                        type="submit"
                        class="hh-btn w-auto bg-hh-coral text-white"
                        :disabled="form.processing || (!!form.errors.household && !form.confirm_household_deletion)"
                    >
                        Leave household
                    </button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
