<script>
import axios from 'axios';

export default {
    updated() {
        this.$nextTick(async () => {
            if (this.$refs.loadMoreIntersect) {
                const observer = new IntersectionObserver(entries => entries.forEach(entry => entry.isIntersecting && this.loadMore(), {
                    rootMargin : "-250px 0px 0px 0px"
                }));

                observer.observe(this.$refs.loadMoreIntersect)
            }
        });
    },
    data() {
        return {
            entries   : [],
            page      : 1,
            isLoading : false,
            hasMore   : true,
        }
    },
    methods : {
        async loadMore() {
            if (!this.hasMore || this.isLoading) {
                return;
            }

            await this.$nextTick(async () => {
                this.isLoading = true;
                const response = await axios.get(
                    route(this.loadRoute, {
                        ...this.routeParams,
                        page : this.page++,
                    }),
                );
                this.entries   = [...this.entries, ...response.data.data]
                this.isLoading = false;
                this.hasMore   = response.data.next_page_url !== null;
            });

        }
    },
}
</script>
