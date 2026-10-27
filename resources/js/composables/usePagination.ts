import { computed, ref } from 'vue';

export function usePagination(source: () => any[]) {
    const currentPage = ref(1);
    const perPage = ref(10);

    const total = computed(() => source().length);

    const totalPages = computed(() =>
        Math.max(1, Math.ceil(total.value / perPage.value)),
    );

    const paginatedData = computed(() => {
        const data = source();

        if (!Array.isArray(data)) return [];

        const start = (currentPage.value - 1) * perPage.value;
        return data.slice(start, start + perPage.value);
    });

    const from = computed(() => {
        if (!total.value) return 0;
        return (currentPage.value - 1) * perPage.value + 1;
    });

    const to = computed(() => {
        return Math.min(currentPage.value * perPage.value, total.value);
    });

    function next() {
        if (currentPage.value < totalPages.value) currentPage.value++;
    }

    function prev() {
        if (currentPage.value > 1) currentPage.value--;
    }

    return {
        currentPage,
        perPage,
        paginatedData,
        total,
        totalPages,
        from,
        to,
        next,
        prev,
    };
}
