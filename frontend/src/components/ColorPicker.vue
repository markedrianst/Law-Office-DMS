<template>
  <div class="relative">
    <button
      type="button"
      @click="isOpen = !isOpen"
      class="w-10 h-10 rounded-lg border-2 border-slate-200 shadow-sm hover:shadow-md transition-all"
      :style="{ backgroundColor: modelValue }"
    ></button>
    
    <Transition name="dropdown">
      <div v-if="isOpen" class="absolute z-50 mt-2 p-3 bg-white rounded-xl shadow-xl border border-slate-200 w-64">
        <div class="mb-2 px-1 flex items-center justify-between">
          <span class="text-xs font-semibold text-slate-500">Pick a color</span>
          <button @click="isOpen = false" class="text-slate-400 hover:text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        
        <!-- Preset colors -->
        <div class="grid grid-cols-6 gap-2 mb-3">
          <button
            v-for="color in presetColors"
            :key="color"
            @click="selectColor(color)"
            class="w-8 h-8 rounded-lg border-2 transition-transform hover:scale-110"
            :style="{ backgroundColor: color, borderColor: modelValue === color ? '#1a4972' : 'transparent' }"
          ></button>
        </div>
        
        <!-- Custom color input -->
        <div class="flex items-center gap-2">
          <input
            type="color"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-10 h-10 rounded cursor-pointer"
          />
          <input
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            placeholder="#HEX"
            class="flex-1 px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-[#1a4972]"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: '#1a4972'
  }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);

const presetColors = [
  '#dc2626', '#ea580c', '#d97706', '#eab308', '#16a34a', '#10b981',
  '#06b6d4', '#2563eb', '#4f46e5', '#7c3aed', '#9333ea', '#db2777',
  '#64748b', '#6b7280', '#1a4972', '#0f2f4a', '#000000', '#ffffff'
];

const selectColor = (color) => {
  emit('update:modelValue', color);
  isOpen.value = false;
};
</script>

<style scoped>
.dropdown-enter-active, .dropdown-leave-active {
  transition: all 0.2s ease;
}
.dropdown-enter-from, .dropdown-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>