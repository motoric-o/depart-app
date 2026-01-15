import axios from 'axios';

export default function datatable(config) {
    return {
        items: [],
        pagination: {
            current_page: 1,
            last_page: 1,
            total: 0,
            from: 0,
            to: 0,
            per_page: 10
        },
        filters: {
            search: '',
            sort_by: config.sort_by || 'created_at',
            sort_order: config.sort_order || 'desc',
            ...config.filters
        },
        loading: false,
        url: config.url,

        init() {
            // Initial fetch if not lazy
            if (!config.lazy) {
                this.fetchData();
            }

            // Watchers for Debounced Search
            // Watcher removed: Search triggers only on Enter or Button Click
            /*
            this.$watch('filters.search', (value) => {
                clearTimeout(this.searchDebounce);
                this.searchDebounce = setTimeout(() => {
                    this.fetchData(1);
                }, 300);
            });
            */
        },

        async fetchData(page = 1) {
            this.loading = true;
            this.pagination.current_page = page;

            const params = new URLSearchParams({
                page: page,
                per_page: this.pagination.per_page,
                search: this.filters.search,
                sort_by: this.filters.sort_by,
                sort_order: this.filters.sort_order,
                ...this.filters
            });

            // Remove loading state protection for demo speed, but in prod add error handling
            try {
                const response = await axios.get(this.url + '?' + params.toString());
                const data = response.data;
                console.log('Datatable API Response for ' + this.url, data);

                this.items = data.data;
                this.pagination = {
                    current_page: data.current_page,
                    last_page: data.last_page,
                    total: data.total,
                    from: data.from,
                    to: data.to,
                    per_page: data.per_page
                };
            } catch (error) {
                console.error('Datatable Fetch Error:', error);
                console.log('Error Details:', error.response);
            } finally {
                this.loading = false;
            }
        },

        sortBy(column) {
            if (this.filters.sort_by === column) {
                this.filters.sort_order = this.filters.sort_order === 'asc' ? 'desc' : 'asc';
            } else {
                this.filters.sort_by = column;
                this.filters.sort_order = 'asc'; // Default to asc for new column
            }
            this.fetchData(1);
        },

        addFilter(key, value) {
            this.filters[key] = value;
            this.fetchData(1);
        }
    };
}
