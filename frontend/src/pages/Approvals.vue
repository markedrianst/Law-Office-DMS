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

    <!-- Filters Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
      <!-- Search -->
      <div class="relative mb-3">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <input 
          v-model="filters.search"
          @input="applyFilters"
          type="text"
          placeholder="Search by case code, task, clerk name, purpose..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
        />
      </div>

      <!-- Filter Dropdowns -->
      <div class="flex flex-wrap gap-2.5">
        <select 
          v-model="filters.status" 
          @change="applyFilters"
          class="flex-1 min-w-[120px] px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-blue-400 bg-white"
        >
          <option value="ALL">All Status</option>
          <option value="PENDING">⏳ Pending</option>
          <option value="APPROVED">✓ Approved</option>
          <option value="REJECTED">✗ Rejected</option>
        </select>

        <select 
          v-model="filters.type" 
          @change="applyFilters"
          class="flex-1 min-w-[100px] px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-blue-400 bg-white"
        >
          <option value="all">All Types</option>
          <option value="checklist">📋 Checklist</option>
          <option value="folder">📁 Folder</option>
        </select>

        <select 
          v-model="filters.direction" 
          @change="applyFilters"
          class="flex-1 min-w-[100px] px-3 py-2.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-blue-400 bg-white"
        >
          <option value="ALL">In & Out</option>
          <option value="OUT">↗ OUT</option>
          <option value="IN">↙ IN</option>
        </select>

        <button 
          v-if="hasActiveFilters" 
          @click="clearFilters"
          class="px-4 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
        >
          Clear Filters
        </button>

        <button 
          @click="loadApprovals"
          class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh
        </button>
      </div>
    </div>

   <!-- Enhanced Loading State -->
<div v-if="loading" class="bg-white rounded-xl shadow-sm border border-slate-200 py-24 flex flex-col items-center">
  <!-- Animated Spinner -->
  <div class="relative mb-6">
    <!-- Outer ring -->
    <div class="w-16 h-16 rounded-full border-4 border-blue-100 absolute"></div>
    <!-- Spinning ring -->
    <div class="w-16 h-16 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
    <!-- Inner pulse -->
    <div class="absolute inset-0 flex items-center justify-center">
      <div class="w-4 h-4 rounded-full bg-blue-600 animate-pulse"></div>
    </div>
  </div>
  
  <!-- Loading text with animation -->
  <p class="text-base font-semibold text-slate-700 mb-2 animate-pulse">Loading Approvals</p>
  <p class="text-sm text-slate-500">Please wait while we fetch the data...</p>
  
  <!-- Progress dots -->
  <div class="flex gap-2 mt-4">
    <div class="w-2 h-2 rounded-full bg-blue-600 animate-bounce" style="animation-delay: 0ms;"></div>
    <div class="w-2 h-2 rounded-full bg-blue-600 animate-bounce" style="animation-delay: 150ms;"></div>
    <div class="w-2 h-2 rounded-full bg-blue-600 animate-bounce" style="animation-delay: 300ms;"></div>
  </div>
</div>

    <!-- Empty State -->
    <div v-else-if="!approvals.length" class="bg-white rounded-xl shadow-sm border border-slate-200 py-16 flex flex-col items-center">
      <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <p class="text-base font-medium text-slate-700 mb-1">No approvals found</p>
      <p class="text-sm text-slate-500">Try adjusting your filters</p>
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
                    {{ getInitialsHelper(item.recorder?.full_name) }}
                  </div>
                  <span class="text-sm text-slate-700">{{ item.recorder?.full_name || '—' }}</span>
                </div>
              </td>

              <!-- Date -->
              <td class="px-4 py-3">
                <span class="text-sm text-slate-600">{{ formatDateHelper(item.date) }}</span>
              </td>

              <!-- Status -->
              <td class="px-4 py-3">
                <span 
                  class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                  :class="statusClassHelper(item.approval_status)"
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
                <span v-else class="text-xs text-slate-400 italic">
                  {{ item.approved_by ? `by ${item.approver?.full_name || 'Unknown'}` : '' }}
                </span>
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
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
      <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <div class="flex items-start gap-4 mb-4">
          <div 
            class="w-12 h-12 rounded-full flex items-center justify-center"
            :class="modal.action === 'APPROVED' ? 'bg-emerald-100' : 'bg-red-100'"
          >
            <svg 
              class="w-6 h-6" 
              :class="modal.action === 'APPROVED' ? 'text-emerald-600' : 'text-red-600'"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path v-if="modal.action === 'APPROVED'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-slate-900 mb-1">
              {{ modal.action === 'APPROVED' ? 'Approve Movement' : 'Reject Movement' }}
            </h3>
            <p class="text-sm text-slate-600">
              {{ modal.action === 'APPROVED' 
                ? 'This will mark the movement as approved and update the status.'
                : 'Please provide a reason for rejection.' 
              }}
            </p>
          </div>
        </div>

        <!-- Movement Details -->
        <div class="bg-slate-50 rounded-lg p-3 mb-4 text-sm">
          <div class="flex items-center gap-2 mb-2">
            <span class="font-medium text-slate-700">Case:</span>
            <span class="text-blue-700">{{ modal.item?.case_code || `Case #${modal.item?.case_id}` }}</span>
          </div>
          <div class="flex items-center gap-4">
            <span class="px-2 py-0.5 rounded text-xs font-medium" :class="modal.item?.source === 'checklist' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'">
              {{ modal.item?.source === 'checklist' ? 'Checklist' : 'Folder' }}
            </span>
            <span class="px-2 py-0.5 rounded text-xs font-medium" :class="modal.item?.type === 'OUT' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">
              {{ modal.item?.type }}
            </span>
          </div>
        </div>

        <!-- Notes Input (Required for Rejection) -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Notes <span v-if="modal.action === 'REJECTED'" class="text-red-500">*</span>
          </label>
          <textarea
            v-model="modal.notes"
            rows="3"
            :placeholder="modal.action === 'APPROVED' ? 'Optional approval notes...' : 'Required: Reason for rejection'"
            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
            :class="{ 'border-red-300': modal.action === 'REJECTED' && !modal.notes }"
          ></textarea>
          <p v-if="modal.action === 'REJECTED' && !modal.notes" class="text-xs text-red-500 mt-1">
            Rejection reason is required
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button
            @click="closeModal"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
          >
            Cancel
          </button>
          <button
            @click="submitDecision"
            :disabled="modal.processing || (modal.action === 'REJECTED' && !modal.notes)"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-white rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="modal.action === 'APPROVED' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'"
          >
            <svg v-if="modal.processing" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ modal.processing ? 'Processing...' : (modal.action === 'APPROVED' ? 'Approve' : 'Reject') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div 
      v-if="toast.show" 
      class="fixed bottom-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
      :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'"
    >
      <div class="w-5 h-5 rounded-full flex items-center justify-center" :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'">
        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </div>
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue';
import approvalService from '@/services/approvalService';
import Swal from 'sweetalert2';

// Import from appUtils
import { 
  getApprovals,
  getApprovalStats,
  setApprovals,
  setApprovalStats,
  listenForUpdates,
  formatDate,
  formatDateTime,
  getInitials
} from '@/utils/appUtils';

// ========== STATE ==========
// Get initial data from appUtils (INSTANT!)
const initialApprovals = getApprovals();
const initialStats = getApprovalStats();

console.log('📊 Initial approvals from appUtils:', initialApprovals?.length);
console.log('📊 Initial stats from appUtils:', initialStats);

const approvals = ref(initialApprovals || []);
const stats = ref(initialStats || { total: 0, pending: 0, approved: 0, rejected: 0 });
const loading = ref(false); // ⬅️ ALWAYS FALSE - we have cached data
const isRefreshing = ref(false);
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

// ========== FETCH APPROVALS (Background Refresh) ====================
const fetchApprovals = async (showLoading = false) => {
  // Don't show loading spinner when refreshing in background
  if (showLoading) loading.value = true;
  isRefreshing.value = true;
  
  try {
    const params = {
      status: filters.status !== 'ALL' ? filters.status : undefined,
      type: filters.type !== 'all' ? filters.type : undefined,
      direction: filters.direction !== 'ALL' ? filters.direction : undefined,
      search: filters.search || undefined
    };
    
    console.log('Fetching approvals with params:', params);
    const response = await approvalService.getApprovals(params);
    console.log('Approvals response:', response);
    
    // approvals are automatically updated via appUtils
    lastUpdated.value = new Date().toLocaleTimeString();
    
  } catch (error) {
    console.error('Failed to load approvals:', error);
    showToast(error.message || 'Failed to load approvals', 'error');
  } finally {
    if (showLoading) loading.value = false;
    isRefreshing.value = false;
  }
};

// ========== INITIALIZE ====================
const initialize = async () => {
  console.log('🚀 Initializing Approvals...');
  console.log('📊 Approvals in ref (cached):', approvals.value.length);
  
  // ALWAYS fetch fresh data in background WITHOUT showing loading
  // User sees cached data instantly, then it updates silently
  console.log('📡 Fetching fresh approvals in background...');
  fetchApprovals(false); // ⬅️ Pass false to hide loading spinner
};

// ========== FILTER METHODS ====================
const applyFilters = () => {
  fetchApprovals(true); // ⬅️ Show loading when manually filtering
};

const clearFilters = () => {
  filters.status = 'ALL';
  filters.type = 'all';
  filters.direction = 'ALL';
  filters.search = '';
  fetchApprovals(true); // ⬅️ Show loading when clearing filters
};

// ========== MODAL METHODS ====================
const openApproveModal = (item) => {
  console.log('Opening approve modal for:', item);
  modal.show = true;
  modal.item = item;
  modal.action = 'APPROVED';
  modal.notes = '';
  modal.processing = false;
};

const openRejectModal = (item) => {
  console.log('Opening reject modal for:', item);
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

const openViewModal = (item) => {
  viewModal.item = item;
  viewModal.show = true;
};

const closeViewModal = () => {
  viewModal.show = false;
  viewModal.item = null;
};

// ========== SUBMIT DECISION ====================
const submitDecision = async () => {
  console.log('Submitting decision:', {
    source: modal.item.source,
    id: modal.item.id,
    action: modal.action,
    notes: modal.notes
  });
  
  if (modal.action === 'REJECTED' && !modal.notes) {
    showToast('Please provide a reason for rejection', 'error');
    return;
  }

  modal.processing = true;
  
  try {
    const response = await approvalService.reviewMovement(
      modal.item.source,
      modal.item.id,
      modal.action,
      modal.notes
    );
    
    console.log('Review response:', response);
    
    showToast(
      `Movement ${modal.action === 'APPROVED' ? 'approved' : 'rejected'} successfully`,
      'success'
    );
    
    closeModal();
    await fetchApprovals(false); // ⬅️ Refresh in background after action
    
  } catch (error) {
    console.error('Review error:', error);
    showToast(
      error.response?.data?.message || 
      error.message || 
      `Failed to ${modal.action === 'APPROVED' ? 'approve' : 'reject'} movement`, 
      'error'
    );
  } finally {
    modal.processing = false;
  }
};

// ========== TOAST METHODS ====================
const showToast = (message, type = 'success') => {
  toast.show = true;
  toast.message = message;
  toast.type = type;
  
  setTimeout(() => {
    toast.show = false;
  }, 3000);
};

// ========== LISTEN FOR UPDATES ====================
const handleApprovalsUpdated = (event) => {
  console.log('🔄 Approvals updated event received');
  approvals.value = event.detail;
};

const handleStatsUpdated = (event) => {
  console.log('🔄 Approval stats updated event received');
  stats.value = event.detail;
};

let cleanupApprovals = null;
let cleanupStats = null;

// ========== HELPER FUNCTIONS (FOR TEMPLATE USE) ==========
const formatDateHelper = (date) => {
  return formatDate(date);
};

const formatDateTimeHelper = (date) => {
  return formatDateTime(date);
};

const getInitialsHelper = (name) => {
  return getInitials(name);
};

const statusClassHelper = (status) => {
  const classes = {
    PENDING: 'bg-amber-100 text-amber-700',
    APPROVED: 'bg-emerald-100 text-emerald-700',
    REJECTED: 'bg-red-100 text-red-700'
  };
  return classes[status] || 'bg-slate-100 text-slate-600';
};

// ========== MANUAL REFRESH ====================
const manualRefresh = async () => {
  isRefreshing.value = true;
  await fetchApprovals(true); // ⬅️ Show loading on manual refresh
  isRefreshing.value = false;
  
  Swal.fire({
    icon: 'success',
    title: 'Refreshed!',
    text: 'Approvals list updated',
    timer: 1500,
    showConfirmButton: false,
    position: 'top-end',
    toast: true
  });
};

// ========== LIFECYCLE ====================
onMounted(async () => {
  console.log('🚀 Approvals mounted');
  await initialize();
  
  cleanupApprovals = listenForUpdates('approvals-updated', handleApprovalsUpdated);
  cleanupStats = listenForUpdates('approval-stats-updated', handleStatsUpdated);
});

onUnmounted(() => {
  if (cleanupApprovals) cleanupApprovals();
  if (cleanupStats) cleanupStats();
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
</style>