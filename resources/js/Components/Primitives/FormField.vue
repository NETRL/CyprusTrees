<template>
    <label :for="name" class="font-medium text-900 mr-3 w-full mb-1 block">{{ label }}</label>
    <InputText v-if="component === 'InputText'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs"/>
    <Password v-else-if="component === 'Password'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs" inputClass="w-full">
        <template #header>
            <h3>Pick a password</h3>
        </template>
        <template #footer>
            <Divider/>
            <p class="mt-2">Suggestions</p>
            <ul class="pl-2 ml-2 mt-0" style="line-height: 1.5">
                <li>At least one lowercase</li>
                <li>At least one uppercase</li>
                <li>At least one numeric</li>
                <li>Minimum 8 characters</li>
            </ul>
        </template>
    </Password>
    <Dropdown v-else-if="component === 'Dropdown'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs"/>
    <MultiSelect v-else-if="component === 'MultiSelect'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs"  :maxSelectedLabels="1" />
    <InputNumber v-else-if="component==='Number'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs"/>
    <Textarea v-else-if="component==='Textarea'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs"/>
    <FileUpload v-else-if="component === 'File'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" style="width:130px;" v-bind="$attrs"/>
    <Calendar v-else-if="component === 'Calendar'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" locale="en-GB" v-bind="$attrs"/>
    <InputSwitch v-else-if="component === 'InputSwitch'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs"/>
    <Checkbox v-else-if="component === 'Checkbox'" :id="name" :class="[shouldDisplayErrors ? 'p-invalid' : '', 'w-full']" v-bind="$attrs"/>
    <small v-if="shouldDisplayErrors" :id="name+'-help'" class="p-error">{{ $page.props.errors[name] }}</small>
</template>

<script>
export default {
    props    : {
        label         : String,
        name          : String,
        displayErrors : {
            type    : Boolean,
            default : true
        },
        component     : {
            type    : String,
            default : 'InputText'
        }
    },
    computed : {
        shouldDisplayErrors() {
            return this.$page.props.errors[this.name] && this.displayErrors;
        }
    }
}
</script>
