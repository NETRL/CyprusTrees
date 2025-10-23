<template>
    <Dialog :breakpoints="{ '960px': '75vw', '640px': '100vw' }" :modal="true" :style="{ width: '450px' }"
        :visible="visible" header="User Details" @show="initForm" @update:visible="$emit('update:visible', event)">
        <form class="grid grid-cols-12 w-full" @submit.prevent="submit">
            <div class="col-span-12 mb-4">
                <FormField v-model="localUser.first_name" :displayErrors="displayErrors" autocomplete="given-name"
                    label="First Name" name="first_name" />
            </div>
            <div class="col-span-12 mb-4">
                <FormField v-model="localUser.last_name" :displayErrors="displayErrors" autocomplete="family-name"
                    label="Last Name" name="last_name" />
            </div>
            <div class="col-span-12 mb-4">
                <FormField v-model="localUser.email" :displayErrors="displayErrors" autocomplete="email" label="Email"
                    name="email" type="email" />
            </div>
            <div class="col-span-12 mb-4">
                <FormField v-model="localUser.password" :displayErrors="displayErrors" autocomplete="new-password"
                    component="Password" label="Password" name="password" />
            </div>
            <div class="col-span-12 mb-4">
                <FormField v-model="localUser.confirm_password" :displayErrors="displayErrors"
                    autocomplete="new-password" component="Password" label="Confirm Password" name="confirm_password" />
            </div>
            <div v-has-permission="{ props: $page.props, permissions: ['roles.assign'] }" class="col-span-12 mb-4">
                <FormField v-model="localUser.roles" :displayErrors="displayErrors" :filter="false" :options="roles"
                    component="MultiSelect" label="Roles" name="roles" optionLabel="name" optionValue="id" />
            </div>
        </form>
        <template #footer>
            <Button class="p-button-text" icon="pi pi-times" label="Cancel" @click="closeForm" />
            <Button :label="action" class="p-button-text" icon="pi pi-check" @click="submit" />
        </template>
    </Dialog>
</template>

<script>
import FormField from "@/Components/Primitives/FormField.vue"

export default {
    emits: ['update:visible'],
    components: {
        FormField
    },
    props: {
        visible: Boolean,
        user: Object,
        roles: Object,
        action: String
    },
    data() {
        return {
            showForm: this.visible,
            localUser: {},
            displayErrors: false
        }
    },
    methods: {
        submit() {
            if (this.action === 'Create') {
                this.$inertia.post(
                    route('users.store'),
                    this.localUser,
                    {
                        preserveScroll: true,
                        onSuccess: () => this.closeForm(),
                        onFinish: () => this.displayErrors = true,
                    }
                );
            } else
                if (this.action === 'Edit') {
                    this.$inertia.patch(
                        route('users.update', this.localUser.id),
                        this.localUser,
                        {
                            preserveScroll: true,
                            onSuccess: () => this.closeForm(),
                            onFinish: () => this.displayErrors = true,

                        }
                    );
                }
        },
        initForm() {
            this.displayErrors = false;
            this.localUser.id = this.user?.id
            this.localUser.first_name = this.user?.first_name
            this.localUser.last_name = this.user?.last_name
            this.localUser.email = this.user?.email
            this.localUser.password = ''
            this.localUser.confirm_password = ''

            const roles = [];
            this.user?.roles.map((role) => {
                roles.push(role.id);
            })

            this.localUser.roles = roles;

        },
        closeForm() {
            this.$emit('update:visible', false)
        }
    }
}
</script>