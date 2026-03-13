<template>
  <div class="min-h-screen p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Case Categories</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage case categories and their colors</p>
    </div>

    <!-- Search and Add Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <input v-model="searchQuery" @input="debouncedSearch" type="text"
            placeholder="Search categories..."
            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:bg-white transition-all" />
        </div>

        <select v-model="statusFilter" @change="handleFilterChange"
          class="px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100">
          <option value="">All Status</option>
          <option value="true">Active Only</option>
          <option value="false">Inactive Only</option>
        </select>

        <button @click="openCreateModal" :disabled="isAdding"
          class="text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center transition-all whitespace-nowrap hover:shadow-lg active:scale-95 disabled:opacity-50 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30">
          <svg v-if="!isAdding" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <svg v-else class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ isAdding ? 'Adding...' : 'Add Category' }}
        </button>
      </div>
    </div>

    <!-- Categories Table -->
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
          <tr v-for="(item, index) in categories" :key="item.id" 
            class="transition-all duration-300 hover:bg-blue-50/30 group"
            :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.03}s both` }">
            
            <!-- Color + Name -->
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" :style="{ backgroundColor: item.color }"></div>
                <span class="text-sm font-semibold text-slate-800">{{ item.name }}</span>
              </div>
            </td>

            <!-- Sort Order -->
            <td class="px-5 py-4">
              <span class="text-sm text-slate-600">{{ item.sort_order }}</span>
            </td>

            <!-- Status -->
            <td class="px-5 py-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-lg"
                :class="item.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>

            <!-- Created At -->
            <td class="px-5 py-4 text-sm text-slate-400">{{ formatDate(item.created_at) }}</td>

            <!-- Actions -->
            <td class="px-5 py-4">
              <div class="flex items-center gap-2">
                <button @click="editItem(item)" :disabled="editingId === item.id"
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-[#1a4972] hover:bg-[#1a4972]/10 transition-all disabled:opacity-50">
                  <svg v-if="editingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ editingId === item.id ? 'Editing...' : 'Edit' }}
                </button>

                <button @click="toggleStatus(item)" :disabled="togglingId === item.id"
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold"
                  :class="item.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50'">
                  <svg v-if="togglingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path v-if="item.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ togglingId === item.id ? '...' : (item.is_active ? 'Deactivate' : 'Activate') }}
                </button>

                <button @click="confirmDelete(item)" :disabled="deletingId === item.id"
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-600 text-sm font-semibold hover:bg-red-50 transition-all disabled:opacity-50">
                  <svg v-if="deletingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ deletingId === item.id ? 'Deleting...' : 'Delete' }}
                </button>
              </div>
            </td>
          </tr>

          <!-- Empty state -->
          <tr v-if="categories.length === 0">
            <td :colspan="columns.length" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-[#1a4972]/10 flex items-center justify-center mb-3">
                  <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                  </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700 mb-1">No categories found</p>
                <p class="text-xs text-slate-400">Click "Add Category" to create one</p>
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
          <span class="font-semibold text-slate-700">{{ pagination.total }}</span> categories
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

    <!-- Create/Edit Modal -->
    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
          
          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-[#1a4972]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-bold text-slate-800">{{ isEditing ? 'Edit Category' : 'Add Category' }}</h2>
                <p class="text-sm text-slate-500">{{ isEditing ? 'Update category details' : 'Create a new case category' }}</p>
              </div>
            </div>
            <button @click="closeModal" :disabled="formLoading" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="px-6 py-5 space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category Name <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" placeholder="e.g. Criminal, Civil, etc."
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                :class="{ 'border-red-400': errors.name }" />
              <p v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name }}</p>
            </div>

            <!-- Color Picker -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category Color</label>
              <div class="flex items-center gap-3">
                <ColorPicker v-model="form.color" />
                <input v-model="form.color" type="text" placeholder="#HEX"
                  class="flex-1 px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all font-mono" />
              </div>
            </div>

            <!-- Sort Order -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" min="0" placeholder="0"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all" />
              <p class="text-xs text-slate-400 mt-1">Lower numbers appear first</p>
            </div>

            <!-- Status -->
            <div class="flex items-center gap-2">
              <input type="checkbox" v-model="form.is_active" id="isActive" class="w-4 h-4 rounded border-slate-300 text-[#1a4972] focus:ring-[#1a4972]" />
              <label for="isActive" class="text-sm font-medium text-slate-700">Active</label>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            <button @click="closeModal" :disabled="formLoading"
              class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
              Cancel
            </button>
            <button @click="submitForm" :disabled="formLoading"
              class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 min-w-[100px] justify-center bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30">
              <svg v-if="formLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ formLoading ? (isEditing ? 'Saving...' : 'Adding...') : (isEditing ? 'Save Changes' : 'Add Category') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { debounce } from 'lodash';
import { caseCategoryService } from '@/services/masterData';
import cacheService from '@/services/cacheService';
import ColorPicker from '@/components/ColorPicker.vue';
import Swal from 'sweetalert2';

// Columns
const columns = [
  { label: 'Category', field: 'name', sortable: true },
  { label: 'Sort Order', field: 'sort_order', sortable: true },
  { label: 'Status', field: 'is_active', sortable: true },
  { label: 'Created', field: 'created_at', sortable: true },
  { label: 'Actions', field: 'actions', sortable: false },
];

// State
const categories = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

const searchQuery = ref('');
const statusFilter = ref('');
const sortField = ref('sort_order');
const sortDirection = ref('asc');
const currentPage = ref(1);

// Loading states
const isLoading = ref(false);
const isRefreshing = ref(false);
const isFromCache = ref(false);
const isAdding = ref(false);
const editingId = ref(null);
const togglingId = ref(null);
const deletingId = ref(null);
const formLoading = ref(false);

// Modal
const showModal = ref(false);
const isEditing = ref(false);
const editingItemId = ref(null);

// Form
const form = reactive({
  name: '',
  color: '#1a4972',
  sort_order: 0,
  is_active: true
});

const errors = reactive({
  name: '',
  color: ''
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

// ==================== LOAD FROM CACHE FIRST ====================
const loadFromCache = () => {
  const params = {
    search: searchQuery.value || undefined,
    is_active: statusFilter.value || undefined,
    sort_by: sortField.value,
    sort_direction: sortDirection.value,
    page: currentPage.value,
    per_page: pagination.value.per_page
  };
  
  // Try to get cached categories
  const cachedCategories = cacheService.getCategories();
  if (cachedCategories && cachedCategories.length > 0) {
    console.log('📦 Loading categories from cache');
    
    // Filter and sort cached data based on current params
    let filtered = [...cachedCategories];
    
    if (statusFilter.value) {
      const isActive = statusFilter.value === 'true';
      filtered = filtered.filter(c => c.is_active === isActive);
    }
    
    if (searchQuery.value) {
      const search = searchQuery.value.toLowerCase();
      filtered = filtered.filter(c => c.name.toLowerCase().includes(search));
    }
    
    // Sort
    filtered.sort((a, b) => {
      let aVal = a[sortField.value];
      let bVal = b[sortField.value];
      
      if (sortField.value === 'name') {
        aVal = aVal.toLowerCase();
        bVal = bVal.toLowerCase();
      }
      
      if (sortDirection.value === 'asc') {
        return aVal > bVal ? 1 : -1;
      } else {
        return aVal < bVal ? 1 : -1;
      }
    });
    
    // Paginate
    const start = (currentPage.value - 1) * pagination.value.per_page;
    const end = start + pagination.value.per_page;
    const paginatedData = filtered.slice(start, end);
    
    categories.value = paginatedData;
    pagination.value = {
      ...pagination.value,
      total: filtered.length,
      from: start + 1,
      to: Math.min(end, filtered.length)
    };
    
    isFromCache.value = true;
  }
};

// ==================== FETCH FRESH CATEGORIES ====================
const fetchFreshCategories = async (showLoading = true) => {
  if (showLoading) isLoading.value = true;
  isRefreshing.value = true;
  
  try {
    const params = {
      search: searchQuery.value || undefined,
      is_active: statusFilter.value || undefined,
      sort_by: sortField.value,
      sort_direction: sortDirection.value,
      page: currentPage.value,
      per_page: pagination.value.per_page
    };

    const response = await caseCategoryService.getCategories(params);
    
    categories.value = response.data || [];
    pagination.value = response.meta || {
      current_page: currentPage.value,
      last_page: 1,
      per_page: 15,
      total: categories.value.length,
      from: 1,
      to: categories.value.length
    };
    
    // Update cache with all categories (fetch all for caching)
    if (currentPage.value === 1 && !searchQuery.value && !statusFilter.value) {
      const allResponse = await caseCategoryService.getCategories({ per_page: 100 });
      cacheService.setCategories(allResponse.data || []);
    }
    
    isFromCache.value = false;
    
  } catch (error) {
    console.error('Failed to load categories:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to load categories',
      confirmButtonColor: '#dc2626',
      timer: 2000,
      showConfirmButton: false
    });
  } finally {
    if (showLoading) isLoading.value = false;
    isRefreshing.value = false;
  }
};

// ==================== LOAD CATEGORIES (with cache first) ====================
const loadCategories = async (forceRefresh = false) => {
  if (forceRefresh) {
    await fetchFreshCategories(true);
  } else {
    // Try cache first
    loadFromCache();
    
    // Fetch fresh in background
    setTimeout(() => {
      fetchFreshCategories(false);
    }, 100);
  }
};

// Filters
const debouncedSearch = debounce(() => {
  currentPage.value = 1;
  fetchFreshCategories(true);
}, 500);

const handleFilterChange = () => {
  currentPage.value = 1;
  fetchFreshCategories(true);
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  fetchFreshCategories(true);
};

// Pagination
const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchFreshCategories(true);
  }
};

const nextPage = () => {
  if (currentPage.value < pagination.value.last_page) {
    currentPage.value++;
    fetchFreshCategories(true);
  }
};

const goToPage = (page) => {
  currentPage.value = page;
  fetchFreshCategories(true);
};

// Format date
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Modal functions
const resetForm = () => {
  form.name = '';
  form.color = '#1a4972';
  form.sort_order = 0;
  form.is_active = true;
  errors.name = '';
  errors.color = '';
};

const clearErrors = () => {
  errors.name = '';
  errors.color = '';
};

const openCreateModal = async () => {
  resetForm();
  isEditing.value = false;
  editingItemId.value = null;
  
  // Get the next available sort order (excluding "Other")
  try {
    const response = await caseCategoryService.getCategories({ 
      sort_by: 'sort_order', 
      sort_direction: 'desc',
      per_page: 100
    });
    
    if (response.data && response.data.length > 0) {
      const normalItems = response.data.filter(item => item.sort_order < 9000);
      if (normalItems.length > 0) {
        const maxSortOrder = Math.max(...normalItems.map(item => item.sort_order));
        form.sort_order = maxSortOrder + 1;
      } else {
        form.sort_order = 1;
      }
    } else {
      form.sort_order = 1;
    }
  } catch (error) {
    console.error('Failed to get next sort order:', error);
    form.sort_order = 1;
  }
  
  showModal.value = true;
};

const editItem = (item) => {
  resetForm();
  isEditing.value = true;
  editingItemId.value = item.id;
  form.name = item.name;
  form.color = item.color;
  form.sort_order = item.sort_order;
  form.is_active = item.is_active;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

// Submit form
const submitForm = async () => {
  formLoading.value = true;
  clearErrors();

  try {
    const payload = {
      name: form.name,
      color: form.color,
      sort_order: form.sort_order,
      is_active: form.is_active
    };

    if (isEditing.value) {
      editingId.value = editingItemId.value;
      
      // Optimistic update
      const index = categories.value.findIndex(c => c.id === editingItemId.value);
      if (index !== -1) {
        categories.value[index] = {
          ...categories.value[index],
          ...payload
        };
      }

      await caseCategoryService.updateCategory(editingItemId.value, payload);
      
      // Update cache
      cacheService.remove(cacheService.CACHE_KEYS.CATEGORIES);

      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Category updated successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

    } else {
      isAdding.value = true;
      const response = await caseCategoryService.createCategory(payload);
      
      if (response.data) {
        categories.value.unshift(response.data);
      }
      
      // Update cache
      cacheService.remove(cacheService.CACHE_KEYS.CATEGORIES);

      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Category created successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    }

    closeModal();
    
    // Refresh in background
    setTimeout(() => {
      fetchFreshCategories(false);
    }, 500);

  } catch (error) {
    if (error.errors) {
      if (error.errors.name) errors.name = error.errors.name[0] || error.errors.name;
      if (error.errors.color) errors.color = error.errors.color[0] || error.errors.color;
    }

    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'An error occurred',
      confirmButtonColor: '#dc2626'
    });

  } finally {
    formLoading.value = false;
    isAdding.value = false;
    editingId.value = null;
  }
};

// Toggle status
const toggleStatus = async (item) => {
  togglingId.value = item.id;

  try {
    // Optimistic update
    const index = categories.value.findIndex(c => c.id === item.id);
    if (index !== -1) {
      categories.value[index] = {
        ...categories.value[index],
        is_active: !item.is_active
      };
    }

    await caseCategoryService.toggleCategory(item.id);
    
    // Update cache
    cacheService.remove(cacheService.CACHE_KEYS.CATEGORIES);

    await Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: `Category ${!item.is_active ? 'activated' : 'deactivated'} successfully`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } catch (error) {
    // Revert on error
    await fetchFreshCategories(true);

    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'Failed to toggle status',
      confirmButtonColor: '#dc2626'
    });

  } finally {
    togglingId.value = null;
  }
};

// Delete
const confirmDelete = async (item) => {
  const result = await Swal.fire({
    title: 'Delete Category?',
    text: `Are you sure you want to delete "${item.name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    deletingId.value = item.id;

    try {
      // Optimistic delete
      categories.value = categories.value.filter(c => c.id !== item.id);

      await caseCategoryService.deleteCategory(item.id);
      
      // Update cache
      cacheService.remove(cacheService.CACHE_KEYS.CATEGORIES);

      await Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'Category deleted successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

      // Refresh in background
      setTimeout(() => {
        fetchFreshCategories(false);
      }, 500);

    } catch (error) {
      // Revert on error
      await fetchFreshCategories(true);

      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.message || 'Failed to delete category',
        confirmButtonColor: '#dc2626'
      });

    } finally {
      deletingId.value = null;
    }
  }
};

// Watch for page changes
watch(currentPage, () => {
  fetchFreshCategories(true);
});

// Initial load
onMounted(() => {
  // 1. Load from cache INSTANTLY
  loadFromCache();
  
  // 2. Fetch fresh data in background
  setTimeout(() => {
    fetchFreshCategories(false);
  }, 100);
});
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>