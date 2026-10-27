<script setup lang="ts">
import { ref, computed } from 'vue';
import { Monitor, Moon, Sun } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();
const open = ref(false);

const tabs = [
    { value: 'light', Icon: Sun, label: 'Light' },
    { value: 'dark', Icon: Moon, label: 'Dark' },
    { value: 'system', Icon: Monitor, label: 'System' },
] as const;

const current = computed(
    () => tabs.find((t) => t.value === appearance.value) || tabs[0],
);

function select(value: 'light' | 'dark' | 'system') {
    updateAppearance(value);
    open.value = false;
}
</script>

<template>
    <div class="relative inline-block">
        <!-- Trigger -->
        <button
            @click="open = !open"
            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-neutral-100 text-neutral-800 dark:bg-neutral-800 dark:text-neutral-100"
        >
            <component :is="current.Icon" class="h-4 w-4" />
        </button>

        <div
            v-if="open"
            class="absolute z-50 mt-1 w-40 rounded-lg bg-white p-1 shadow-md dark:bg-neutral-800"
        >
            <button
                v-for="{ value, Icon, label } in tabs"
                :key="value"
                @click="select(value)"
                :class="[
                    'flex w-full items-center rounded-md px-3.5 py-1.5 transition-colors',
                    appearance === value
                        ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                        : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                ]"
            >
                <component :is="Icon" class="mr-2 -ml-1 h-4 w-4" />
                <span class="text-sm">{{ label }}</span>
            </button>
        </div>
    </div>
</template>
