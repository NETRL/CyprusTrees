<template>
    <Dialog :breakpoints="breakpoints" :modal="true" :style="{width:widthModal}" :visible="visible"
            :header="__(header)" @update:visible="$emit('update:visible', event)">

        <div v-html="body"></div>
    </Dialog>
</template>

<script>
export default {
    emits : ['update:visible'],
    props : {
        visible    : Boolean,
        header     : String,
        body       : String,
        breakpoints: {
            type    : Object,
            default : () => ({
                '960px': '75vw',
                '640px': '100vw',
            }),
        },
        widthModal : {
            type    : String,
            default : '450px',
        },
    },
}
</script>
<style scoped lang="scss">

</style>