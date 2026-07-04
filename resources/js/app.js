import Alpine from 'alpinejs';
import axios from 'axios';
import Chart from 'chart.js/auto';

// Make available globally
window.Alpine = Alpine;
window.axios = axios;
window.Chart = Chart;

// Configure axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ===== Dark Mode Manager =====
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        on: localStorage.getItem('darkMode') === 'true' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),

        toggle() {
            this.on = !this.on;
            localStorage.setItem('darkMode', this.on);
            this.apply();
        },

        apply() {
            if (this.on) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        },

        init() {
            this.apply();
        }
    });

    // ===== Sidebar Manager =====
    Alpine.store('sidebar', {
        open: window.innerWidth > 1024,

        toggle() {
            this.open = !this.open;
        },

        close() {
            if (window.innerWidth <= 1024) {
                this.open = false;
            }
        }
    });

    // ===== Toast Notification =====
    Alpine.store('toast', {
        show: false,
        message: '',
        type: 'success',
        timeout: null,

        fire(message, type = 'success', duration = 3000) {
            this.message = message;
            this.type = type;
            this.show = true;

            if (this.timeout) clearTimeout(this.timeout);
            this.timeout = setTimeout(() => {
                this.show = false;
            }, duration);
        }
    });
});

// ===== Chart.js Default Config =====
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.color = '#94a3b8';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.pointStyle = 'circle';
Chart.defaults.plugins.legend.labels.padding = 20;
Chart.defaults.elements.bar.borderRadius = 6;
Chart.defaults.elements.line.tension = 0.4;
Chart.defaults.scale.grid = {
    color: 'rgba(148, 163, 184, 0.08)',
};

// ===== Event Stream (SSE / Polling) =====
window.EventStream = {
    listeners: [],
    pollingInterval: null,

    subscribe(callback) {
        this.listeners.push(callback);
    },

    notify(event) {
        this.listeners.forEach(cb => cb(event));
    },

    startPolling(url = '/api/events/stream', interval = 3000) {
        this.pollingInterval = setInterval(async () => {
            try {
                const response = await axios.get(url);
                if (response.data && response.data.events) {
                    response.data.events.forEach(event => this.notify(event));
                }
            } catch (e) {
                // Silently fail
            }
        }, interval);
    },

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }
};

// Initialize Alpine
Alpine.start();
