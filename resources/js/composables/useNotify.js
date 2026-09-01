import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

/**
 * Helper unificado para notificaciones y confirmaciones.
 * Reemplaza el uso directo de SweetAlert2 en las páginas.
 *
 *   const notify = useNotify();
 *   notify.success('Guardado correctamente');
 *   notify.confirmDelete({ message: '¿Eliminar este registro?', accept: () => ... });
 */
export function useNotify() {
    const toast = useToast();
    const confirm = useConfirm();

    const success = (detail, summary = 'Éxito') =>
        toast.add({ severity: 'success', summary, detail, life: 4000 });

    const error = (detail, summary = 'Error') =>
        toast.add({ severity: 'error', summary, detail, life: 5000 });

    const info = (detail, summary = 'Información') =>
        toast.add({ severity: 'info', summary, detail, life: 4000 });

    const warn = (detail, summary = 'Advertencia') =>
        toast.add({ severity: 'warn', summary, detail, life: 4500 });

    const confirmDelete = ({
        message,
        header = 'Confirmar eliminación',
        acceptLabel = 'Sí, eliminar',
        accept,
        reject,
    }) =>
        confirm.require({
            message,
            header,
            icon: 'pi pi-exclamation-triangle',
            rejectProps: { label: 'Cancelar', severity: 'secondary', outlined: true },
            acceptProps: { label: acceptLabel, severity: 'danger' },
            accept,
            reject,
        });

    const confirmAction = ({
        message,
        header = 'Confirmar',
        acceptLabel = 'Confirmar',
        acceptSeverity = null,
        icon = 'pi pi-question-circle',
        accept,
        reject,
    }) => {
        // severity 'primary' no existe en PrimeVue Button (usa el estilo por defecto)
        const sev = acceptSeverity && acceptSeverity !== 'primary' ? acceptSeverity : undefined;
        return confirm.require({
            message,
            header,
            icon,
            rejectProps: { label: 'Cancelar', severity: 'secondary', outlined: true },
            acceptProps: { label: acceptLabel, ...(sev ? { severity: sev } : {}) },
            accept,
            reject,
        });
    };

    return { toast, confirm, success, error, info, warn, confirmDelete, confirmAction };
}
