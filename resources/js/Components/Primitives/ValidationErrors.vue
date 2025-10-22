<template>
    <div v-if="hasErrors">
        <div class="flex align-items-start p-4 bg-pink-100 border-round border-1 border-pink-300">
            <i class="pi pi-times-circle text-pink-900 text-2xl mr-3"></i>
            <div class="mr-3">
                <div class="text-pink-900 font-medium text-xl mb-3 line-height-1">
                    {{ __('Oops Something went wrong') }}
                </div>
                <ul class="m-0 p-0 text-pink-700 ml-3">
                    <li v-for="(error, key) in errors" :key="key" class="p-1">
                        {{ __(error) }}
                    </li>
                </ul>
            </div>
            <div class="ml-auto">
                <a v-ripple
                   class="inline-flex align-items-center justify-content-center ml-auto border-circle hover:bg-pink-50 no-underline cursor-pointer transition-colors transition-duration-150 p-ripple"
                   style="width: 1.5rem; height: 1.5rem">
                </a>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    computed : {
        errors() {
            return this.$page.props.errors;
        },

        hasErrors() {
            return Object.keys(this.errors).length > 0;
        },
    },
};
</script>
