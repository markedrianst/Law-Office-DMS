<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @click.self="$emit('close')">
      <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
      
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-slate-800">Quick Create Client</h2>
              <p class="text-sm text-slate-500">Add a new client and auto-assign to case</p>
            </div>
          </div>
          <button @click="$emit('close')" :disabled="saving" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4">
          
          <!-- Full Name -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Full Name <span class="text-red-500">*</span>
            </label>
            <input v-model="form.full_name" type="text" placeholder="Enter client's full name"
              class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 transition-all"
              :class="{ 'border-red-400': errors.full_name }" />
            <p v-if="errors.full_name" class="text-xs text-red-500 mt-1">{{ errors.full_name }}</p>
          </div>

          <!-- Contact Number -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Contact Number <span class="text-slate-400 font-normal text-xs">(Optional)</span>
            </label>
            <input v-model="form.contact_no" type="text" placeholder="09XXXXXXXXX"
              @keypress="(e) => { if (!/[0-9]/.test(e.key)) e.preventDefault() }"
              @input="form.contact_no = form.contact_no.replace(/\D/g, '')"
              maxlength="11"
              class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 transition-all"
              :class="{ 'border-red-400': errors.contact_no }" />
            <p v-if="errors.contact_no" class="text-xs text-red-500 mt-1">{{ errors.contact_no }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Email Address <span class="text-slate-400 font-normal text-xs">(Optional)</span>
            </label>
            <input v-model="form.email" type="email" placeholder="client@example.com"
              class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 transition-all"
              :class="{ 'border-red-400': errors.email }" />
            <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
          </div>

          <!-- Address -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Address <span class="text-slate-400 font-normal text-xs">(Optional)</span>
            </label>
            <input v-model="form.address" type="text" placeholder="Complete address"
              class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-emerald-500 transition-all" />
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
          <button @click="$emit('close')" :disabled="saving"
            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
            Cancel
          </button>
          <button @click="submitForm" :disabled="saving"
            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 min-w-[120px] justify-center bg-gradient-to-r from-emerald-500 to-emerald-700 shadow-md hover:shadow-lg transition-all">
            <svg v-if="saving" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ saving ? 'Creating...' : 'Create Client' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import clientService from '@/services/clientService';

const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'saved']);

// Form state
const form = reactive({
  full_name: '',
  contact_no: '',
  email: '',
  address: ''
});

const errors = reactive({
  full_name: '',
  contact_no: '',
  email: ''
});

const saving = ref(false);

// Reset form when modal opens
watch(() => props.show, (newVal) => {
  if (newVal) {
    form.full_name = '';
    form.contact_no = '';
    form.email = '';
    form.address = '';
    errors.full_name = '';
    errors.contact_no = '';
    errors.email = '';
  }
});

// Clear errors when typing
const clearErrors = () => {
  errors.full_name = '';
  errors.contact_no = '';
  errors.email = '';
};

// Validate form
const validateForm = () => {
  clearErrors();
  let isValid = true;

  if (!form.full_name.trim()) {
    errors.full_name = 'Full name is required';
    isValid = false;
  }

  if (form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Invalid email format';
    isValid = false;
  }

  if (form.contact_no && form.contact_no.length < 11) {
    errors.contact_no = 'Contact number must be 11 digits';
    isValid = false;
  }

  return isValid;
};

// Submit form
const submitForm = async () => {
  if (!validateForm()) return;

  saving.value = true;

  try {
    const response = await clientService.create({
      full_name: form.full_name,
      contact_no: form.contact_no || null,
      email: form.email || null,
      address: form.address || null
    });

    emit('saved', response.data);
    emit('close');

  } catch (error) {
    if (error.errors) {
      if (error.errors.full_name) errors.full_name = error.errors.full_name[0];
      if (error.errors.contact_no) errors.contact_no = error.errors.contact_no[0];
      if (error.errors.email) errors.email = error.errors.email[0];
    }
  } finally {
    saving.value = false;
  }
};
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: all 0.25s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>