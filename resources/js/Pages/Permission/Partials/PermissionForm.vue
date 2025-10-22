<template>
    <Dialog :breakpoints="{'960px': '75vw', '640px': '100vw'}" :modal="true" :style="{width: '450px'}" :visible="visible"
            header="Permission Details" @show="initForm" @update:visible="$emit('update:visible', event)">
        <form class="grid grid-cols-12 w-full" @submit.prevent="submit">
            <div class="col-span-12 mb-4">
                <FormField v-model="localPermission.type" :displayErrors="displayErrors" label="Type" name="type"/>
            </div>

            <div class="col-span-12 mb-4">
                <FormField v-model="localPermission.description" :displayErrors="displayErrors" label="Description" name="description"/>
            </div>

        </form>
        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm"/>
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit"/>
        </template>
    </Dialog>
</template>

<script>
import FormField from "@/Components/Primitives/FormField.vue"

export default {
    emits      : ['update:visible'],
    components : {
        FormField
    },
    props      : {
        visible    : Boolean,
        action     : String,
        group      : Object,
        permission : Object
    },
    data() {
        return {
            showForm        : this.visible,
            localPermission : {},
            displayErrors   : false,
        }
    },
    methods : {
        submit() {
            if (this.action === 'Create') {
                this.$inertia.post(
                    route('permissions.store'),
                    {
                        ...this.localPermission,
                        group : this.group.id
                    },
                    {
                        preserveScroll : true,
                        onSuccess      : () => this.closeForm(),
                        onFinish       : () => this.displayErrors = true,
                    }
                );
            } else
                if (this.action === 'Edit') {
                    this.$inertia.patch(
                        route('permissions.update', this.localPermission.id),
                        this.localPermission,
                        {
                            preserveScroll : true,
                            onSuccess      : () => this.closeForm(),
                            onFinish       : () => this.displayErrors = true,
                        }
                    );
                }
        },
        initForm() {
            this.displayErrors               = false;
            this.localPermission.id          = this.permission?.id
            this.localPermission.type        = this.permission?.type
            this.localPermission.description = this.permission?.description

        },
        closeForm() {
            this.$emit('update:visible', false)
        }
    }
}
</script>
