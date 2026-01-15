import './bootstrap';
import datatable from './components/datatable';

document.addEventListener('alpine:init', () => {
    Alpine.data('datatable', datatable);
});

window.datatable = datatable;
