<script>
function applyPermission(el, binding) {
    const value = binding.value || {};

    // required permissions from the directive binding
    const required = Array.isArray(value.permissions) ? value.permissions : [];

    // no required permissions => do nothing
    if (required.length === 0) {
        return;
    }

    const props = value.props || {};
    const auth  = props.auth || {};

    // Support either auth.permissions or auth.user.permissions
    // and ALWAYS fall back to an empty array.
    let user_permissions = [];

    if (Array.isArray(auth.permissions)) {
        user_permissions = auth.permissions;
    } else if (auth.user && Array.isArray(auth.user.permissions)) {
        user_permissions = auth.user.permissions;
    }

    // At this point user_permissions is ALWAYS an array
    const hasAny = required.some(permission =>
        user_permissions.some(p => p.name === permission)
    );

    if (!hasAny) {
        el.style.display = 'none';
    } else {
        // Reset any previous hiding if needed
        el.style.removeProperty('display');
    }
}

export default {
    beforeMount(el, binding) {
        applyPermission(el, binding);
    },
    updated(el, binding) {
        applyPermission(el, binding);
    }
}
</script>
