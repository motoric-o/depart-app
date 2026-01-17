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
        selectedItems: [],

        // ... existing properties ...
        loading: false,
        url: config.url,

        init() {
            // Initial fetch if not lazy
            if (!config.lazy) {
                this.fetchData();
            }

            this.$watch('items', () => {
                this.selectedItems = [];
            });
        },

        checkAllSelected() {
            return this.items.length > 0 && this.selectedItems.length === this.items.length;
        },

        toggleSelectAll() {
            if (this.checkAllSelected()) {
                this.selectedItems = [];
            } else {
                this.selectedItems = this.items.map(item => item.id);
            }
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
                    current_page: parseInt(data.current_page),
                    last_page: parseInt(data.last_page),
                    total: parseInt(data.total),
                    from: parseInt(data.from),
                    to: parseInt(data.to),
                    per_page: parseInt(data.per_page)
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
        },

        getPages() {
            const range = 2; // Number of pages to show around current page
            let pages = [];
            let start = Math.max(1, this.pagination.current_page - range);
            let end = Math.min(this.pagination.last_page, this.pagination.current_page + range);

            // Always show first page
            if (start > 1) {
                pages.push(1);
                if (start > 2) pages.push('...');
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            // Always show last page
            if (end < this.pagination.last_page) {
                if (end < this.pagination.last_page - 1) pages.push('...');
                pages.push(this.pagination.last_page);
            }

            return pages;
        }
    };
}
