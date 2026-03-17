<template>
  <div class="min-h-screen p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Case Master</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage and track all legal cases</p>
      <!-- Add this after the New Case button in the filters section -->
<button @click="openImportModal" 
  class="text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center bg-gradient-to-r from-emerald-600 to-emerald-700 shadow-md hover:shadow-lg transition-all">
  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
  </svg>
  Import Excel
</button>
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

    <!-- Loading State (only shows on first load if no cache) -->
    <div v-if="initialLoading" class="bg-white rounded-2xl shadow-sm border border-slate-100 py-24 flex flex-col items-center">
      <div class="relative mb-6">
        <div class="w-16 h-16 rounded-full border-4 border-blue-100 absolute"></div>
        <div class="w-16 h-16 rounded-full border-4 border-[#1a4972] border-t-transparent animate-spin"></div>
      </div>
      <p class="text-base font-semibold text-slate-700 mb-2">Loading Cases</p>
      <p class="text-sm text-slate-500">Please wait while we fetch the data...</p>
    </div>

    <!-- Cases Table -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
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
          <tr v-for="(item, index) in displayedCases" :key="item.id" 
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
          <tr v-if="filteredCases.length === 0">
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
      <div v-if="filteredCases.length > 0" class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
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
<!-- Import Modal -->
    <ImportModal
      :show="showImportModal"
      @close="closeImportModal"
      @imported="handleImportComplete"
    />
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
      :is-loading="false"
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
      @refresh="fetchCases"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import { debounce } from 'lodash';
import { useAuth } from '@/composables/useAuth';
import { useMasterData } from '@/composables/useMasterData';
import caseService from '@/services/caseService';
import CaseFormModal from '@/components/Modals/Admin/CaseMasterModal/CaseFormModal.vue';
import CaseViewModal from '@/components/Modals/Admin/CaseMasterModal/CaseViewModal.vue';
import ImportModal from '@/components/Modals/Admin/CaseMasterModal/ImportModal.vue';
import Swal from 'sweetalert2';

// Import from appUtils
import { 
  getCases,
  setCases,
  listenForUpdates,
  getInitials,
  formatDate
} from '@/utils/appUtils';

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

// ========== STATE ==========
// Get initial data from appUtils
const initialCases = getCases();

const allCases = ref(initialCases || []);
const lookups = ref({
  categories: [],
  stages: [],
  lawyers: [],
  clerks: [],
  clients: [],
  courts: [],
  users: []
});

// Loading state
const initialLoading = ref(!initialCases || initialCases.length === 0);

// Pagination
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

// Loading states
const formLoading = ref(false);
const isRefreshing = ref(false);

// Modals
const showFormModal = ref(false);
const showViewModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const selectedCase = ref(null);
// Add this with your other refs
const showImportModal = ref(false);

// Add this method
const openImportModal = () => {
  showImportModal.value = true;
};

const closeImportModal = () => {
  showImportModal.value = false;
};

const handleImportComplete = () => {
  fetchCases(true); // Refresh the cases list
};
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

// Flag to track if lookups are loaded
let lookupsLoaded = false;

// ========== COMPUTED ==========
// Filter cases based on current filters
const filteredCases = computed(() => {
  let filtered = allCases.value;

  // Filter by status
  if (statusFilter.value) {
    filtered = filtered.filter(item => item.case_status === statusFilter.value);
  }

  // Filter by priority
  if (priorityFilter.value) {
    filtered = filtered.filter(item => item.priority === priorityFilter.value);
  }

  // Filter by stage
  if (stageFilter.value) {
    filtered = filtered.filter(item => item.current_stage_id === stageFilter.value);
  }

  // Filter by search
  if (searchQuery.value) {
    const searchLower = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => {
      return (item.case_code?.toLowerCase().includes(searchLower)) ||
             (item.title?.toLowerCase().includes(searchLower)) ||
             (item.client?.toLowerCase().includes(searchLower)) ||
             (item.case_no?.toLowerCase().includes(searchLower));
    });
  }

  // Sort
  filtered.sort((a, b) => {
    let aVal = a[sortField.value];
    let bVal = b[sortField.value];
    
    if (sortField.value === 'created_at') {
      aVal = new Date(aVal);
      bVal = new Date(bVal);
    }
    
    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });

  return filtered;
});

// Paginated cases
const displayedCases = computed(() => {
  const start = (pagination.value.current_page - 1) * pagination.value.per_page;
  const end = start + pagination.value.per_page;
  return filteredCases.value.slice(start, end);
});

// Display pages for pagination
const displayedPages = computed(() => {
  const pages = [];
  const max = 5;
  const total = pagination.value.last_page || 1;
  const current = pagination.value.current_page || 1;
  
  if (total <= max) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    let start = Math.max(1, current - 2);
    let end = Math.min(total, start + max - 1);
    if (end - start + 1 < max) start = Math.max(1, end - max + 1);
    
    if (start > 1) {
      pages.push(1);
      if (start > 2) pages.push('...');
    }
    
    for (let i = start; i <= end; i++) pages.push(i);
    
    if (end < total) {
      if (end < total - 1) pages.push('...');
      pages.push(total);
    }
  }
  return pages;
});

const previewCode = computed(() => {
  const year = new Date().getFullYear();
  const nextNum = (filteredCases.value.length || 0) + 1;
  return `${year}-${String(nextNum).padStart(4, '0')}`;
});

// ========== FETCH CASES ==========
const fetchCases = async (showLoading = false) => {
  if (showLoading) initialLoading.value = true;
  isRefreshing.value = true;
  
  try {
    const params = {
      search: searchQuery.value || undefined,
      case_status: statusFilter.value || undefined,
      priority: priorityFilter.value || undefined,
      stage_id: stageFilter.value || undefined,
      sort_by: sortField.value,
      sort_direction: sortDirection.value,
      per_page: 100 // Get more items for cache
    };

    const response = await caseService.getCases(params);
    
    if (response.data) {
      // Store in appUtils
      setCases(response.data);
      allCases.value = response.data;
      
      // Update pagination
      updatePagination();
      
      // Turn off loading
      if (initialLoading.value) initialLoading.value = false;
    }
    
  } catch (error) {
    console.error('Failed to load cases:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to load cases',
      confirmButtonColor: '#dc2626',
      timer: 2000,
      showConfirmButton: false
    });
  } finally {
    if (showLoading) initialLoading.value = false;
    isRefreshing.value = false;
  }
};

// ========== FETCH LOOKUPS (ONCE) ==========
const fetchLookups = async () => {
  // Skip if already loaded
  if (lookupsLoaded) {
    return;
  }
  
  try {
    const response = await caseService.getLookups();
    const data = response.data || {};
    
    // Ensure users array exists (combine lawyers and clerks)
    const lawyers = data.lawyers || [];
    const clerks = data.clerks || [];
    
    data.users = [
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
    
    lookups.value = data;
    lookupsLoaded = true;
    
  } catch (error) {
    console.error('Failed to load lookups:', error);
  }
};

// ========== INITIALIZE ==========
const initialize = async () => {
  console.log('🚀 Initializing Case Master...');
  console.log('📊 Cases in cache:', allCases.value.length);
  
  // Update pagination based on cached data
  updatePagination();
  
  // If no cached data, show loading and fetch
  if (!allCases.value || allCases.value.length === 0) {
    console.log('📡 No cached data, fetching...');
    await fetchCases(true);
  } else {
    // Fetch fresh data in background
    console.log('📡 Fetching fresh data in background...');
    fetchCases(false);
  }
  
  // Fetch lookups once
  await fetchLookups();
};

// Update pagination based on filtered cases
const updatePagination = () => {
  const total = filteredCases.value.length;
  const last_page = Math.ceil(total / pagination.value.per_page) || 1;
  const current = Math.min(pagination.value.current_page, last_page);
  
  pagination.value = {
    current_page: current,
    last_page: last_page,
    per_page: pagination.value.per_page,
    total: total,
    from: total > 0 ? (current - 1) * pagination.value.per_page + 1 : 0,
    to: total > 0 ? Math.min(current * pagination.value.per_page, total) : 0
  };
};

// ========== FILTER METHODS ==========
const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1;
  updatePagination();
}, 500);

const handleFilterChange = () => {
  pagination.value.current_page = 1;
  updatePagination();
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  pagination.value.current_page = 1;
  updatePagination();
};

// Pagination
const previousPage = () => {
  if (pagination.value.current_page > 1) {
    pagination.value.current_page--;
    updatePagination();
  }
};

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++;
    updatePagination();
  }
};

const goToPage = (page) => {
  pagination.value.current_page = page;
  updatePagination();
};

// Helpers
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
const openCreateModal = () => {
  resetForm();
  isEditing.value = false;
  editingId.value = null;
  showFormModal.value = true;
};

const openEditModal = (caseItem) => {
  resetForm();
  isEditing.value = true;
  editingId.value = caseItem.id;
  
  Object.assign(form, {
    case_no: caseItem.case_no,
    title: caseItem.title,
    category_id: caseItem.category_id || '',
    client_id: caseItem.client_id,
    court_or_office: caseItem.court_or_office || '',
    docket_no: caseItem.docket_no || '',
    assigned_lawyer_id: caseItem.assigned_lawyer_id,
    assigned_clerk_id: caseItem.assigned_clerk_id || '',
    priority: caseItem.priority,
    case_status: caseItem.case_status,
    current_stage_id: caseItem.current_stage_id || '',
    summary: caseItem.summary || ''
  });
  
  showFormModal.value = true;
};

const openViewModal = async (caseItem) => {
  try {
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
  Object.assign(form, {
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
  
  Object.assign(errors, {
    case_no: '',
    title: '',
    client_id: '',
    assigned_lawyer_id: ''
  });
};

const clearErrors = () => {
  Object.assign(errors, {
    case_no: '',
    title: '',
    client_id: '',
    assigned_lawyer_id: ''
  });
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
      await caseService.createCase(payload);

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
    await fetchCases(false);

  } catch (error) {
    console.error('Form submission error:', error);
    
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors || {};
      
      Object.assign(errors, {
        case_no: validationErrors.case_no ? (Array.isArray(validationErrors.case_no) ? validationErrors.case_no[0] : validationErrors.case_no) : '',
        title: validationErrors.title ? (Array.isArray(validationErrors.title) ? validationErrors.title[0] : validationErrors.title) : '',
        client_id: validationErrors.client_id ? (Array.isArray(validationErrors.client_id) ? validationErrors.client_id[0] : validationErrors.client_id) : '',
        assigned_lawyer_id: validationErrors.assigned_lawyer_id ? (Array.isArray(validationErrors.assigned_lawyer_id) ? validationErrors.assigned_lawyer_id[0] : validationErrors.assigned_lawyer_id) : ''
      });
      
      Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        text: 'Please check the form for errors',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.response?.data?.message || error.message || 'Failed to save case',
        confirmButtonColor: '#dc2626'
      });
    }

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

      await fetchCases(false);

    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: error.message || 'Failed to delete case',
        confirmButtonColor: '#dc2626'
      });
    }
  }
};

// ========== MANUAL REFRESH ==========
const manualRefresh = async () => {
  isRefreshing.value = true;
  await fetchCases(true);
  
  Swal.fire({
    icon: 'success',
    title: 'Refreshed!',
    text: 'Cases list updated',
    timer: 1500,
    showConfirmButton: false,
    position: 'top-end',
    toast: true
  });
};

// ========== LISTEN FOR UPDATES ==========
const handleCasesUpdated = (event) => {
  console.log('🔄 Cases updated event received');
  allCases.value = event.detail;
  updatePagination();
};

let cleanupCases = null;

// ========== LIFECYCLE ==========
onMounted(async () => {
  await initialize();
  
  // Listen for updates from appUtils
  cleanupCases = listenForUpdates('cases-updated', handleCasesUpdated);
});

onUnmounted(() => {
  if (cleanupCases) cleanupCases();
});
</script>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>