import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useHouseholdRole() {
    const page = usePage<SharedData>();
    const user = computed(() => page.props.auth?.user ?? null);
    const role = computed(() => user.value?.role ?? null);
    const canManage = computed(() => role.value === 'owner' || role.value === 'adult');
    const isTeen = computed(() => role.value === 'teen');

    // Teen/Child can only tick off chores assigned to them; Owner/Adult can toggle any.
    function canToggleChore(chore: { assignee: { id: number } | null }): boolean {
        return canManage.value || chore.assignee?.id === user.value?.id;
    }

    return { user, role, canManage, isTeen, canToggleChore };
}
