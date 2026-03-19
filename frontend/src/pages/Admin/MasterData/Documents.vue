<template>
  <div class="min-h-screen p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Document Types</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage document types and approval requirements</p>
    </div>

    <!-- Pending Approvals Alert (for Lawyers) -->
    <div v-if="pendingApprovals.length > 0 && userRole === 'lawyer'" class="mb-4">
      <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-xl p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-amber-800">{{ pendingApprovals.length }} document(s) pending your approval</p>
            <p class="text-xs text-amber-600">Review and approve/reject pending documents</p>
          </div>
        </div>
        <button @click="showPendingModal = true" 
          class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-xl transition">
          Review Now
        </button>
      </div>
    </div>

    <!-- Last updated indicator (non-blocking) -->
    <div class="text-xs text-slate-400 mb-2 ml-4 flex items-center gap-2">
      <span>📄 Documents loaded from cache</span>
      <span class="w-1 h-1 rounded-full bg-slate-300"></span>
      <span>{{ lastUpdated }}</span>
      <span v-if="isRefreshing" class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
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
          <input 
            v-model="filters.search" 
            @input="debouncedSearch" 
            type="text"
            placeholder="Search documents..."
            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:bg-white transition-all" 
          />
        </div>

        <select 
          v-model="filters.category" 
          @change="handleFilterChange"
          class="px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100"
        >
          <option value="">All Categories</option>
          <option v-for="cat in documentCategories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <select 
          v-model="filters.approval_status" 
          @change="handleFilterChange"
          class="px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100"
        >
          <option value="">All Approval Status</option>
          <option value="pending">Pending Approval</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>

        <select 
          v-model="filters.is_active" 
          @change="handleFilterChange"
          class="px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100"
        >
          <option value="">All Status</option>
          <option value="true">Active Only</option>
          <option value="false">Inactive Only</option>
        </select>

        <!-- Add Document Button -->
        <button @click="openCreateModal" :disabled="isAdding"
          class="text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center transition-all whitespace-nowrap hover:shadow-lg active:scale-95 disabled:opacity-50 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30"
        >
          <svg v-if="!isAdding" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <svg v-else class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ isAdding ? 'Adding...' : 'Add Document' }}
        </button>
      </div>
    </div>
        <!-- Loading State - Only shown on first visit -->
          <div v-if="isLoading" class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 flex flex-col items-center">
            <div class="w-12 h-12 rounded-full border-4 border-blue-200 border-t-[#1a4972] animate-spin mb-4"></div>
            <p class="text-sm text-slate-500">Loading cases...</p>
          </div>
    <!-- Documents Table - Always shows instantly from cache -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-slate-100 bg-[#1a4972]/5">
            <th 
              v-for="col in columns" 
              :key="col.field" 
              scope="col"
              class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
              :class="col.sortable ? 'cursor-pointer hover:text-[#1a4972] select-none group' : ''"
              @click="col.sortable ? sortBy(col.field) : null"
            >
              <div class="flex items-center gap-1.5">
                {{ col.label }}
                <svg 
                  v-if="col.sortable && filters.sort_by === col.field" 
                  class="w-3.5 h-3.5 text-[#1a4972]" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path :d="filters.sort_direction === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" stroke-width="2.5"/>
                </svg>
                <svg 
                  v-else-if="col.sortable" 
                  class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-400" 
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
              </div>
            </th>
          </tr>
        </thead>
  
        <tbody  class="divide-y divide-slate-50">
          <tr 
            v-for="(item, index) in documents" 
            :key="item.id" 
            class="transition-all duration-300 hover:bg-blue-50/30 group"
            :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.03}s both` }"
          >
            <!-- Color + Type -->
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <div class="w-6 h-6 rounded-full border-2 border-white shadow-sm" :style="{ backgroundColor: item.color }"></div>
                <span class="text-sm font-semibold text-slate-800">{{ item.type }}</span>
              </div>
            </td>

            <!-- Category -->
            <td class="px-5 py-4">
              <span class="inline-block px-2.5 py-1 text-xs font-semibold rounded-full"
                :class="categoryBadgeClass(item.category)">
                {{ item.category }}
              </span>
            </td>

            <!-- Approval Status -->
            <td class="px-5 py-4">
              <div v-if="item.requires_approval" class="flex flex-col gap-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg"
                  :class="approvalStatusClass(item.approval_status)">
                  <span class="w-1.5 h-1.5 rounded-full" :class="approvalStatusDot(item.approval_status)"></span>
                  {{ formatApprovalStatus(item.approval_status) }}
                </span>
                <span v-if="item.approval_status === 'approved' && item.approver" class="text-[10px] text-slate-400">
                  by {{ item.approver.full_name }}
                </span>
                <span v-if="item.approval_status === 'rejected' && item.rejection_reason" 
                  class="text-[10px] text-red-400 truncate max-w-[150px]" :title="item.rejection_reason">
                  {{ item.rejection_reason }}
                </span>
              </div>
              <span v-else class="text-sm text-slate-400">—</span>
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
            <td class="px-5 py-4 text-sm text-slate-400">{{ formatDateHelper(item.created_at) }}</td>

            <!-- Actions -->
            <td class="px-5 py-4">
              <div class="flex items-center gap-2">
                <!-- Approve/Reject buttons for pending docs (Lawyers only) -->
                <template v-if="item.requires_approval && item.approval_status === 'pending' && userRole === 'lawyer'">
                  <button @click="approveDocument(item)" :disabled="approvingId === item.id"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-emerald-600 hover:bg-emerald-50 transition-all disabled:opacity-50">
                    <svg v-if="approvingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Approve
                  </button>
                  <button @click="showRejectModal(item)" :disabled="rejectingId === item.id"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50 transition-all disabled:opacity-50">
                    <svg v-if="rejectingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Reject
                  </button>
                </template>

                <!-- Regular Edit button for all other cases -->
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
          <tr v-if="documents.length === 0 && !isRefreshing">
            <td :colspan="columns.length" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-[#1a4972]/10 flex items-center justify-center mb-3">
                  <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700 mb-1">No documents found</p>
                <p class="text-xs text-slate-400">Click "Add Document" to create one</p>
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
          <span class="font-semibold text-slate-700">{{ pagination.total }}</span> documents
        </p>
        <div class="flex items-center gap-1">
          <button 
            @click="previousPage" 
            :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'"
          >
            ← Prev
          </button>
          <button 
            v-for="page in displayedPages" 
            :key="page" 
            @click="goToPage(page)"
            class="w-7 h-7 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === page ? 'bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white' : 'text-slate-600 hover:bg-slate-200'"
          >
            {{ page }}
          </button>
          <button 
            @click="nextPage" 
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === pagination.last_page ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'"
          >
            Next →
          </button>
        </div>
      </div>
    </div>

    <!-- Modals (shortened for brevity) -->
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-bold text-slate-800">{{ isEditing ? 'Edit Document' : 'Add Document' }}</h2>
                <p class="text-sm text-slate-500">{{ isEditing ? 'Update document type' : 'Create a new document type' }}</p>
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
            <!-- Document Type -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Document Type <span class="text-red-500">*</span></label>
              <input v-model="form.type" type="text" placeholder="e.g. Complaint, Motion, etc."
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                :class="{ 'border-red-400': errors.type }" />
              <p v-if="errors.type" class="text-sm text-red-500 mt-1">{{ errors.type }}</p>
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category <span class="text-red-500">*</span></label>
              <select v-model="form.category"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600"
                :class="{ 'border-red-400': errors.category }">
                <option value="" disabled>Select category</option>
                <option v-for="cat in documentCategories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
              <p v-if="errors.category" class="text-sm text-red-500 mt-1">{{ errors.category }}</p>
            </div>

            <!-- Color Picker -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Document Color</label>
              <div class="flex items-center gap-3">
                <ColorPicker v-model="form.color" />
                <input v-model="form.color" type="text" placeholder="#HEX"
                  class="flex-1 px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all font-mono" />
              </div>
            </div>

            <!-- Requires Approval -->
            <div class="flex items-center gap-2">
              <input type="checkbox" v-model="form.requires_approval" id="requiresApproval" 
                class="w-4 h-4 rounded border-slate-300 text-[#1a4972] focus:ring-[#1a4972]" />
              <label for="requiresApproval" class="text-sm font-medium text-slate-700">
                Requires Lawyer Approval
              </label>
            </div>

            <!-- Sort Order -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" min="0" placeholder="0"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all" />
              <p class="text-xs text-slate-400 mt-1">Lower numbers appear first (9999 = Others)</p>
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
              {{ formLoading ? (isEditing ? 'Saving...' : 'Adding...') : (isEditing ? 'Save Changes' : 'Add Document') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Pending Approvals Modal (for Lawyers) -->
    <Transition name="modal">
      <div v-if="showPendingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="showPendingModal = false">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
          
          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-bold text-slate-800">Pending Approvals</h2>
                <p class="text-sm text-slate-500">Review and approve/reject document types</p>
              </div>
            </div>
            <button @click="showPendingModal = false" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="px-6 py-5 max-h-96 overflow-y-auto">
            <div v-if="pendingApprovals.length === 0" class="py-8 text-center">
              <p class="text-sm text-slate-500">No pending approvals</p>
            </div>
            <div v-else class="space-y-3">
              <div v-for="doc in pendingApprovals" :key="doc.id" 
                class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full" :style="{ backgroundColor: doc.color }"></div>
                  <div>
                    <p class="text-sm font-semibold text-slate-800">{{ doc.type }}</p>
                    <p class="text-xs text-slate-500">Category: {{ doc.category }}</p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <button @click="approveDocument(doc)" :disabled="approvingId === doc.id"
                    class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1">
                    <svg v-if="approvingId !== doc.id" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg v-else class="animate-spin w-3 h-3" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Approve
                  </button>
                  <button @click="showRejectModal(doc)" :disabled="rejectingId === doc.id"
                    class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1">
                    <svg v-if="rejectingId !== doc.id" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <svg v-else class="animate-spin w-3 h-3" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Reject
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Reject Modal -->
    <Transition name="modal">
      <div v-if="showRejectDocModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4" @click.self="showRejectDocModal = false">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
          
          <div class="px-6 py-5 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">Reject Document</h3>
            <p class="text-sm text-slate-500">Provide a reason for rejection</p>
          </div>

          <div class="px-6 py-5">
            <textarea v-model="rejectionReason" rows="4" placeholder="Enter rejection reason..."
              class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-red-500 transition-all resize-none"></textarea>
          </div>

          <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            <button @click="showRejectDocModal = false" :disabled="rejectLoading"
              class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
              Cancel
            </button>
            <button @click="submitRejection" :disabled="rejectLoading || !rejectionReason.trim()"
              class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-all flex items-center gap-2">
              <svg v-if="rejectLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ rejectLoading ? 'Rejecting...' : 'Confirm Rejection' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue';
import { debounce } from 'lodash';
import documentService from '@/services/documentService';
import { useAuth } from '@/composables/useAuth';
import ColorPicker from '@/components/ColorPicker.vue';
import Swal from 'sweetalert2';

// Import from appUtils
import { 
  getDocuments,
  setDocuments,
  listenForUpdates,
  formatDate,
} from '@/utils/appUtils';

const { userRole } = useAuth();

// Columns
const columns = [
  { label: 'Document Type', field: 'type', sortable: true },
  { label: 'Category', field: 'category', sortable: true },
  { label: 'Approval', field: 'approval_status', sortable: true },
  { label: 'Sort Order', field: 'sort_order', sortable: true },
  { label: 'Status', field: 'is_active', sortable: true },
  { label: 'Created', field: 'created_at', sortable: true },
  { label: 'Actions', field: 'actions', sortable: false },
];


const initialDocuments = getDocuments();
const documents = ref(initialDocuments || []);
const allDocuments = ref(initialDocuments || []);
const documentCategories = ref(['Pleading', 'Letter', 'Evidence', 'Court Issuance', 'Other']);
const pendingApprovals = ref([]);
// Last updated
const lastUpdated = ref(
  initialDocuments?.length ? new Date().toLocaleTimeString() : 'No data'
);

// Loading states - only for background refresh
const isRefreshing = ref(false);
const formLoading = ref(false);
const isAdding = ref(false);
const isLoading = ref(false); // Not used for initial load

// Action loading states
const editingId = ref(null);
const togglingId = ref(null);
const deletingId = ref(null);
const approvingId = ref(null);
const rejectingId = ref(null);
const rejectLoading = ref(false);

// Pagination - SERVER SIDE
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: initialDocuments?.length || 0,
  from: 1,
  to: Math.min(15, initialDocuments?.length || 0)
});

// Filters - sent to server
const filters = reactive({
  search: '',
  category: '',
  approval_status: '',
  is_active: '',
  sort_by: 'sort_order',
  sort_direction: 'asc'
});

// Modals
const showModal = ref(false);
const showPendingModal = ref(false);
const showRejectDocModal = ref(false);
const isEditing = ref(false);
const editingItemId = ref(null);
const documentToReject = ref(null);
const rejectionReason = ref('');

// Form
const form = reactive({
  type: '',
  category: '',
  color: '#94a3b8',
  requires_approval: true,
  sort_order: null,
  is_active: true
});

const errors = reactive({
  type: '',
  category: '',
  color: '',
  sort_order: ''
});

// ========== COMPUTED ==========
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

// ========== HELPER FUNCTIONS ==========
const categoryBadgeClass = (category) => {
  const classes = {
    'Pleading': 'bg-blue-50 text-blue-700 border border-blue-200',
    'Letter': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'Evidence': 'bg-amber-50 text-amber-700 border border-amber-200',
    'Court Issuance': 'bg-red-50 text-red-700 border border-red-200',
    'Other': 'bg-slate-50 text-slate-600 border border-slate-200'
  };
  return classes[category] || 'bg-slate-50 text-slate-600';
};

const approvalStatusClass = (status) => {
  const classes = {
    'pending': 'bg-amber-50 text-amber-700 border border-amber-200',
    'approved': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'rejected': 'bg-red-50 text-red-700 border border-red-200'
  };
  return classes[status] || 'bg-slate-50 text-slate-600';
};

const approvalStatusDot = (status) => {
  const classes = {
    'pending': 'bg-amber-500',
    'approved': 'bg-emerald-500',
    'rejected': 'bg-red-500'
  };
  return classes[status] || 'bg-slate-400';
};

const formatApprovalStatus = (status) => {
  if (!status) return '—';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

// ========== FETCH DOCUMENTS (Background Refresh) ==========
const fetchDocuments = async () => {
  isRefreshing.value = true;
  
  try {
    const params = {
      search: filters.search || undefined,
      category: filters.category || undefined,
      approval_status: filters.approval_status || undefined,
      is_active: filters.is_active || undefined,
      sort_by: filters.sort_by,
      sort_direction: filters.sort_direction,
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    };

    const response = await documentService.getDocuments(params);
    
    if (response.data) {
      documents.value = response.data;
      pagination.value = response.meta;
      lastUpdated.value = new Date().toLocaleTimeString();
      
      // Update cache
      setDocuments(response.data);
    }
    
  } catch (error) {
    console.error('Failed to load documents:', error);
  } finally {
    isRefreshing.value = false;
  }
};

// ========== FETCH PENDING APPROVALS ==========
const fetchPendingApprovals = async () => {
  if (userRole.value !== 'lawyer') return;
  
  try {
    const response = await documentService.getPendingApprovals();
    pendingApprovals.value = response.data || [];
  } catch (error) {
    console.error('Failed to load pending approvals:', error);
  }
};

// ========== FETCH DOCUMENT CATEGORIES ==========
const fetchDocumentCategories = async () => {
  try {
    const response = await documentService.getDocumentCategories();
    documentCategories.value = response.data || ['Pleading', 'Letter', 'Evidence', 'Court Issuance', 'Other'];
  } catch (error) {
    console.error('Failed to load document categories:', error);
  }
};

// ========== INITIALIZE ==========
const initialize = async () => {

  
  if (!initialDocuments?.length) {
    isLoading.value = true; // Show loading if no cache
      await fetchDocumentCategories();
  } else {
    documents.value = initialDocuments.slice(0, pagination.value.per_page);
    pagination.value.total = initialDocuments.length;
    pagination.value.last_page = Math.ceil(initialDocuments.length / pagination.value.per_page);
  }
  
  await fetchDocuments(); // Add await to ensure loading state turns off after fetch
  isLoading.value = false;
  
  if (userRole.value === 'lawyer') {
    await fetchPendingApprovals();
  }
};
// ========== FILTER HANDLERS ==========
const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1;
  fetchDocuments();
}, 500);

const handleFilterChange = () => {
  pagination.value.current_page = 1;
  fetchDocuments();
};

const sortBy = (field) => {
  if (filters.sort_by === field) {
    filters.sort_direction = filters.sort_direction === 'asc' ? 'desc' : 'asc';
  } else {
    filters.sort_by = field;
    filters.sort_direction = 'asc';
  }
  pagination.value.current_page = 1;
  fetchDocuments();
};

// ========== PAGINATION ==========
const previousPage = () => {
  if (pagination.value.current_page > 1) {
    pagination.value.current_page--;
    fetchDocuments();
  }
};

const nextPage = () => {
  if (pagination.value.current_page < pagination.value.last_page) {
    pagination.value.current_page++;
    fetchDocuments();
  }
};

const goToPage = (page) => {
  pagination.value.current_page = page;
  fetchDocuments();
};

// ========== MODAL FUNCTIONS ==========
const resetForm = () => {
  form.type = '';
  form.category = '';
  form.color = '#94a3b8';
  form.requires_approval = true;
  form.sort_order = null;
  form.is_active = true;
  errors.type = '';
  errors.category = '';
  errors.color = '';
  errors.sort_order = '';
};

const clearErrors = () => {
  errors.type = '';
  errors.category = '';
  errors.color = '';
  errors.sort_order = '';
};

const openCreateModal = () => {
  resetForm();
  isEditing.value = false;
  editingItemId.value = null;
  showModal.value = true;
};

const editItem = (item) => {
  resetForm();
  isEditing.value = true;
  editingItemId.value = item.id;
  form.type = item.type;
  form.category = item.category;
  form.color = item.color;
  form.requires_approval = item.requires_approval;
  form.sort_order = item.sort_order;
  form.is_active = item.is_active;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

// ========== SUBMIT FORM ==========
const submitForm = async () => {
  formLoading.value = true;
  clearErrors();

  try {
    const payload = {
      type: form.type,
      category: form.category,
      color: form.color,
      requires_approval: form.requires_approval,
      sort_order: form.sort_order,
      is_active: form.is_active
    };

    if (isEditing.value) {
      editingId.value = editingItemId.value;
      await documentService.updateDocument(editingItemId.value, payload);

      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Document updated successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

    } else {
      isAdding.value = true;
      await documentService.createDocument(payload);

      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Document created successfully',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    }

    closeModal();
    await fetchDocuments();
    
    if (userRole.value === 'lawyer') {
      await fetchPendingApprovals();
    }

  } catch (error) {
    if (error.errors) {
      if (error.errors.type) errors.type = error.errors.type[0];
      if (error.errors.category) errors.category = error.errors.category[0];
      if (error.errors.color) errors.color = error.errors.color[0];
      if (error.errors.sort_order) errors.sort_order = error.errors.sort_order[0];
    }

    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'An error occurred',
      timer: 2000,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } finally {
    formLoading.value = false;
    isAdding.value = false;
    editingId.value = null;
  }
};

// ========== APPROVE DOCUMENT ==========
const approveDocument = async (item) => {
  approvingId.value = item.id;

  try {
    await documentService.approveDocument(item.id);
    
    // Remove from pending approvals
    pendingApprovals.value = pendingApprovals.value.filter(p => p.id !== item.id);
    
    // Refresh current page
    await fetchDocuments();

    Swal.fire({
      icon: 'success',
      title: 'Approved!',
      text: 'Document approved successfully',
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'Failed to approve document',
      timer: 2000,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } finally {
    approvingId.value = null;
  }
};

// ========== REJECT DOCUMENT ==========
const showRejectModal = (item) => {
  documentToReject.value = item;
  rejectionReason.value = '';
  showRejectDocModal.value = true;
};

const submitRejection = async () => {
  if (!rejectionReason.value.trim() || !documentToReject.value) return;

  rejectLoading.value = true;
  rejectingId.value = documentToReject.value.id;

  try {
    await documentService.rejectDocument(documentToReject.value.id, {
      rejection_reason: rejectionReason.value
    });

    // Remove from pending approvals
    pendingApprovals.value = pendingApprovals.value.filter(p => p.id !== documentToReject.value.id);
    
    // Refresh current page
    await fetchDocuments();

    showRejectDocModal.value = false;

    Swal.fire({
      icon: 'success',
      title: 'Rejected',
      text: 'Document rejected',
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'Failed to reject document',
      timer: 2000,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } finally {
    rejectLoading.value = false;
    rejectingId.value = null;
    documentToReject.value = null;
  }
};

// ========== TOGGLE STATUS ==========
const toggleStatus = async (item) => {
  togglingId.value = item.id;

  try {
    await documentService.toggleDocument(item.id);
    
    // Refresh current page
    await fetchDocuments();

    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: `Document ${!item.is_active ? 'activated' : 'deactivated'} successfully`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'Failed to toggle status',
      timer: 2000,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } finally {
    togglingId.value = null;
  }
};

// ========== DELETE ==========
const confirmDelete = async (item) => {
  const result = await Swal.fire({
    title: 'Delete Document?',
    text: `Are you sure you want to delete "${item.type}"?`,
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
      await documentService.deleteDocument(item.id);

      // Remove from pending approvals if present
      pendingApprovals.value = pendingApprovals.value.filter(p => p.id !== item.id);
      
      // Refresh current page
      await fetchDocuments();

      Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'Document deleted successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.message || 'Failed to delete document',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

    } finally {
      deletingId.value = null;
    }
  }
};

// ========== WATCH FILTERS ==========
watch(() => filters.search, () => {
  debouncedSearch();
});

watch(() => filters.category, () => {
  handleFilterChange();
});

watch(() => filters.approval_status, () => {
  handleFilterChange();
});

watch(() => filters.is_active, () => {
  handleFilterChange();
});

// ========== LISTEN FOR UPDATES ==========
const handleDocumentsUpdated = (event) => {
  allDocuments.value = event.detail;
  // Update current page if we're on page 1
  if (pagination.value.current_page === 1) {
    documents.value = event.detail.slice(0, pagination.value.per_page);
  }
};

let cleanup = null;

// ========== LIFECYCLE ==========
onMounted(async () => {
  await initialize();
  
  cleanup = listenForUpdates('documents-updated', handleDocumentsUpdated);
});

onUnmounted(() => {
  if (cleanup) cleanup();
  debouncedSearch.cancel();
});

// ========== HELPER FUNCTIONS FOR TEMPLATE ==========
const formatDateHelper = (date) => {
  return formatDate(date);
};
</script>