<script>

export default {
    created(el, binding) {
        if (!binding.value.permissions) {
            return;
        }

        const user_permissions = binding.value.props.auth.permissions;

        const found = binding.value.permissions.find(permission => {
            return user_permissions.some(e => e.name === permission)
        });

        if (!found) { // no match
            el.style.display = "none";
        }
    },
    updated(el, binding) {
        if (!binding.value.permissions) {
            return;
        }

        const user_permissions = binding.value.props.auth.permissions;

        const found = binding.value.permissions.find(permission => {
            return user_permissions.some(e => e.name === permission)
        });

        if (!found) { // no match
            el.style.display = "none";
        } else {
            // el.style.display = "inline";
        }
    }
}
</script>
