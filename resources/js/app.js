import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';

// ✅ Ant Design
import {
    Button,
    Input,
    Select,
    Table,
    DatePicker,
    Modal,
    Tag,
} from 'ant-design-vue';
import 'ant-design-vue/dist/reset.css';

// ✅ Componente de alerta personalizado
import AlertModal from '@/Components/AlertModal.vue';

createInertiaApp({
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob('./Pages/**/*.vue')
    ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // ✅ Componentes Ant Design
        app.component('AButton', Button);
        app.component('AInput', Input);
        app.component('ASelect', Select);
        app.component('ATable', Table);
        app.component('ADatePicker', DatePicker);
        app.component('AModal', Modal);
        app.component('ATag', Tag);

        // ✅ Registrar componente de alerta global
        app.component('AlertModal', AlertModal);

        return app.mount(el);
    },
});