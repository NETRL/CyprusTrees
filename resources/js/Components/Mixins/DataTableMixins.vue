<script>
import {FilterMatchMode} from '@primevue/core/api';

export default {
    data() {
        return {
            selected           : null,
            filters            : {},
            rowsPerPageOptions : [10, 25, 50, 100],
            loading            : false,
            sortOrder          : this.$page.props.old?.sortOrder,
            sortField          : this.$page.props.old?.sortField,
            oldSearch          : this.$page.props.old?.search ? this.$page.props.old?.search : '',
            sortOrderData      : this.$page.props.old?.sortOrder,
            sortOrderInt       : this.$page.props.old?.sortOrder === "asc" ? 1 : -1,
            sortFieldData      : this.$page.props.old?.sortField,
            searchData         : this.$page.props.old?.search ? this.$page.props.old?.search : '',
            userPermissions    : this.$page.props.auth.permissions,
        }
    },
    computed : {
        hasPermission() {
            return this.userPermissions.some(item => item.name === 'interestedStakeholders.edit' || item.name === 'interestedStakeholders.delete');
        },
    },
    methods  : {
        exportCSV() {
            this.$refs.dt.exportCSV();
        },
        initFilters() {
            this.filters = {
                'global' : {value : null, matchMode : FilterMatchMode.CONTAINS},
            }
        },
        makeHeader(key) {
            // Convert snake_case to words and capitalize the first letter of each word
            return key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        },
        doRequest(page, search, perPage, field, order) {
            this.loading = true;
            this.$inertia.get(this.routeOriginal,
                {
                    page      : page,
                    search    : search,
                    per_page  : perPage,
                    sortField : field,
                    sortOrder : order,
                },
                {onFinish : () => this.loading = false,}
            );
        },
        onSort(event) {
            this.sortOrderData = event.sortOrder === 1 ? "asc" : "desc"
            //Incase we sort by the same field twice, we need to change the sort order
            if (event.sortOrder === -1 && event.sortField === this.sortField) {
                this.sortOrderData = "desc"
            } else {
                this.sortOrderData = "asc"
            }

            this.doRequest(1, this.searchData, this.perPage, event.sortField, this.sortOrderData);
        },
        onPage(event) {
            this.doRequest(event.page + 1, this.searchData, this.perPage, this.sortField, this.sortOrder);
        },
        onPageButton(page) {
            this.doRequest(page, this.searchData, this.perPage, this.sortField, this.sortOrder);
        },
        searchTerm() {
            this.doRequest(1, this.searchData, this.perPage, null, null);
        },
    },
    created() {
        this.initFilters();
    }
}
</script>