<template>
  <div class="min-h-screen p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Case Master</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage and track all legal cases</p>
    </div>

    <!-- Filters and Actions -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-4">
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Search -->
        <div class="relative flex-1">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <input v-model="searchQuery" @input="debouncedSearch" type="text"
            placeholder="Search by case code, title, or client..."
            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:bg-white transition-all" />
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
          <select v-model="statusFilter" @change="handleFilterChange"
            class="px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="closed">Closed</option>
            <option value="archived">Archived</option>
          </select>

          <select v-model="priorityFilter" @change="handleFilterChange"
            class="px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
            <option value="">All Priority</option>
            <option value="urgent">Urgent</option>
            <option value="normal">Normal</option>
            <option value="low">Low</option>
          </select>

          <select v-model="stageFilter" @change="handleFilterChange"
            class="px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
            <option value="">All Stages</option>
            <option v-for="stage in lookups.stages" :key="stage.id" :value="stage.id">
              {{ stage.name }}
            </option>
          </select>

          <button @click="openCreateModal" 
            class="text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md hover:shadow-lg transition-all">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            New Case
          </button>
        </div>
      </div>
    </div>

    <!-- Cases Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-slate-100 bg-[#1a4972]/5">
            <th v-for="col in columns" :key="col.field" scope="col"
              class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
              :class="col.sortable ? 'cursor-pointer hover:text-[#1a4972] select-none group' : ''"
              @click="col.sortable ? sortBy(col.field) : null">
              <div class="flex items-center gap-1.5">
                {{ col.label }}
                <svg v-if="col.sortable && sortField === col.field" class="w-3.5 h-3.5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path :d="sortDirection === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" stroke-width="2.5"/>
                </svg>
                <svg v-else-if="col.sortable" class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
              </div>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-50">
          <tr v-for="(item, index) in cases" :key="item.id" 
            class="transition-all duration-300 hover:bg-blue-50/30 group"
            :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.03}s both` }">
            
            <!-- Case Code + Title -->
            <td class="px-5 py-4">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold tracking-wider text-[#1a4972]">{{ item.case_code }}</span>
                <span v-if="item.category" class="px-2 py-0.5 text-[10px] font-semibold rounded-full"
                  :style="{ 
                    backgroundColor: item.category_color + '20',
                    color: item.category_color,
                    border: '1px solid ' + item.category_color + '40'
                  }">
                  {{ item.category }}
                </span>
              </div>
              <p class="text-sm font-semibold text-slate-800">{{ item.title }}</p>
              <p class="text-xs text-slate-400 mt-0.5">#{{ item.case_no }}</p>
            </td>

            <!-- Client -->
            <td class="px-5 py-4">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-[#1a4972]">
                  {{ getInitials(item.client) }}
                </div>
                <span class="text-sm text-slate-700">{{ item.client }}</span>
              </div>
            </td>

            <!-- Assigned To -->
            <td class="px-5 py-4">
              <div class="space-y-1">
                <div class="flex items-center gap-1">
                  <span class="text-xs text-slate-400 w-12">Atty.</span>
                  <span class="text-sm text-slate-700">{{ item.lawyer }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="text-xs text-slate-400 w-12">Clerk</span>
                  <span class="text-sm text-slate-700">{{ item.clerk || '—' }}</span>
                </div>
              </div>
            </td>

            <!-- Stage -->
            <td class="px-5 py-4">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg"
                :style="{ 
                  backgroundColor: item.stage_color + '20',
                  color: item.stage_color,
                  border: '1px solid ' + item.stage_color + '40'
                }">
                <span class="w-1.5 h-1.5 rounded-full" :style="{ backgroundColor: item.stage_color }"></span>
                {{ item.stage }}
              </span>
            </td>

            <!-- Priority -->
            <td class="px-5 py-4">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg"
                :class="priorityClass(item.priority)">
                <span class="w-1.5 h-1.5 rounded-full" :class="priorityDotClass(item.priority)"></span>
                {{ capitalize(item.priority) }}
              </span>
            </td>

            <!-- Status -->
            <td class="px-5 py-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-lg"
                :class="statusClass(item.case_status)">
                {{ capitalize(item.case_status) }}
              </span>
            </td>
            <!-- Actions -->
            <td class="px-5 py-4">
              <div class="flex items-center gap-2">
                <button @click="openViewModal(item)" 
                  class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#1a4972] hover:bg-[#1a4972]/10 transition-all">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                  View
                </button>
                <button @click="openEditModal(item)" 
                  class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-[#1a4972] hover:bg-[#1a4972]/10 transition-all">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  Edit
                </button>
                <button @click="confirmDelete(item)" 
                  class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 transition-all">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="cases.length === 0">
            <td :colspan="columns.length" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-[#1a4972]/10 flex items-center justify-center mb-3">
                  <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                  </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700 mb-1">No cases found</p>
                <p class="text-xs text-slate-400">Click "New Case" to create one</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
        <p class="text-xs text-slate-500">
          Showing <span class="font-semibold text-slate-700">{{ pagination.from }}</span> to
          <span class="font-semibold text-slate-700">{{ pagination.to }}</span> of
          <span class="font-semibold text-slate-700">{{ pagination.total }}</span> cases
        </p>
        <div class="flex items-center gap-1">
          <button @click="previousPage" :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'">
            ← Prev
          </button>
          <button v-for="page in displayedPages" :key="page" @click="goToPage(page)"
            class="w-7 h-7 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === page ? 'bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white' : 'text-slate-600 hover:bg-slate-200'">
            {{ page }}
          </button>
          <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === pagination.last_page ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'">
            Next →
          </button>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <CaseFormModal
      ref="formModalRef"
      :show="showFormModal"
      :is-editing="isEditing"
      :form-loading="formLoading"
      :form="form"
      :errors="errors"
      :categories="lookups.categories"
      :stages="lookups.stages"
      :courts="lookups.courts"
      :lawyers="lookups.lawyers"
      :clerks="lookups.clerks"
      :clients="lookups.clients"
      :preview-code="previewCode"
      :is-loading="isLoadingLookups"
      @close="closeFormModal"
      @submit="submitForm"
      @client-created="onClientCreated"
    />
    <CaseViewModal
      ref="viewModalRef"
      :show="showViewModal"
      :case-data="selectedCase"
      :stages="lookups.stages"
      :clerks="lookups.clerks"
      :all-users="lookups.users"
      @close="showViewModal = false"
      @refresh="loadCases"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { debounce } from 'lodash';
import { useAuth } from '@/composables/useAuth';
import { useMasterData } from '@/composables/useMasterData';
import caseService from '@/services/caseService';
import CaseFormModal from '@/components/Modals/Admin/CaseMasterModal/CaseFormModal.vue';
import CaseViewModal from '@/components/Modals/Admin/CaseMasterModal/CaseViewModal.vue';
import Swal from 'sweetalert2';

const { userRole } = useAuth();
const { refreshClients } = useMasterData();

// Columns
const columns = [
  { label: 'Case', field: 'case_code', sortable: true },
  { label: 'Client', field: 'client', sortable: true },
  { label: 'Assigned To', field: 'lawyer', sortable: false },
  { label: 'Stage', field: 'stage', sortable: true },
  { label: 'Priority', field: 'priority', sortable: true },
  { label: 'Status', field: 'case_status', sortable: true },
    { label: 'Actions', field: 'actions', sortable: false },
];

// State
const cases = ref([]);
const lookups = ref({
  categories: [],
  stages: [],
  lawyers: [],
  clerks: [],
  clients: [],
  courts: [],
  users: []
});
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

// Filters
const searchQuery = ref('');
const statusFilter = ref('');
const priorityFilter = ref('');
const stageFilter = ref('');
const sortField = ref('created_at');
const sortDirection = ref('desc');
const currentPage = ref(1);

// Loading states
const isLoading = ref(false);
const isLoadingLookups = ref(false);
const formLoading = ref(false);

// Modals
const showFormModal = ref(false);
const showViewModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const selectedCase = ref(null);

// Form
const form = reactive({
  case_no: '',
  title: '',
  category_id: '',
  client_id: '',
  court_or_office: '',
  docket_no: '',
  assigned_lawyer_id: '',
  assigned_clerk_id: '',
  priority: 'normal',
  case_status: 'active',
  current_stage_id: '',
  summary: ''
});

const errors = reactive({
  case_no: '',
  title: '',
  client_id: '',
  assigned_lawyer_id: ''
});

// Computed
const displayedPages = computed(() => {
  const pages = [];
  const max = 5;
  const total = pagination.value.last_page || 1;
  const current = pagination.value.current_page || 1;
  if (total <= max) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    let s = Math.max(1, current - 2);
    let e = Math.min(total, s + max - 1);
    if (e - s + 1 < max) s = Math.max(1, e - max + 1);
    for (let i = s; i <= e; i++) pages.push(i);
  }
  return pages;
});

const previewCode = computed(() => {
  const year = new Date().getFullYear();
  const nextNum = (pagination.value.total || 0) + 1;
  return `${year}-${String(nextNum).padStart(4, '0')}`;
});

// Load cases
const loadCases = async () => {
  isLoading.value = true;
  try {
    const params = {
      search: searchQuery.value || undefined,
      case_status: statusFilter.value || undefined,
      priority: priorityFilter.value || undefined,
      stage_id: stageFilter.value || undefined,
      sort_by: sortField.value,
      sort_direction: sortDirection.value,
      page: currentPage.value,
      per_page: pagination.value.per_page
    };

    const response = await caseService.getCases(params);
    cases.value = response.data || [];
    pagination.value = response.meta || {
      current_page: currentPage.value,
      last_page: 1,
      per_page: 15,
      total: cases.value.length,
      from: 1,
      to: cases.value.length
    };
  } catch (error) {
    console.error('Failed to load cases:', error);
    cases.value = [];
    
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to load cases',
      confirmButtonColor: '#dc2626'
    });
  } finally {
    isLoading.value = false;
  }
};


// In the loadLookups function, after setting lookups.value
const loadLookups = async () => {
  isLoadingLookups.value = true;
  try {
    const response = await caseService.getLookups();
    lookups.value = response.data || {};
    
    // Ensure users array exists (combine lawyers and clerks)
    const lawyers = lookups.value.lawyers || [];
    const clerks = lookups.value.clerks || [];
    
    lookups.value.users = [
      ...lawyers.map(l => ({ 
        id: l.id, 
        full_name: l.full_name, 
        role: 'lawyer' 
      })),
      ...clerks.map(c => ({ 
        id: c.id, 
        full_name: c.full_name, 
        role: 'clerk' 
      }))
    ];
    

    
  } catch (error) {
    console.error('Failed to load lookups:', error);
    lookups.value = {
      categories: [],
      stages: [],
      lawyers: [],
      clerks: [],
      clients: [],
      courts: [],
      users: []
    };
  } finally {
    isLoadingLookups.value = false;
  }
};
// Filters
const debouncedSearch = debounce(() => {
  currentPage.value = 1;
  loadCases();
}, 500);

const handleFilterChange = () => {
  currentPage.value = 1;
  loadCases();
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  loadCases();
};

// Pagination
const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    loadCases();
  }
};

const nextPage = () => {
  if (currentPage.value < pagination.value.last_page) {
    currentPage.value++;
    loadCases();
  }
};

const goToPage = (page) => {
  currentPage.value = page;
  loadCases();
};

// Helpers
const getInitials = (name) => {
  if (!name || name === '—') return '?';
  const parts = name.split(' ').filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0][0].toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const capitalize = (s) => s ? s[0].toUpperCase() + s.slice(1) : '';

const priorityClass = (p) => ({
  urgent: 'bg-red-50 text-red-700 border border-red-200',
  normal: 'bg-blue-50 text-blue-700 border border-blue-200',
  low: 'bg-slate-100 text-slate-600 border border-slate-200'
}[p] || 'bg-slate-100 text-slate-500');

const priorityDotClass = (p) => ({
  urgent: 'bg-red-500',
  normal: 'bg-blue-500',
  low: 'bg-slate-400'
}[p] || 'bg-slate-400');

const statusClass = (s) => ({
  active: 'bg-emerald-50 text-emerald-700 border border-emerald-200',
  closed: 'bg-slate-100 text-slate-600 border border-slate-200',
  archived: 'bg-amber-50 text-amber-700 border border-amber-200'
}[s] || 'bg-slate-100 text-slate-500');

// Modal functions
const openCreateModal = async () => {
  await loadLookups();
  resetForm();
  isEditing.value = false;
  editingId.value = null;
  showFormModal.value = true;
};

const openEditModal = async (caseItem) => {
  await loadLookups();
  resetForm();
  isEditing.value = true;
  editingId.value = caseItem.id;
  
  form.case_no = caseItem.case_no;
  form.title = caseItem.title;
  form.category_id = caseItem.category_id || '';
  form.client_id = caseItem.client_id;
  form.court_or_office = caseItem.court_or_office || '';
  form.docket_no = caseItem.docket_no || '';
  form.assigned_lawyer_id = caseItem.assigned_lawyer_id;
  form.assigned_clerk_id = caseItem.assigned_clerk_id || '';
  form.priority = caseItem.priority;
  form.case_status = caseItem.case_status;
  form.current_stage_id = caseItem.current_stage_id || '';
  form.summary = caseItem.summary || '';
  
  showFormModal.value = true;
};

// Update this function
const openViewModal = async (caseItem) => {
  try {
    // Load lookups first to get users and clerks
    await loadLookups();
    
    const response = await caseService.getCase(caseItem.id);
    selectedCase.value = response.data;
    showViewModal.value = true;
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to load case details',
      confirmButtonColor: '#dc2626'
    });
  }
};

const closeFormModal = () => {
  showFormModal.value = false;
  resetForm();
};

const resetForm = () => {
  form.case_no = '';
  form.title = '';
  form.category_id = '';
  form.client_id = '';
  form.court_or_office = '';
  form.docket_no = '';
  form.assigned_lawyer_id = '';
  form.assigned_clerk_id = '';
  form.priority = 'normal';
  form.case_status = 'active';
  form.current_stage_id = '';
  form.summary = '';
  
  errors.case_no = '';
  errors.title = '';
  errors.client_id = '';
  errors.assigned_lawyer_id = '';
};

const clearErrors = () => {
  errors.case_no = '';
  errors.title = '';
  errors.client_id = '';
  errors.assigned_lawyer_id = '';
};

// Client created handler
const onClientCreated = (updatedClients) => {
  lookups.value.clients = updatedClients;
  refreshClients(updatedClients);
};

// Submit form
const submitForm = async () => {
  formLoading.value = true;
  clearErrors();

  try {
    const payload = {
      case_no: form.case_no,
      title: form.title,
      category_id: form.category_id || null,
      client_id: form.client_id,
      court_or_office: form.court_or_office || null,
      docket_no: form.docket_no || null,
      assigned_lawyer_id: form.assigned_lawyer_id,
      assigned_clerk_id: form.assigned_clerk_id || null,
      priority: form.priority,
      case_status: form.case_status,
      current_stage_id: form.current_stage_id || null,
      summary: form.summary || null
    };

    if (isEditing.value) {
      // Optimistic update
      const index = cases.value.findIndex(c => c.id === editingId.value);
      if (index !== -1) {
        cases.value[index] = {
          ...cases.value[index],
          ...payload,
          client: lookups.value.clients.find(c => c.id === payload.client_id)?.full_name || '—',
          lawyer: lookups.value.lawyers.find(l => l.id === payload.assigned_lawyer_id)?.full_name || '—',
          clerk: lookups.value.clerks.find(c => c.id === payload.assigned_clerk_id)?.full_name || '—'
        };
      }

      await caseService.updateCase(editingId.value, payload);
      
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Case updated successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    } else {
      const response = await caseService.createCase(payload);
      
      if (response.data) {
        cases.value.unshift(response.data);
      }

      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: 'Case created successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    }

    closeFormModal();
    await loadCases(); // Refresh in background

  } catch (error) {
    if (error.errors) {
      if (error.errors.case_no) errors.case_no = error.errors.case_no[0];
      if (error.errors.title) errors.title = error.errors.title[0];
      if (error.errors.client_id) errors.client_id = error.errors.client_id[0];
      if (error.errors.assigned_lawyer_id) errors.assigned_lawyer_id = error.errors.assigned_lawyer_id[0];
    }

    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to save case',
      confirmButtonColor: '#dc2626'
    });

  } finally {
    formLoading.value = false;
  }
};

// Delete
const confirmDelete = async (caseItem) => {
  const result = await Swal.fire({
    title: 'Delete Case?',
    text: `Are you sure you want to delete case ${caseItem.case_code}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    try {
      // Optimistic delete
      cases.value = cases.value.filter(c => c.id !== caseItem.id);

      await caseService.deleteCase(caseItem.id);

      Swal.fire({
        icon: 'success',
        title: 'Deleted',
        text: 'Case deleted successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

      await loadCases(); // Refresh

    } catch (error) {
      // Revert on error
      await loadCases();

      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.message || 'Failed to delete case',
        confirmButtonColor: '#dc2626'
      });
    }
  }
};

// Watch for page changes
watch(currentPage, () => {
  loadCases();
});

// Initial load
onMounted(() => {
  loadCases();
});
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>