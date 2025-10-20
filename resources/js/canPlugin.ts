import { usePage } from '@inertiajs/vue3';
import { App } from 'vue';

export default {
    install: (app: App<Element>) => {
        window['can'] = function (model, method, context) {
            const page = usePage();
            let value;
            if (context) {
                value = context.can[model]?.[method];
            } else {
                value = page.props.can?.[model]?.[method] ?? page.props.appGlobalCan?.[model]?.[method] ?? page.props.globalCan[model]?.[method];
            }
            if (value === undefined)
                throw new Error(
                    `cannot read ability ${model}.${method} in page.props.can || page.props.appGlobalCan || page.props.globalCan || context.can, did you forget to provide the ability in the controller?`,
                );
            return value;
        };
        app.config.globalProperties['can'] = window.can;
    },
};
