import { useConfirm } from "primevue";
import { router } from "@inertiajs/vue3";

/**
 * Reusable CRUD operations composable
 * @param {string} routePrefix - The route prefix (e.g., 'species', 'users')
 * @returns {Object} - CRUD operation functions
 */
export function useCrudOperations(routePrefix) {
    const confirm = useConfirm();

    /**
     * Delete a single resource with confirmation
     * @param {number|string} id - The resource ID
     * @param {Function} onSuccess - Callback on successful deletion
     */
    const deleteOne = (id, onSuccess) => {
        confirm.require({
            message: 'Are you sure you want to delete this item?',
            header: 'Confirmation',
            icon: 'pi pi-exclamation-triangle',
            accept: () => {
                router.delete(route(`${routePrefix}.destroy`, id), {
                    onSuccess: () => {
                        if (onSuccess) onSuccess();
                    }
                });
            },
            reject: () => { }
        });
    };

    /**
   * Mass delete resources with confirmation
   * @param {Array} items - Array of items to delete
   * @param {Function} onSuccess - Callback on successful deletion
   */
    const massDelete = (items, onSuccess) => {
        if (!items || items.length === 0) {
            return;
        }

        const itemCount = items.length;
        const itemText = itemCount === 1 ? 'item' : 'items';

        confirm.require({
            message: `Are you sure you want to delete ${itemCount} ${itemText}?`,
            header: 'Confirmation',
            icon: 'pi pi-exclamation-triangle',
            accept: () => {
                router.post(
                    route(`${routePrefix}.massDestroy`),
                    { [routePrefix]: items },
                    {
                        onSuccess: () => {
                            if (onSuccess) onSuccess();
                        }
                    }
                );
            },
            reject: () => { }
        });
    };

    return {
        deleteOne,
        massDelete
    };
}