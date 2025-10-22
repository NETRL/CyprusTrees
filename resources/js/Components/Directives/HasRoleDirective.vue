<script>

export default {
    created(el, binding) {
        const user_roles = binding.value.props.auth.user.roles;

        const found = binding.value.roles.find(role => {
            return user_roles.some(e => e.name === role)
        });

        if (!found) { // no match
            el.style.display = "none";
        }
    }
}
</script>
