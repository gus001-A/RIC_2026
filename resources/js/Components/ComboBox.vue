<script setup>
import { ref, computed, nextTick } from 'vue';

/**
 * Combo editable: input de texto + lista desplegable con estilo propio.
 * Permite escribir libremente y a la vez elegir de una lista filtrada.
 *
 *   <ComboBox v-model="form.municipio" :options="municipios" placeholder="Municipio" />
 */
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, default: () => [] },        // ['Cuernavaca', ...] ó [{ label, value }]
    placeholder: { type: String, default: '' },
    inputClass: { type: [String, Array, Object], default: '' },
    disabled: { type: Boolean, default: false },
    emptyText: { type: String, default: 'Sin coincidencias' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const activeIndex = ref(-1);
const rootEl = ref(null);
const inputEl = ref(null);

const normalize = (s) =>
    String(s ?? '')
        .toLowerCase()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '');

const asItem = (o) => (typeof o === 'object' && o !== null ? o : { label: String(o), value: o });

const filtered = computed(() => {
    const q = normalize(props.modelValue);
    const items = props.options.map(asItem);
    if (!q) return items.slice(0, 60);
    return items.filter((i) => normalize(i.label).includes(q)).slice(0, 60);
});

const openList = () => {
    if (props.disabled) return;
    open.value = true;
    activeIndex.value = -1;
};

const closeList = () => {
    open.value = false;
    activeIndex.value = -1;
};

const onInput = (e) => {
    emit('update:modelValue', e.target.value);
    open.value = true;
};

const pick = (item) => {
    emit('update:modelValue', item.value);
    closeList();
    nextTick(() => inputEl.value?.blur());
};

const onKeydown = (e) => {
    if (!open.value && ['ArrowDown', 'ArrowUp'].includes(e.key)) {
        openList();
        return;
    }
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex.value = Math.min(activeIndex.value + 1, filtered.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex.value = Math.max(activeIndex.value - 1, 0);
    } else if (e.key === 'Enter') {
        if (open.value && activeIndex.value >= 0 && filtered.value[activeIndex.value]) {
            e.preventDefault();
            pick(filtered.value[activeIndex.value]);
        }
    } else if (e.key === 'Escape') {
        closeList();
    }
};

const onBlur = () => {
    // pequeño delay para permitir el click en un item
    setTimeout(closeList, 120);
};
</script>

<template>
    <div ref="rootEl" class="combo" :class="{ 'combo-open': open }">
        <input
            ref="inputEl"
            type="text"
            :value="modelValue"
            :placeholder="placeholder"
            :class="inputClass"
            :disabled="disabled"
            autocomplete="off"
            role="combobox"
            :aria-expanded="open"
            @input="onInput"
            @focus="openList"
            @blur="onBlur"
            @keydown="onKeydown"
        />
        <span class="combo-caret" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </span>

        <transition name="combo-fade">
            <ul v-if="open" class="combo-list" role="listbox">
                <li v-if="!filtered.length" class="combo-empty">{{ emptyText }}</li>
                <li
                    v-for="(item, i) in filtered"
                    :key="item.value ?? i"
                    class="combo-item"
                    :class="{
                        'is-active': i === activeIndex,
                        'is-selected': String(item.value) === String(modelValue),
                    }"
                    role="option"
                    :aria-selected="String(item.value) === String(modelValue)"
                    @mousedown.prevent="pick(item)"
                    @mouseenter="activeIndex = i"
                >
                    <span class="combo-item-label">{{ item.label }}</span>
                    <svg v-if="String(item.value) === String(modelValue)" class="combo-item-check" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </li>
            </ul>
        </transition>
    </div>
</template>

<style scoped>
.combo {
    position: relative;
    width: 100%;
}

.combo input {
    width: 100%;
    padding: 9px 34px 9px 14px;
    font-size: 0.85rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: #fff;
    color: #1f2937;
    outline: none;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
}

.combo input::placeholder {
    color: #9ca3af;
}

.combo input:hover:not(:focus):not(:disabled) {
    border-color: #cbd5e1;
}

.combo input:focus {
    border-color: #1a3a5c;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.12);
}

.combo input:disabled {
    background: #f1f5f9;
    color: #94a3b8;
    cursor: not-allowed;
}

.combo input.error {
    border-color: #ef4444;
}

.combo-caret {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: #94a3b8;
    pointer-events: none;
    transition: transform 0.18s ease;
}

.combo-open .combo-caret {
    transform: translateY(-50%) rotate(180deg);
}

.combo-list {
    position: absolute;
    z-index: 60;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    margin: 0;
    padding: 6px;
    list-style: none;
    max-height: 240px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 16px 40px -12px rgba(15, 23, 42, 0.28), 0 0 0 1px rgba(15, 23, 42, 0.03);
}

.combo-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 12px;
    border-radius: 9px;
    font-size: 0.85rem;
    color: #334155;
    cursor: pointer;
    transition: background 0.12s ease, color 0.12s ease;
}

.combo-item.is-active {
    background: rgba(26, 58, 92, 0.08);
    color: #0f2136;
}

.combo-item.is-selected {
    color: #1a3a5c;
    font-weight: 600;
}

.combo-item-label {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.combo-item-check {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    color: #1a3a5c;
}

.combo-empty {
    padding: 12px;
    text-align: center;
    font-size: 0.82rem;
    color: #94a3b8;
}

.combo-list::-webkit-scrollbar { width: 6px; }
.combo-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

.combo-fade-enter-active,
.combo-fade-leave-active {
    transition: opacity 0.14s ease, transform 0.14s ease;
}

.combo-fade-enter-from,
.combo-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}
</style>
