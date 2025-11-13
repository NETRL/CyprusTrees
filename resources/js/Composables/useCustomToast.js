import { usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { watch } from "vue";


export function useCustomToast() {

    const page = usePage();
    const toast = useToast();

    // Flash message toast
    watch(
        () => page.props?.message,
        (msg) => {
            if (msg?.type && msg?.message) {
                const severity =
                    msg.type === 'success'
                        ? 'success'
                        : msg.type === 'error'
                            ? 'error'
                            : 'info'
                toast.add({
                    severity: severity,
                    summary: msg.type.charAt(0).toUpperCase() + msg.type.slice(1),
                    detail: msg.message,
                    life: 3000
                });
            }
        },
        { immediate: true }
    )
}