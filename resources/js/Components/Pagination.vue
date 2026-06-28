<template>
    <div v-if="links.length > 3" class="flex items-center justify-between">
        <div class="flex-1 flex justify-between sm:hidden">
            <Link v-if="links[0].url" :href="links[0].url" 
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Anterior
            </Link>
            <Link v-if="links[links.length - 1].url" :href="links[links.length - 1].url" 
                  class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Siguiente
            </Link>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Mostrando 
                    <span class="font-medium">{{ links[0].label }}</span>
                    a 
                    <span class="font-medium">{{ links[links.length - 1].label }}</span>
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <template v-for="(link, key) in links" :key="key">
                        <Link v-if="link.url" :href="link.url" 
                              :class="[
                                  link.active 
                                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' 
                                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                              ]"
                              v-html="link.label" />
                        <span v-else 
                              class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                              v-html="link.label" />
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true
    }
});
</script>