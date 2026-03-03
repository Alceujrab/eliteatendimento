import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Sortable from 'sortablejs';

// Make available globally
window.Alpine = Alpine;
window.Chart = Chart;
window.Sortable = Sortable;

Alpine.start();
