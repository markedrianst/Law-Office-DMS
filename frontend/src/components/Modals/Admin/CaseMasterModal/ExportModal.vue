<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="close">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
      
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-slate-800">Export Data</h2>
              <p class="text-sm text-slate-500">Choose export options</p>
            </div>
          </div>
          <button @click="close" :disabled="exporting" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">
          
          <!-- Format Selection -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Format <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-3 gap-2">
              <button type="button" @click="format = 'excel'"
                class="px-3 py-2.5 text-sm font-semibold rounded-xl border transition-all"
                :class="format === 'excel' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">
                📊 Excel
              </button>
              <button type="button" @click="format = 'pdf'"
                class="px-3 py-2.5 text-sm font-semibold rounded-xl border transition-all"
                :class="format === 'pdf' ? 'bg-red-600 text-white border-red-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'">
                📄 PDF
              </button>
            </div>
          </div>

          <!-- Export Type -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Export Type <span class="text-red-500">*</span>
            </label>
            <div class="space-y-2">
              <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-slate-50 transition"
                :class="{ 'border-violet-500 bg-violet-50': exportType === 'cases' }">
                <input type="radio" v-model="exportType" value="cases" class="w-4 h-4 text-violet-600">
                <div>
                  <p class="text-sm font-semibold text-slate-800">Cases Only</p>
                  <p class="text-xs text-slate-500">Export cases in import-compatible format</p>
                </div>
              </label>
              
              <!-- Full Backup — admin only -->
              <label
                class="flex items-center gap-3 p-3 border rounded-xl transition"
                :class="[
                  isAdmin
                    ? 'cursor-pointer hover:bg-slate-50 ' + (exportType === 'all' ? 'border-violet-500 bg-violet-50' : 'border-slate-200')
                    : 'cursor-not-allowed opacity-50 bg-slate-50 border-slate-200'
                ]"
              >
                <input type="radio" v-model="exportType" value="all" :disabled="!isAdmin" class="w-4 h-4 text-violet-600">
                <div>
                  <div class="flex items-center gap-2">
                    <p class="text-sm font-semibold text-slate-800">Full Backup</p>
                    <span v-if="!isAdmin" class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-200 text-slate-500">Admin Only</span>
                  </div>
                  <p class="text-xs text-slate-500">Export all data (users, clients, categories, cases)</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Category Filter -->
          <div v-if="exportType === 'cases'">
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">
              Filter by Category
            </label>
            <select 
              v-model="selectedCategory"
              class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-white focus:outline-none focus:border-violet-500 transition-all"
            >
              <option value="">All Categories</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <!-- Group by Category Option (Excel only) -->
          <div v-if="exportType === 'cases' && format === 'excel'" 
               class="bg-violet-50 rounded-xl p-4 border border-violet-200">
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="groupByCategory" class="w-4 h-4 rounded border-slate-300 text-violet-600">
              <span class="text-sm font-semibold text-slate-700">Separate files per category</span>
            </label>
            <p class="text-xs text-slate-500 mt-1 ml-6">
              Creates a ZIP file with one Excel file per category (Criminal, Civil, Admin, etc.)
            </p>
          </div>

          <!-- Info Box -->
          <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div>
                <p class="text-sm text-blue-800">
                  <span class="font-semibold">{{ format === 'pdf' ? 'PDF Report' : 'Import Compatible Format' }}</span><br>
                  {{ format === 'pdf' 
                    ? 'Generate a formatted PDF report with case details and checklists' 
                    : 'The exported Excel file matches exactly the format your import system expects' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div v-if="exporting" class="space-y-2">
            <div class="flex items-center justify-between text-xs">
              <span class="text-slate-600">{{ format === 'pdf' ? 'Generating PDF...' : 'Exporting...' }}</span>
              <span class="text-violet-600 font-semibold">{{ exportProgress }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-violet-500 to-violet-600 transition-all duration-300" 
                   :style="{ width: exportProgress + '%' }"></div>
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
          <button @click="close" :disabled="exporting"
            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
            Cancel
          </button>
          <button @click="exportData" :disabled="exporting"
            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 min-w-[120px] justify-center bg-gradient-to-r from-violet-600 to-violet-700 shadow-md hover:shadow-lg transition-all disabled:opacity-50">
            <svg v-if="exporting" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            {{ exporting ? (format === 'pdf' ? 'Generating...' : 'Exporting...') : 'Export' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import exportService from '@/services/exportService';
import caseService from '@/services/caseService';
import { useAuth } from '@/composables/useAuth';
import Swal from 'sweetalert2';

const props = defineProps({
  show: Boolean
});

const emit = defineEmits(['close', 'exported']);

// ── ONLY NEW LINE: get the role ──
const { userRole } = useAuth();
const isAdmin = computed(() => userRole.value === 'admin');

const format = ref('excel');
const exportType = ref('cases');
const selectedCategory = ref('');
const groupByCategory = ref(false);
const categories = ref([]);

const exporting = ref(false);
const exportProgress = ref(0);
const success = ref(false);
const successMessage = ref('');
const error = ref(false);
const errorMessage = ref('');

// Load categories on mount
onMounted(async () => {
  try {
    const response = await caseService.getLookups();
    categories.value = response.data?.categories || [];
  } catch (error) {
    console.error('Failed to load categories:', error);
  }
});

// Disable groupByCategory for PDF
watch(format, (newFormat) => {
  if (newFormat === 'pdf') {
    groupByCategory.value = false;
  }
});

// If a non-admin has 'all' selected and role changes, snap back to 'cases'
watch(isAdmin, (val) => {
  if (!val) exportType.value = 'cases';
});

const close = () => {
  if (!exporting.value) {
    emit('close');
  }
};

const exportData = async () => {
  exporting.value = true;
  exportProgress.value = 0;
  error.value = false;
  success.value = false;

  const progressInterval = setInterval(() => {
    if (exportProgress.value < 90) {
      exportProgress.value += 10;
    }
  }, 300);

  try {
    let response;
    
    if (exportType.value === 'cases') {
      const params = {
        format: format.value,
        category_id: selectedCategory.value || null,
        group_by_category: groupByCategory.value
      };
      response = await exportService.exportCases(params);
    } else {
      const params = {
        format: format.value,
        group_by_category: groupByCategory.value && format.value === 'excel'
      };
      response = await exportService.exportAll(params);
    }

    clearInterval(progressInterval);
    exportProgress.value = 100;

    // Determine filename
    let filename;
    const dateStr = new Date().toISOString().split('T')[0];
    
    if (format.value === 'pdf') {
      const categoryName = selectedCategory.value 
        ? categories.value.find(c => c.id === selectedCategory.value)?.name.toLowerCase().replace(/\s+/g, '_')
        : 'all';
      filename = exportType.value === 'cases' 
        ? `${categoryName}_cases_${dateStr}.pdf`
        : `full_backup_${dateStr}.pdf`;
    } else {
      const extension = groupByCategory.value ? 'zip' : 'xlsx';
      if (exportType.value === 'cases') {
        filename = groupByCategory.value 
          ? `cases_by_category_${dateStr}.zip`
          : `cases_export_${dateStr}.xlsx`;
      } else {
        filename = groupByCategory.value 
          ? `full_backup_by_category_${dateStr}.zip`
          : `full_backup_${dateStr}.xlsx`;
      }
    }
    
    // Download file
    exportService.downloadFile(response, filename);

    success.value = true;
    successMessage.value = `Export completed! File ready for download.`;

    setTimeout(() => {
      close();
      emit('exported');
    }, 1500);

  } catch (err) {
    clearInterval(progressInterval);
    exportProgress.value = 0;
    
    error.value = true;
    errorMessage.value = err.response?.data?.message || 'Export failed. Please try again.';
    
    Swal.fire({
      icon: 'error',
      title: 'Export Failed',
      text: errorMessage.value,
      confirmButtonColor: '#dc2626'
    });

  } finally {
    exporting.value = false;
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