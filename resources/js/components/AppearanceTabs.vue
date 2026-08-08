<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Monitor, Moon, Sun } from 'lucide-vue-next';

interface Props {
    class?: string;
}

const { class: containerClass = '' } = defineProps<Props>();

const { appearance, updateAppearance } = useAppearance();

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;
</script>

<template>
    <div :class="['inline-flex gap-1.5 rounded-[15px] border border-hh-line bg-hh-panel p-1.5', containerClass]">
        <button
            v-for="{ value, Icon, label } in tabs"
            :key="value"
            type="button"
            class="flex items-center gap-1.5 rounded-[11px] px-4 py-2 text-[13px] transition-colors"
            :class="appearance === value ? 'bg-hh-card font-bold text-hh-ink shadow-hh' : 'font-medium text-hh-ink2 hover:bg-hh-soft'"
            @click="updateAppearance(value)"
        >
            <component :is="Icon" class="h-4 w-4" />
            {{ label }}
        </button>
    </div>
</template>
