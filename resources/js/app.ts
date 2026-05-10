import '../css/app.css';

if (typeof window !== 'undefined') {
    const originalAddEventListener = EventTarget.prototype.addEventListener;

    EventTarget.prototype.addEventListener = function(type, listener, options) {
        const passiveEvents = ['touchstart', 'touchmove', 'touchend', 'wheel', 'mousewheel'];

        if (passiveEvents.includes(type)) {
            if (typeof options === 'boolean') {
                options = { passive: true, capture: options };
            } else if (typeof options === 'object' && options !== null) {
                options = { ...options, passive: true };
            } else {
                options = { passive: true };
            }
        }

        return originalAddEventListener.call(this, type, listener, options);
    };

    const originalConsoleWarn = console.warn;
    console.warn = function(...args) {
        const message = args[0];
        if (typeof message === 'string' &&
            (message.includes('Added non-passive event listener') ||
             message.includes('scroll-blocking'))) {
            return;
        }
        return originalConsoleWarn.apply(console, args);
    };
}

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useAppearance';

// ✅ Import v-calendar
import { setupCalendar, Calendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => title || appName,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            // ✅ DIPERBAIKI: hapus locales dan locale yang menyebabkan error
            .use(setupCalendar, {})
            .component('VCalendar', Calendar)
            .component('VDatePicker', DatePicker)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
