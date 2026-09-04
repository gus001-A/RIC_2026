import { watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

// Mapea las claves de flash de Laravel a la severidad / título de PrimeVue Toast
const SEVERITY = {
    success: 'success',
    created: 'success',
    updated: 'success',
    deleted: 'success',
    error: 'error',
    warning: 'warn',
    info: 'info',
};

const SUMMARY = {
    success: 'Éxito',
    created: 'Creado',
    updated: 'Actualizado',
    deleted: 'Eliminado',
    error: 'Error',
    warning: 'Advertencia',
    info: 'Información',
};

/**
 * Escucha `page.props.flash` y muestra un toast por cada mensaje.
 * Se monta una sola vez, en el layout principal.
 *
 * OJO: `toast.add()` sólo funciona después de que <Toast> está montado.
 * Por eso el primer disparo va en onMounted (no con `immediate`).
 */
export function useFlashMessages() {
    const page = usePage();
    const toast = useToast();

    const mostrados = new WeakSet();

    const flush = (flash) => {
        if (!flash || mostrados.has(flash)) return;
        mostrados.add(flash);

        for (const [key, message] of Object.entries(flash)) {
            if (!message || !SEVERITY[key]) continue;

            toast.add({
                severity: SEVERITY[key],
                summary: SUMMARY[key],
                detail: message,
                life: SEVERITY[key] === 'error' ? 5000 : 4000,
            });
        }
    };

    onMounted(() => flush(page.props.flash));

    watch(
        () => page.props.flash,
        (flash) => flush(flash),
        { deep: true },
    );
}
