import { ref, computed } from 'vue';
import { ArrowUp, ArrowDown, ArrowUpDown } from 'lucide-vue-next';

export function useSortable<T>(data: T[]) {
    const sortKey = ref<keyof T | null>(null);
    const sortOrder = ref<'asc' | 'desc'>('asc');

    const sortedData = computed(() => {
        if (!sortKey.value) return data;

        return [...data].sort((a, b) => {
            const aValue = a[sortKey.value as keyof T];
            const bValue = b[sortKey.value as keyof T];

            if (aValue == null || bValue == null) return 0;

            let result = 0;

            // detect date
            if (typeof aValue === 'string' && !isNaN(Date.parse(aValue))) {
                result =
                    new Date(aValue).getTime() -
                    new Date(bValue as string).getTime();
            } else {
                result = String(aValue).localeCompare(String(bValue as string));
            }

            return sortOrder.value === 'asc' ? result : -result;
        });
    });

    function sort(key: keyof T) {
        if (sortKey.value === key) {
            sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortKey.value = key;
            sortOrder.value = 'asc';
        }
    }

    // 🔥 NEW: UI ICON HANDLER
    function getSortIcon(key: keyof T) {
        if (sortKey.value !== key) {
            return ArrowUpDown;
        }

        return sortOrder.value === 'asc' ? ArrowUp : ArrowDown;
    }

    // optional helper for styling state
    function isSorted(key: keyof T) {
        return sortKey.value === key;
    }

    return {
        sortKey,
        sortOrder,
        sortedData,
        sort,
        getSortIcon,
        isSorted,
    };
}
