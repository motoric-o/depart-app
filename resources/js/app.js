import './bootstrap';

import Swal from 'sweetalert2';

window.Swal = Swal.mixin({
    confirmButtonColor: '#2563EB', // blue-600
    cancelButtonColor: '#4B5563', // gray-600
});

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import datatable from './components/datatable';
import usersManager from './components/users-manager';
import expensesManager from './components/expenses-manager';
import transactionsManager from './components/transactions-manager';

window.Alpine = Alpine;
Alpine.plugin(collapse);

document.addEventListener('alpine:init', () => {
    Alpine.data('datatable', datatable);
    Alpine.data('usersManager', usersManager);
    Alpine.data('expensesManager', expensesManager);
    Alpine.data('transactionsManager', transactionsManager);
});

Alpine.start();
