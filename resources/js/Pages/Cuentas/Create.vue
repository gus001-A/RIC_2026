<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useNotify } from '@/composables/useNotify';

const notify = useNotify();

const props = defineProps({
    empresas: { type: Array, default: () => [] },
});

const form = useForm({
    id_empresa: props.empresas.length === 1 ? props.empresas[0].id : '',
    codigo_cuenta: '',
    nombre_cuenta: '',
    descripcion: '',
    Naturaleza: 'DEUDORA',
    nivel: 1,
    en_uso: true,
    es_cuenta_resultados: false,
    fondeo_c: false,
    cuenta_resultados: '',
});

// tipo: 'normal' | 'resultados' | 'fondeadora'
const tipo = ref('normal');
watch(tipo, (t) => {
    form.es_cuenta_resultados = t === 'resultados';
    form.fondeo_c = t === 'fondeadora';
    if (t !== 'normal') form.cuenta_resultados = '';
});

// ---- Cuentas de resultados de la empresa (para el select "pertenece a") ----
const cuentasResultados = ref([]);
const cargandoResultados = ref(false);

const cargarCuentasResultados = async (empresaId) => {
    cuentasResultados.value = [];
    if (!empresaId) return;
    cargandoResultados.value = true;
    try {
        const { data } = await axios.get(route('cuentas.get-cuentas-resultados'), {
            params: { empresa_id: empresaId },
        });
        cuentasResultados.value = data?.data || [];
    } catch (e) {
        cuentasResultados.value = [];
    } finally {
        cargandoResultados.value = false;
    }
};

watch(() => form.id_empresa, (id) => {
    form.cuenta_resultados = '';
    cargarCuentasResultados(id);
});

onMounted(() => {
    if (form.id_empresa) cargarCuentasResultados(form.id_empresa);
});

const empresaNombre = computed(() => {
    const e = props.empresas.find((x) => String(x.id) === String(form.id_empresa));
    return e ? e.nombre_empresa : '';
});

const puedeGuardar = computed(() => {
    if (!form.id_empresa || !form.codigo_cuenta.trim() || !form.nombre_cuenta.trim() || !form.Naturaleza) return false;
    if (tipo.value === 'normal' && !form.cuenta_resultados) return false;
    return true;
});

const submit = () => {
    if (!puedeGuardar.value) {
        notify.error('Completa los campos obligatorios antes de continuar.', 'Faltan datos');
        return;
    }
    form
        .transform((d) => ({
            ...d,
            es_cuenta_resultados: d.es_cuenta_resultados ? 1 : 0,
            fondeo_c: d.fondeo_c ? 1 : 0,
            en_uso: d.en_uso ? 1 : 0,
            cuenta_resultados: d.cuenta_resultados || null,
        }))
        .post(route('cuentas.store'), {
            onError: () => notify.error('Revisa los datos del formulario.', 'No se pudo crear la cuenta'),
        });
};
</script>

<template>
    <AppLayout title="RIC - Nueva Cuenta">
        <template #header>
            <div class="cc-header">
                <div class="cc-header-left">
                    <div class="cc-header-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="cc-header-title">Nueva Cuenta</h2>
                        <p class="cc-header-sub">
                            Registra una cuenta contable
                            <span class="cc-hint">Los campos con <strong>*</strong> son obligatorios</span>
                        </p>
                    </div>
                </div>
                <div class="cc-header-actions">
                    <Link :href="route('cuentas.index')" class="cc-btn cc-btn-ghost">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Cancelar
                    </Link>
                    <button type="button" class="cc-btn cc-btn-primary" :class="{ pending: !puedeGuardar }" :disabled="form.processing" @click="submit">
                        <span v-if="form.processing" class="cc-spin"></span>
                        <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        {{ form.processing ? 'Guardando...' : 'Crear Cuenta' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="cc-page">
            <div class="cc-card">
                <form id="cuentaForm" @submit.prevent="submit">
                    <div class="cc-body">
                        <!-- Empresa + Naturaleza -->
                        <section class="cc-section">
                            <div class="cc-section-head">
                                <span class="cc-section-dot"></span>
                                <h3>Identificación</h3>
                            </div>
                            <div class="cc-grid">
                                <div class="cc-field cc-col-2">
                                    <label>Empresa <span class="req">*</span></label>
                                    <select v-model="form.id_empresa" :class="{ error: form.errors.id_empresa }">
                                        <option value="">Selecciona la empresa</option>
                                        <option v-for="e in empresas" :key="e.id" :value="e.id">{{ e.nombre_empresa }}</option>
                                    </select>
                                    <p v-if="form.errors.id_empresa" class="cc-err">{{ form.errors.id_empresa }}</p>
                                </div>
                                <div class="cc-field">
                                    <label>Código <span class="req">*</span></label>
                                    <input type="text" v-model="form.codigo_cuenta" maxlength="50"
                                           placeholder="Ej: 1000"
                                           :class="{ error: form.errors.codigo_cuenta }">
                                    <p v-if="form.errors.codigo_cuenta" class="cc-err">{{ form.errors.codigo_cuenta }}</p>
                                </div>
                                <div class="cc-field">
                                    <label>Nivel <span class="req">*</span></label>
                                    <select v-model.number="form.nivel">
                                        <option v-for="n in 5" :key="n" :value="n">Nivel {{ n }}</option>
                                    </select>
                                </div>
                                <div class="cc-field cc-col-4">
                                    <label>Nombre de la cuenta <span class="req">*</span></label>
                                    <input type="text" v-model="form.nombre_cuenta" maxlength="255"
                                           placeholder="Ej: Bancos"
                                           :class="{ error: form.errors.nombre_cuenta }">
                                    <p v-if="form.errors.nombre_cuenta" class="cc-err">{{ form.errors.nombre_cuenta }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Naturaleza + Tipo -->
                        <section class="cc-section">
                            <div class="cc-section-head">
                                <span class="cc-section-dot"></span>
                                <h3>Clasificación</h3>
                            </div>
                            <div class="cc-grid">
                                <div class="cc-field cc-col-2">
                                    <label>Naturaleza <span class="req">*</span></label>
                                    <div class="cc-segment">
                                        <button type="button" :class="{ on: form.Naturaleza === 'DEUDORA' }" @click="form.Naturaleza = 'DEUDORA'">Deudora</button>
                                        <button type="button" :class="{ on: form.Naturaleza === 'ACREEDORA' }" @click="form.Naturaleza = 'ACREEDORA'">Acreedora</button>
                                    </div>
                                </div>
                                <div class="cc-field cc-col-4">
                                    <label>Tipo de cuenta</label>
                                    <div class="cc-tipos">
                                        <label class="cc-tipo" :class="{ on: tipo === 'normal' }">
                                            <input type="radio" value="normal" v-model="tipo">
                                            <span class="cc-tipo-title">Normal</span>
                                            <span class="cc-tipo-desc">Pertenece a una cuenta de resultados</span>
                                        </label>
                                        <label class="cc-tipo" :class="{ on: tipo === 'resultados' }">
                                            <input type="radio" value="resultados" v-model="tipo">
                                            <span class="cc-tipo-title">De resultados</span>
                                            <span class="cc-tipo-desc">Agrupa otras cuentas</span>
                                        </label>
                                        <label class="cc-tipo" :class="{ on: tipo === 'fondeadora' }">
                                            <input type="radio" value="fondeadora" v-model="tipo">
                                            <span class="cc-tipo-title">Fondeadora</span>
                                            <span class="cc-tipo-desc">Fuente de fondeo</span>
                                        </label>
                                    </div>
                                </div>
                                <div v-if="tipo === 'normal'" class="cc-field cc-col-6">
                                    <label>Pertenece a la cuenta de resultados <span class="req">*</span></label>
                                    <select v-model="form.cuenta_resultados" :class="{ error: form.errors.cuenta_resultados }" :disabled="!form.id_empresa || cargandoResultados">
                                        <option value="">
                                            {{ !form.id_empresa ? 'Selecciona primero una empresa' : (cargandoResultados ? 'Cargando…' : 'Selecciona la cuenta de resultados') }}
                                        </option>
                                        <option v-for="c in cuentasResultados" :key="c.id_cuenta" :value="c.id_cuenta">{{ c.display }}</option>
                                    </select>
                                    <p v-if="form.id_empresa && !cargandoResultados && !cuentasResultados.length" class="cc-hint-line">
                                        Esta empresa no tiene cuentas de resultados. Crea una primero (tipo «De resultados»).
                                    </p>
                                    <p v-if="form.errors.cuenta_resultados" class="cc-err">{{ form.errors.cuenta_resultados }}</p>
                                </div>
                            </div>
                        </section>

                        <!-- Detalle -->
                        <section class="cc-section">
                            <div class="cc-section-head">
                                <span class="cc-section-dot"></span>
                                <h3>Detalle</h3>
                            </div>
                            <div class="cc-grid">
                                <div class="cc-field cc-col-4">
                                    <label>Descripción</label>
                                    <textarea v-model="form.descripcion" rows="2" placeholder="Descripción opcional de la cuenta"></textarea>
                                </div>
                                <div class="cc-field cc-col-2 cc-field-switch">
                                    <label>Estado</label>
                                    <button type="button" class="cc-switch" :class="{ on: form.en_uso }" @click="form.en_uso = !form.en_uso">
                                        <span class="cc-switch-track"><span class="cc-switch-thumb"></span></span>
                                        <span class="cc-switch-label">{{ form.en_uso ? 'Activa' : 'Inactiva' }}</span>
                                    </button>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="cc-foot">
                        <span class="cc-foot-info">
                            <template v-if="empresaNombre">Empresa: <strong>{{ empresaNombre }}</strong></template>
                        </span>
                        <div class="cc-foot-actions">
                            <Link :href="route('cuentas.index')" class="cc-btn cc-btn-ghost">Cancelar</Link>
                            <button type="submit" class="cc-btn cc-btn-primary" :class="{ pending: !puedeGuardar }" :disabled="form.processing">
                                {{ form.processing ? 'Guardando...' : 'Crear Cuenta' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.cc-header { display: flex; align-items: center; justify-content: space-between; gap: 16px; width: 100%; flex-wrap: wrap; }
.cc-header-left { display: flex; align-items: center; gap: 14px; }
.cc-header-icon { width: 44px; height: 44px; border-radius: 13px; background: #1a3a5c; color: #fff; display: flex; align-items: center; justify-content: center; }
.cc-header-icon svg { width: 22px; height: 22px; }
.cc-header-title { font-size: 1.25rem; font-weight: 700; color: #0f2136; margin: 0; }
.cc-header-sub { font-size: 0.85rem; color: #6b7280; margin: 2px 0 0; display: flex; align-items: center; flex-wrap: wrap; gap: 4px; }
.cc-hint { margin-left: 8px; padding-left: 10px; border-left: 1px solid #d1d5db; font-size: 0.78rem; color: #9ca3af; }
.cc-hint strong { color: #dc2626; }
.cc-header-actions { display: flex; gap: 10px; }

.cc-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 20px; border-radius: 11px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.16s ease; }
.cc-btn svg { width: 16px; height: 16px; }
.cc-btn-ghost { background: #f1f5f9; color: #475569; }
.cc-btn-ghost:hover { background: #e2e8f0; }
.cc-btn-primary { background: #1a3a5c; color: #fff; }
.cc-btn-primary:hover:not(:disabled) { background: #14304c; transform: translateY(-1px); }
.cc-btn-primary.pending { opacity: 0.7; }
.cc-btn-primary:disabled { cursor: default; }
.cc-spin { width: 15px; height: 15px; border: 2px solid rgba(255,255,255,0.4); border-top-color: #fff; border-radius: 50%; animation: cc-spin 0.7s linear infinite; }
@keyframes cc-spin { to { transform: rotate(360deg); } }

.cc-page { padding: 0.75rem 0; }
.cc-card {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 1.25rem;
}
.cc-card form {
    background: #fff;
    border: 1px solid #eef1f4;
    border-radius: 18px;
    box-shadow: 0 4px 24px rgba(15, 23, 42, 0.05);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 190px);
    overflow: hidden;
}
.cc-body { flex: 1; overflow-y: auto; padding: 20px 24px; display: flex; flex-direction: column; gap: 20px; }
.cc-body::-webkit-scrollbar { width: 6px; }
.cc-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

.cc-section { }
.cc-section-head { display: flex; align-items: center; gap: 9px; margin-bottom: 12px; }
.cc-section-dot { width: 8px; height: 8px; border-radius: 3px; background: #1a3a5c; }
.cc-section-head h3 { font-size: 0.9rem; font-weight: 700; color: #0f2136; margin: 0; }

.cc-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 14px; }
.cc-field { display: flex; flex-direction: column; gap: 5px; grid-column: span 2; }
.cc-col-2 { grid-column: span 2; }
.cc-col-4 { grid-column: span 4; }
.cc-col-6 { grid-column: span 6; }

.cc-field label { font-size: 0.78rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px; }
.req { color: #dc2626; }

.cc-field input,
.cc-field select,
.cc-field textarea {
    width: 100%;
    padding: 9px 12px;
    font-size: 0.85rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    color: #1f2937;
    outline: none;
    transition: border-color 0.16s ease, box-shadow 0.16s ease;
}
.cc-field textarea { resize: vertical; min-height: 42px; }
.cc-field input:focus,
.cc-field select:focus,
.cc-field textarea:focus { border-color: #1a3a5c; box-shadow: 0 0 0 3px rgba(26,58,92,0.12); }
.cc-field input.error,
.cc-field select.error { border-color: #ef4444; }
.cc-field select:disabled { background: #f1f5f9; color: #94a3b8; }

.cc-err { font-size: 0.72rem; color: #dc2626; margin: 0; }
.cc-hint-line { font-size: 0.72rem; color: #9ca3af; margin: 0; }

.cc-badge { font-size: 0.62rem; font-weight: 700; padding: 2px 7px; border-radius: 6px; color: #fff; text-transform: uppercase; letter-spacing: 0.03em; }
.cc-badge.red { background: #ef4444; }
.cc-badge.green { background: #16a34a; }

.cc-segment { display: flex; gap: 6px; background: #f1f5f9; padding: 4px; border-radius: 11px; }
.cc-segment button {
    flex: 1; padding: 8px 10px; border: none; background: transparent; border-radius: 8px;
    font-size: 0.82rem; font-weight: 600; color: #64748b; cursor: pointer; transition: all 0.15s ease;
}
.cc-segment button.on { background: #fff; color: #1a3a5c; box-shadow: 0 1px 4px rgba(15,23,42,0.1); }

.cc-tipos { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.cc-tipo {
    display: flex; flex-direction: column; gap: 3px; padding: 11px 13px;
    border: 2px solid #e5e7eb; border-radius: 12px; cursor: pointer; transition: all 0.15s ease;
}
.cc-tipo input { display: none; }
.cc-tipo.on { border-color: #1a3a5c; background: rgba(26,58,92,0.04); }
.cc-tipo-title { font-size: 0.82rem; font-weight: 700; color: #0f2136; }
.cc-tipo-desc { font-size: 0.7rem; color: #94a3b8; line-height: 1.3; }

.cc-field-switch { justify-content: flex-start; }
.cc-switch { display: inline-flex; align-items: center; gap: 10px; background: none; border: none; cursor: pointer; padding: 4px 0; }
.cc-switch-track { width: 42px; height: 24px; border-radius: 999px; background: #e5e7eb; position: relative; transition: background 0.18s ease; }
.cc-switch-thumb { position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.2); transition: transform 0.18s ease; }
.cc-switch.on .cc-switch-track { background: #16a34a; }
.cc-switch.on .cc-switch-thumb { transform: translateX(18px); }
.cc-switch-label { font-size: 0.82rem; font-weight: 600; color: #475569; }

.cc-foot { flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 24px; border-top: 1px solid #eef1f4; background: #fff; }
.cc-foot-info { font-size: 0.78rem; color: #94a3b8; }
.cc-foot-info strong { color: #475569; }
.cc-foot-actions { display: flex; gap: 10px; }

@media (max-width: 900px) {
    .cc-grid { grid-template-columns: repeat(2, 1fr); }
    .cc-field, .cc-col-2, .cc-col-4, .cc-col-6 { grid-column: span 2; }
    .cc-tipos { grid-template-columns: 1fr; }
    .cc-card form { max-height: none; overflow: visible; }
    .cc-body { overflow: visible; }
}
</style>
