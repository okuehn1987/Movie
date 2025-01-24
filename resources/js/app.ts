import '../css/app.css';
import '@mdi/font/css/materialdesignicons.css';
import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { de } from 'vuetify/locale';

const appName = import.meta.env['VITE_APP_NAME'];

import { Settings } from 'luxon';
Settings.defaultLocale = 'de';

// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { VCalendar } from 'vuetify/labs/VCalendar';
import { VDateInput, VNumberInput, VTreeview } from 'vuetify/labs/components';
import colors from 'vuetify/util/colors';

const vuetify = createVuetify({
    defaults: {
        VTextField: { variant: 'underlined' },
        VFileInput: { variant: 'underlined' },
        VSelect: { variant: 'underlined' },
        VDateInput: { variant: 'underlined' },
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
    components: { ...components, VCalendar, VDateInput, VNumberInput, VTreeview },
    directives,
});

import canPlugin from './canPlugin';
import useFormPlugin from './useFormPlugin';

createInertiaApp({
    title: title => `${title} - ${appName}`,
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
