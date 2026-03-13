<template>
  <div class="p-4 md:p-6 bg-slate-50 min-h-screen" style="font-family: 'Inter', sans-serif;">
    
    <!-- Header with Stats -->
    <div class="mb-6">
      <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
          <div class="flex items-center gap-3 mb-1.5">
            <div class="w-1 h-8 rounded-full bg-blue-600"></div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900">Approvals Dashboard</h1>
          </div>
          <p class="text-sm text-slate-500">Review and manage pending requests</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="flex gap-3">
          <div class="bg-white rounded-lg shadow-sm border border-slate-200 px-4 py-2">
            <span class="text-xs text-slate-500">Pending</span>
            <div class="text-xl font-bold text-amber-600">{{ stats.pending }}</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-slate-200 px-4 py-2">
            <span class="text-xs text-slate-500">Approved</span>
            <div class="text-xl font-bold text-emerald-600">{{ stats.approved }}</div>
          </div>
          <div class="bg-white rounded-lg shadow-sm border border-slate-200 px-4 py-2">
            <span class="text-xs text-slate-500">Rejected</span>
            <div class="text-xl font-bold text-red-600">{{ stats.rejected }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Bar (same as before) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
      <!-- ... existing filter code ... -->
    </div>

    <!-- Empty State -->
    <div v-if="!approvals.length" class="bg-white rounded-xl shadow-sm border border-slate-200 py-16 flex flex-col items-center">
      <!-- ... existing empty state ... -->
    </div>

    <!-- Approvals Table -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Case</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Direction</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Details</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">From/To</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Recorded By</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr 
              v-for="item in approvals" 
              :key="`${item.source}-${item.id}`"
              class="hover:bg-slate-50 transition-colors"
              :class="{ 'bg-amber-50/30': item.approval_status === 'PENDING' }"
            >
              <!-- Case -->
              <td class="px-4 py-3">
                <span class="text-sm font-medium text-blue-700">{{ item.case_code || `Case #${item.case_id}` }}</span>
              </td>

              <!-- Type -->
              <td class="px-4 py-3">
                <span 
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                  :class="item.source === 'checklist' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'"
                >
                  {{ item.source === 'checklist' ? '📋 Checklist' : '📁 Folder' }}
                </span>
              </td>

              <!-- Direction -->
              <td class="px-4 py-3">
                <span 
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                  :class="item.type === 'OUT' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'"
                >
                  {{ item.type }}
                </span>
              </td>

              <!-- Details -->
              <td class="px-4 py-3 max-w-[200px]">
                <div class="text-sm font-medium text-slate-800 truncate">
                  {{ item.source === 'checklist' ? (item.task_name || item.checklist?.task) : 'Folder Movement' }}
                </div>
                <div v-if="item.purpose" class="text-xs text-slate-500 truncate">
                  {{ item.purpose }}
                </div>
              </td>

              <!-- From/To -->
              <td class="px-4 py-3">
                <span class="text-sm text-slate-600">{{ item.from_to || '—' }}</span>
              </td>

              <!-- Recorded By -->
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-medium text-blue-700">
                    {{ getInitials(item.recorder?.full_name) }}
                  </div>
                  <span class="text-sm text-slate-700">{{ item.recorder?.full_name || '—' }}</span>
                </div>
              </td>

              <!-- Date -->
              <td class="px-4 py-3">
                <span class="text-sm text-slate-600">{{ formatDate(item.date) }}</span>
              </td>

              <!-- Status -->
              <td class="px-4 py-3">
                <span 
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                  :class="statusClass(item.approval_status)"
                >
                  <span v-if="item.approval_status === 'PENDING'" class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                  {{ item.approval_status }}
                </span>
                <div v-if="item.notes" class="text-xs text-slate-500 mt-1 max-w-[150px] truncate" :title="item.notes">
                  📝 {{ item.notes }}
                </div>
              </td>

              <!-- Actions -->
              <td class="px-4 py-3">
                <!-- PENDING: Show Approve/Reject buttons -->
                <div v-if="item.approval_status === 'PENDING'" class="flex items-center gap-2">
                  <button 
                    @click="openApproveModal(item)"
                    class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors flex items-center gap-1"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approve
                  </button>
                  <button 
                    @click="openRejectModal(item)"
                    class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors flex items-center gap-1"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reject
                  </button>
                </div>

                <!-- APPROVED/REJECTED: Show View Details button -->
                <div v-else class="flex items-center gap-2">
                  <button 
                    @click="openViewModal(item)"
                    class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors flex items-center gap-1"
                  >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View
                  </button>
                  <span v-if="item.approved_by" class="text-xs text-slate-400 italic">
                    by {{ item.approver?.full_name || 'Unknown' }}
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table Footer -->
      <div class="px-4 py-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between">
        <p class="text-xs text-slate-600">
          Showing <span class="font-medium">{{ approvals.length }}</span> of 
          <span class="font-medium">{{ stats.total }}</span> movements
        </p>
        <div class="flex items-center gap-2">
          <span class="text-xs text-slate-500">Last updated: {{ lastUpdated }}</span>
        </div>
      </div>
    </div>

    <!-- Approval/Rejection Modal -->
    <div v-if="modal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal">
      <!-- ... existing modal code ... -->
    </div>

<!-- ========== VIEW DETAILS MODAL - HORIZONTAL LAYOUT ========== -->
<Transition name="modal">
  <div v-if="viewModal.show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeViewModal">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
    
    <!-- Modal Container - Wider, shorter -->
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-3xl overflow-hidden">
      
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
        <div class="flex items-center gap-3">
          <div 
            class="w-10 h-10 rounded-full flex items-center justify-center"
            :class="viewModal.item?.approval_status === 'APPROVED' ? 'bg-emerald-100' : 'bg-red-100'"
          >
            <svg 
              class="w-5 h-5" 
              :class="viewModal.item?.approval_status === 'APPROVED' ? 'text-emerald-600' : 'text-red-600'"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path v-if="viewModal.item?.approval_status === 'APPROVED'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">
              {{ viewModal.item?.source === 'checklist' ? 'Checklist Movement' : 'Folder Movement' }}
            </h3>
            <p class="text-xs text-slate-500">
              {{ viewModal.item?.approval_status }} · {{ formatDateTime(viewModal.item?.approved_at) }}
            </p>
          </div>
        </div>
        <button @click="closeViewModal" class="p-2 hover:bg-slate-100 rounded-lg text-slate-400 hover:text-slate-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Body - Horizontal Grid Layout -->
      <div class="p-6">
        <!-- Two-column grid -->
        <div class="grid grid-cols-2 gap-6">
          
          <!-- Left Column -->
          <div class="space-y-4">
            <!-- Case Info Card -->
            <div class="bg-slate-50 rounded-lg p-4">
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Case Information</h4>
              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Case Code</span>
                  <span class="text-sm font-semibold text-blue-700">{{ viewModal.item?.case_code || `Case #${viewModal.item?.case_id}` }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Type</span>
                  <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-xs font-medium rounded" :class="viewModal.item?.source === 'checklist' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'">
                      {{ viewModal.item?.source === 'checklist' ? 'Checklist' : 'Folder' }}
                    </span>
                    <span class="px-2 py-0.5 text-xs font-medium rounded" :class="viewModal.item?.type === 'OUT' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">
                      {{ viewModal.item?.type }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Task Details (for checklist) -->
            <div v-if="viewModal.item?.source === 'checklist'" class="bg-slate-50 rounded-lg p-4">
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Task Details</h4>
              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Task</span>
                  <span class="text-sm font-medium text-slate-700">{{ viewModal.item?.task_name || viewModal.item?.checklist?.task || '—' }}</span>
                </div>
              </div>
            </div>

            <!-- Movement Details -->
            <div class="bg-slate-50 rounded-lg p-4">
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Movement Details</h4>
              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">From / To</span>
                  <span class="text-sm font-medium text-slate-700">{{ viewModal.item?.from_to || '—' }}</span>
                </div>
                <div v-if="viewModal.item?.purpose" class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Purpose</span>
                  <span class="text-sm text-slate-600">{{ viewModal.item.purpose }}</span>
                </div>
                <div v-if="viewModal.item?.handled_by" class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Handled By</span>
                  <span class="text-sm font-medium text-slate-700">{{ viewModal.item.handled_by }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Movement Date</span>
                  <span class="text-sm text-slate-600">{{ formatDate(viewModal.item?.date) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column -->
          <div class="space-y-4">
            <!-- People Involved -->
            <div class="bg-slate-50 rounded-lg p-4">
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">People Involved</h4>
              <div class="space-y-3">
                <!-- Recorded By -->
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700">
                    {{ getInitials(viewModal.item?.recorder?.full_name) }}
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">Recorded By</p>
                    <p class="text-sm font-semibold text-slate-800">{{ viewModal.item?.recorder?.full_name || '—' }}</p>
                  </div>
                </div>

                <!-- Approved By (if available) -->
                <div v-if="viewModal.item?.approver" class="flex items-center gap-3 pt-2 border-t border-slate-200/50">
                  <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-xs font-bold text-emerald-700">
                    {{ getInitials(viewModal.item.approver.full_name) }}
                  </div>
                  <div>
                    <p class="text-xs text-slate-500">Approved By</p>
                    <p class="text-sm font-semibold text-slate-800">{{ viewModal.item.approver.full_name }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Status & Dates -->
            <div class="bg-slate-50 rounded-lg p-4">
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Status & Dates</h4>
              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Status</span>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full" :class="statusClass(viewModal.item?.approval_status)">
                    <span v-if="viewModal.item?.approval_status === 'PENDING'" class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    {{ viewModal.item?.approval_status }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Created</span>
                  <span class="text-sm text-slate-600">{{ formatDateTime(viewModal.item?.created_at) }}</span>
                </div>
                <div v-if="viewModal.item?.approved_at" class="flex justify-between items-center">
                  <span class="text-xs text-slate-500">Approved At</span>
                  <span class="text-sm text-slate-600">{{ formatDateTime(viewModal.item.approved_at) }}</span>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="viewModal.item?.notes" class="bg-slate-50 rounded-lg p-4">
              <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Notes</h4>
              <div class="bg-white rounded-lg p-3 text-sm text-slate-700 border border-slate-200">
                {{ viewModal.item.notes }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end px-6 py-4 border-t border-slate-200 bg-slate-50">
        <button
          @click="closeViewModal"
          class="px-5 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</Transition>

    <!-- Toast Notification -->
    <div 
      v-if="toast.show" 
      class="fixed bottom-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
      :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'"
    >
      <!-- ... existing toast code ... -->
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import approvalService from '@/services/approvalService';
import cacheService from '@/services/cacheService';

// ========== STATE ==========
const approvals = ref([]);
const loading = ref(false);
const isRefreshing = ref(false);
const isFromCache = ref(false);
const stats = ref({
  total: 0,
  pending: 0,
  approved: 0,
  rejected: 0
});
const lastUpdated = ref('');

const filters = reactive({
  status: 'ALL',
  type: 'all',
  direction: 'ALL',
  search: ''
});

const modal = reactive({
  show: false,
  item: null,
  action: null,
  notes: '',
  processing: false
});

// ========== NEW: VIEW MODAL STATE ==========
const viewModal = reactive({
  show: false,
  item: null
});

const toast = reactive({
  show: false,
  message: '',
  type: 'success'
});

// ========== COMPUTED ==========
const hasActiveFilters = computed(() => {
  return filters.status !== 'ALL' || 
         filters.type !== 'all' || 
         filters.direction !== 'ALL' || 
         filters.search !== '';
});

// ========== LOAD FROM CACHE FIRST ==========
const loadFromCache = () => {
  const params = {
    status: filters.status,
    type: filters.type,
    direction: filters.direction,
    search: filters.search || undefined
  };
  
  const cached = cacheService.getApprovalsList(params);
  if (cached) {
    console.log('📦 Loading approvals from cache');
    approvals.value = cached.data || [];
    stats.value = cached.stats || { total: 0, pending: 0, approved: 0, rejected: 0 };
    isFromCache.value = true;
  }
};

// ========== FETCH FRESH APPROVALS ==========
const fetchFreshApprovals = async (showLoading = true) => {
  if (showLoading) loading.value = true;
  isRefreshing.value = true;
  
  try {
    const params = {
      status: filters.status,
      type: filters.type,
      direction: filters.direction,
      search: filters.search || undefined
    };
    
    const response = await approvalService.getApprovals(params);
    
    approvals.value = response.data || [];
    stats.value = response.stats || { total: 0, pending: 0, approved: 0, rejected: 0 };
    lastUpdated.value = new Date().toLocaleTimeString();
    
    // Save to cache
    cacheService.setApprovalsList({ data: approvals.value, stats: stats.value }, params);
    isFromCache.value = false;
    
  } catch (error) {
    showToast(error.message || 'Failed to load approvals', 'error');
  } finally {
    if (showLoading) loading.value = false;
    isRefreshing.value = false;
  }
};

// ========== LOAD APPROVALS (with cache first) ==========
const loadApprovals = async (forceRefresh = false) => {
  if (forceRefresh) {
    await fetchFreshApprovals(true);
  } else {
    // Try cache first
    loadFromCache();
    
    // Fetch fresh in background
    setTimeout(() => {
      fetchFreshApprovals(false);
    }, 100);
  }
};

// ========== FILTER METHODS ==========
const applyFilters = () => {
  fetchFreshApprovals(true);
};

const clearFilters = () => {
  filters.status = 'ALL';
  filters.type = 'all';
  filters.direction = 'ALL';
  filters.search = '';
  fetchFreshApprovals(true);
};

// ========== MODAL METHODS ==========
const openApproveModal = (item) => {
  modal.show = true;
  modal.item = item;
  modal.action = 'APPROVED';
  modal.notes = '';
  modal.processing = false;
};

const openRejectModal = (item) => {
  modal.show = true;
  modal.item = item;
  modal.action = 'REJECTED';
  modal.notes = '';
  modal.processing = false;
};

const closeModal = () => {
  modal.show = false;
  modal.item = null;
  modal.action = null;
  modal.notes = '';
  modal.processing = false;
};

// ========== NEW: VIEW MODAL METHODS ==========
const openViewModal = (item) => {
  viewModal.item = item;
  viewModal.show = true;
};

const closeViewModal = () => {
  viewModal.show = false;
  viewModal.item = null;
};

const submitDecision = async () => {
  if (modal.action === 'REJECTED' && !modal.notes) {
    showToast('Please provide a reason for rejection', 'error');
    return;
  }

  modal.processing = true;
  
  try {
    await approvalService.reviewMovement(
      modal.item.source,
      modal.item.id,
      modal.action,
      modal.notes
    );
    
    showToast(
      `Movement ${modal.action === 'APPROVED' ? 'approved' : 'rejected'} successfully`,
      'success'
    );
    
    closeModal();
    
    // Invalidate cache and refresh
    cacheService.invalidateApprovalsCache();
    await fetchFreshApprovals(true);
    
  } catch (error) {
    showToast(error.message || `Failed to ${modal.action === 'APPROVED' ? 'approve' : 'reject'} movement`, 'error');
  } finally {
    modal.processing = false;
  }
};

// ========== TOAST METHODS ==========
const showToast = (message, type = 'success') => {
  toast.show = true;
  toast.message = message;
  toast.type = type;
  
  setTimeout(() => {
    toast.show = false;
  }, 3000);
};

// ========== HELPERS ==========
const formatDate = (date) => {
  if (!date) return '—';
  const d = new Date(date);
  if (isNaN(d.getTime())) return date;
  return d.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  });
};

const formatDateTime = (date) => {
  if (!date) return '—';
  const d = new Date(date);
  if (isNaN(d.getTime())) return date;
  return d.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const getInitials = (name) => {
  if (!name) return '?';
  return name
    .split(' ')
    .map(p => p[0])
    .join('')
    .slice(0, 2)
    .toUpperCase();
};

const statusClass = (status) => {
  const classes = {
    PENDING: 'bg-amber-100 text-amber-700',
    APPROVED: 'bg-emerald-100 text-emerald-700',
    REJECTED: 'bg-red-100 text-red-700'
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
};

// ========== LIFECYCLE ==========
onMounted(() => {
  // 1. Load from cache INSTANTLY
  loadFromCache();
  
  // 2. Fetch fresh data in background
  setTimeout(() => {
    fetchFreshApprovals(false);
  }, 100);
});
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.25s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>