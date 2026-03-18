<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="close">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
      
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-slate-800">Import Excel Data</h2>
              <p class="text-sm text-slate-500">Upload your Excel file to import cases</p>
            </div>
          </div>
          <button @click="close" :disabled="uploading" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">
          
          <!-- Assignment Section -->
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
              Assignment
            </h4>
            
            <!-- Assigned Lawyer -->
            <div class="mb-3">
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Assigned Lawyer <span class="text-red-500">*</span>
              </label>
              <select 
                v-model="selectedLawyer"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-emerald-500 transition-all"
                :disabled="uploading"
              >
                <option value="">— Select Lawyer —</option>
                <option v-for="lawyer in lawyers" :key="lawyer.id" :value="lawyer.id">
                  Atty. {{ lawyer.full_name }}
                </option>
              </select>
              <p class="text-xs text-slate-400 mt-1">This lawyer will be assigned to all imported cases</p>
            </div>

            <!-- Assigned Clerk -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                Assigned Clerk <span class="text-slate-400 text-xs">(Optional)</span>
              </label>
              <select 
                v-model="selectedClerk"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-emerald-500 transition-all"
                :disabled="uploading"
              >
                <option value="">— Select Clerk —</option>
                <option v-for="clerk in clerks" :key="clerk.id" :value="clerk.id">
                  {{ clerk.full_name }}
                </option>
              </select>
              <p class="text-xs text-slate-400 mt-1">This clerk will be assigned to all imported cases</p>
            </div>
          </div>

          <!-- Category Selection -->
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l5 5a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-5-5A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
              </svg>
              Case Category
            </h4>
            <select 
              v-model="selectedCategory"
              class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-emerald-500 transition-all"
              :disabled="uploading"
            >
              <option value="">— Select Category —</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <p class="text-xs text-slate-400 mt-1">This category will be applied to all imported cases</p>
          </div>

          <!-- File Input -->
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2-10H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2z"/>
              </svg>
              Excel File
            </h4>
            <input 
              type="file" 
              ref="fileInput"
              @change="handleFileSelect"
              accept=".xlsx,.xls"
              class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:bg-white focus:outline-none focus:border-emerald-500 transition-all"
              :disabled="uploading || !selectedLawyer || !selectedCategory"
            />
            <p class="text-xs text-slate-400 mt-1">Supported formats: .xlsx, .xls (Max 10MB)</p>
          </div>

          <!-- Selected File Info -->
          <div v-if="selectedFile" class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2-10H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V6a2 2 0 00-2-2z"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">{{ selectedFile.name }}</p>
                <p class="text-xs text-slate-500">{{ (selectedFile.size / 1024).toFixed(2) }} KB</p>
              </div>
              <button @click="clearFile" class="p-1 hover:bg-emerald-200 rounded-lg">
                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Import Options -->
          <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
            <h4 class="text-sm font-semibold text-slate-700 mb-3 flex items-center gap-2">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Import Options
            </h4>
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="importCourts" class="w-4 h-4 rounded border-slate-300 text-emerald-600">
              <span class="text-sm text-slate-600">Import courts/offices to Court Master</span>
            </label>
            <p class="text-xs text-slate-400 mt-1 ml-6">Will extract unique court/office names and add them to Court Master</p>
          </div>

          <!-- Progress Bar -->
          <div v-if="uploading" class="space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-600">Importing...</span>
              <span class="text-emerald-600 font-semibold">{{ uploadProgress }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600 transition-all duration-300" 
                   :style="{ width: uploadProgress + '%' }"></div>
            </div>
          </div>

          <!-- Success Message -->
          <div v-if="success" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <p class="text-sm text-emerald-800">{{ successMessage }}</p>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
              <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>

        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
          <button @click="close" :disabled="uploading"
            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
            Cancel
          </button>
          <button @click="uploadFile" :disabled="!selectedFile || !selectedCategory || !selectedLawyer || uploading"
            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 min-w-[120px] justify-center bg-gradient-to-r from-emerald-600 to-emerald-700 shadow-md hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <svg v-if="uploading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            {{ uploading ? 'Importing...' : 'Import Data' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import importService from '@/services/importService';
import caseService from '@/services/caseService';
import Swal from 'sweetalert2';

const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'imported']);

const fileInput = ref(null);
const selectedFile = ref(null);
const selectedCategory = ref('');
const selectedLawyer = ref('');
const selectedClerk = ref('');
const categories = ref([]);
const lawyers = ref([]);
const clerks = ref([]);
const importCourts = ref(true);
const uploading = ref(false);
const uploadProgress = ref(0);
const success = ref(false);
const successMessage = ref('');
const error = ref(false);
const errorMessage = ref('');

// Load lookups on mount
onMounted(async () => {
  try {
    const response = await caseService.getLookups();
    categories.value = response.data?.categories || [];
    lawyers.value = response.data?.lawyers || [];
    clerks.value = response.data?.clerks || [];
  } catch (error) {
    console.error('Failed to load lookups:', error);
  }
});

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) {
    selectedFile.value = file;
    error.value = false;
    success.value = false;
  }
};

const clearFile = () => {
  selectedFile.value = null;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const uploadFile = async () => {
  if (!selectedFile.value || !selectedCategory.value || !selectedLawyer.value) return;

  const formData = new FormData();
  formData.append('file', selectedFile.value);
  formData.append('category_id', selectedCategory.value);
  formData.append('lawyer_id', selectedLawyer.value);
  formData.append('clerk_id', selectedClerk.value || '');
  formData.append('import_courts', importCourts.value ? '1' : '0');

  uploading.value = true;
  uploadProgress.value = 0;
  error.value = false;
  success.value = false;

  // Simulate progress
  const progressInterval = setInterval(() => {
    if (uploadProgress.value < 90) {
      uploadProgress.value += 10;
    }
  }, 300);

  try {
    const response = await importService.importExcel(formData);
    
    clearInterval(progressInterval);
    uploadProgress.value = 100;
    
    if (response.success) {
      success.value = true;
      successMessage.value = response.message;
      
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: response.message,
        timer: 3000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

      setTimeout(() => {
        close();
        emit('imported');
      }, 1500);
    }

  } catch (err) {
    clearInterval(progressInterval);
    uploadProgress.value = 0;
    
    error.value = true;
    errorMessage.value = err.response?.data?.message || 'Import failed. Please check your file.';
    
    Swal.fire({
      icon: 'error',
      title: 'Import Failed',
      text: errorMessage.value,
      confirmButtonColor: '#dc2626'
    });

  } finally {
    uploading.value = false;
  }
};

const close = () => {
  if (!uploading.value) {
    clearFile();
    selectedCategory.value = '';
    selectedLawyer.value = '';
    selectedClerk.value = '';
    emit('close');
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

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>