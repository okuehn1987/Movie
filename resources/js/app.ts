import './bootstrap';
import '../css/app.css';
import '@mdi/font/css/materialdesignicons.css';
import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env['VITE_APP_NAME'] || 'Laravel';

// Vuetify
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { VCalendar } from 'vuetify/labs/VCalendar';
import { VDateInput } from 'vuetify/labs/components';

const vuetify = createVuetify({
	theme: {
		defaultTheme: 'lightTheme',
		themes: {
			lightTheme: {
				dark: false,
				colors: {
					primary: '#47545D',
					darkPrimary: '#47545D',
					success: '#2C632C',
					lightPrimary: '#fff',
					secondary: '#868f96',
					accent: '#c09762',
					error: '#dc2626',
					background: '#EDEEEF',
					layout: '#47545D',
					tableHeader: '#868f96',
				},
			},
		},
	},
	locale: {
		locale: 'de',
		fallback: 'en',
	},
	date: {
		locale: {
			de: 'de-DE',
		},
	},
	components: { ...components, VCalendar, VDateInput },
	directives,
});

createInertiaApp({
	title: title => `${title} - ${appName}`,
	resolve: name => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
	setup({ el, App, props, plugin }) {
		createApp({ render: () => h(App, props) })
			.use(plugin)
			.use(ZiggyVue)
			.use(vuetify)
			.mount(el);
	},
	progress: {
		color: '#4B5563',
	},
});
