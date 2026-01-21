import '../css/app.css';
import '@mdi/font/css/materialdesignicons.css';
import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { de } from 'vuetify/locale';

import { Settings } from 'luxon';
Settings.defaultLocale = 'de';

// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { VDateInput } from 'vuetify/labs/components';
import colors from 'vuetify/util/colors';

// import { configureEcho } from '@laravel/echo-vue';

// configureEcho({
//     broadcaster: 'reverb',
//     key: import.meta.env['VITE_REVERB_APP_KEY'],
//     wsHost: window.location.hostname,
//     wsPort: import.meta.env['VITE_REVERB_PORT'],
//     wssPort: import.meta.env['VITE_REVERB_PORT'],
//     forceTLS: false,
//     enabledTransports: ['ws', 'wss'],
// });

const vuetify = createVuetify({
    defaults: {
        VTextField: { variant: 'underlined' },
        VFileInput: { variant: 'underlined' },
        VSelect: { variant: 'underlined' },
        VDateInput: { variant: 'underlined' },
        VAutocomplete: { variant: 'underlined' },
        VAlert: { variant: 'tonal' },
    },
    theme: {
        defaultTheme: 'lightTheme',
        themes: {
            lightTheme: {
                dark: false,
                colors: {
                    primary: colors.blueGrey.darken2,
                    success: '#198754', // ~colors.green.darken3
                    secondary: colors.deepPurple.lighten1,
                    error: colors.red.darken3,
                    background: colors.grey.lighten3,
                    layout: colors.grey.darken2,
                },
            },
        },
    },
    locale: {
        locale: 'de',
        fallback: 'en',
        messages: {
            de,
        },
    },
    date: {
        locale: {
            de: 'de-DE',
        },
    },
    components: { ...components, VDateInput },
    directives,
});

import canPlugin from './canPlugin';
import useFormPlugin from './useFormPlugin';

createInertiaApp({
    title: title => title,
    resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(canPlugin)
            .use(useFormPlugin)
            .use(vuetify)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
