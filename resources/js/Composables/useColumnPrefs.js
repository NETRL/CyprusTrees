import { onMounted, ref, watch } from "vue";

export function useColumnPrefs(key, allValues) {
    const selected = ref([]);

    onMounted(() => {
        const saved = localStorage.getItem(key);
        if (!saved) { selected.value = allValues.slice(); return; }
        try {
            const parsed = JSON.parse(saved);
            const valid = new Set(allValues);
            selected.value = parsed.filter((v) => valid.has(v));
        } catch {
            selected.value = allValues.slice();
        }
    });

    watch(selected, v => localStorage.setItem(key, JSON.stringify(v)), { deep: true });

    return selected;
}