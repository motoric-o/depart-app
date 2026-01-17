import datatable from './datatable';
import Swal from 'sweetalert2';

export default function usersManager(config) {
    return {
        ...datatable(config),
        currentUserRole: config.currentUserRole || 'Unknown',
        canManageUsers: config.canManageUsers || false,
        canManageDrivers: config.canManageDrivers || false,

        canManage(targetRole) {
            // Priority 1: High level user management
            if (this.canManageUsers) {
                if (!targetRole) return false;
                const allowed = ['Customer', 'Driver'];
                // Owner/Super Admin logic handled by Gate mostly, but refining for target hierarchy
                if (this.currentUserRole === 'Owner' || this.currentUserRole === 'Super Admin') {
                    if (this.currentUserRole === 'Owner') allowed.push('Admin', 'Super Admin', 'Financial Admin', 'Operations Admin', 'Scheduling Admin');
                    else if (this.currentUserRole === 'Super Admin') allowed.push('Admin', 'Financial Admin', 'Operations Admin', 'Scheduling Admin');
                }
                return allowed.includes(targetRole);
            }

            // Priority 2: Ops Admin managing Drivers
            if (this.canManageDrivers && targetRole === 'Driver') {
                return true;
            }

            return false;
        },

        deleteItem(id, url) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB', // blue-600 to match theme
                cancelButtonColor: '#4B5563',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.performDelete([id], url);
                }
            })
        },

        bulkDelete() {
            if (this.selectedItems.length === 0) return;

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete ${this.selectedItems.length} users. This cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#4B5563',
                confirmButtonText: 'Yes, delete them!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const promises = this.selectedItems.map(id => {
                        return fetch(`/admin/users/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        }).then(res => res.json());
                    });

                    Promise.all(promises).then(results => {
                        // Check failures
                        const failed = results.filter(r => !r.success);
                        if (failed.length === 0) {
                            this.items = this.items.filter(item => !this.selectedItems.includes(item.id));
                            this.selectedItems = [];
                            Swal.fire('Deleted!', 'Selected users have been deleted.', 'success');
                        } else {
                            Swal.fire('Error!', `${failed.length} items failed to delete.`, 'error');
                        }
                    }).catch(err => {
                        console.error(err);
                        Swal.fire('Error!', 'An error occurred during bulk deletion.', 'error');
                    });
                }
            })
        },

        performDelete(ids, url) {
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.items = this.items.filter(item => item.id !== ids[0]);
                        Swal.fire('Deleted!', data.message, 'success');
                    } else {
                        Swal.fire('Error!', 'Failed to delete item.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error!', 'An error occurred.', 'error');
                });
        },

        formatDate(dateString) {
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        },

        roleClass(roleName) {
            switch (roleName) {
                case 'Admin':
                case 'Super Admin':
                case 'Financial Admin':
                case 'Operations Admin':
                case 'Scheduling Admin':
                    return 'bg-purple-100 text-purple-800';
                case 'Owner': return 'bg-yellow-100 text-yellow-800';
                case 'Driver': return 'bg-blue-100 text-blue-800';
                default: return 'bg-green-100 text-green-800';
            }
        }
    };
}
