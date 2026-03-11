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
            placeholder="Search documents..."
            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:bg-white transition-all" />
        </div>

        <select v-model="categoryFilter" @change="handleFilterChange"
          class="px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100">
          <option value="">All Categories</option>
          <option v-for="cat in documentCategories" :key="cat" :value="cat">{{ cat }}</option>
        </select>

        <select v-model="approvalFilter" @change="handleFilterChange"
          class="px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100">
          <option value="">All Approval Status</option>
          <option value="pending">Pending Approval</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>

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
          {{ isAdding ? 'Adding...' : 'Add Document' }}
        </button>
      </div>
    </div>

    <!-- Documents Table -->
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
          <tr v-for="(item, index) in documents" :key="item.id" 
            class="transition-all duration-300 hover:bg-blue-50/30 group"
            :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.03}s both` }">
            
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
            <td class="px-5 py-4 text-sm text-slate-400">{{ formatDate(item.created_at) }}</td>

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
          <tr v-if="documents.length === 0">
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
            <p v-if="form.requires_approval" class="text-xs text-amber-600 mt-1">
              ⚠ This document will need lawyer approval before it can be used in cases
            </p>

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
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { debounce } from 'lodash';
import { documentService } from '@/services/masterData';
import { useAuth } from '@/composables/useAuth';
import ColorPicker from '@/components/ColorPicker.vue';
import Swal from 'sweetalert2';

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

// State
const documents = ref([]);
const documentCategories = ref(['Pleading', 'Letter', 'Evidence', 'Court Issuance', 'Other']);
const pendingApprovals = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
});

const searchQuery = ref('');
const categoryFilter = ref('');
const approvalFilter = ref('');
const statusFilter = ref('');
const sortField = ref('sort_order');
const sortDirection = ref('asc');
const currentPage = ref(1);

// Loading states
const isAdding = ref(false);
const editingId = ref(null);
const togglingId = ref(null);
const deletingId = ref(null);
const approvingId = ref(null);
const rejectingId = ref(null);
const formLoading = ref(false);
const rejectLoading = ref(false);

// Modal
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
  requires_approval: false,
  sort_order: null,
  is_active: true
});

const errors = reactive({
  type: '',
  category: '',
  color: '',
  sort_order: ''
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

// Helper functions
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

// Load data
const loadDocuments = async () => {
  try {
    const params = {
      search: searchQuery.value || undefined,
      category: categoryFilter.value || undefined,
      approval_status: approvalFilter.value || undefined,
      is_active: statusFilter.value || undefined,
      sort_by: sortField.value,
      sort_direction: sortDirection.value,
      page: currentPage.value,
      per_page: pagination.value.per_page
    };

    const response = await documentService.getDocuments(params);
    documents.value = response.data || [];
    pagination.value = response.meta || {
      current_page: currentPage.value,
      last_page: 1,
      per_page: 15,
      total: documents.value.length,
      from: 1,
      to: documents.value.length
    };
  } catch (error) {
    console.error('Failed to load documents:', error);
    documents.value = [];
  }
};

const loadPendingApprovals = async () => {
  if (userRole.value !== 'lawyer') return;
  
  try {
    const response = await documentService.getPendingApprovals();
    pendingApprovals.value = response.data || [];
  } catch (error) {
    console.error('Failed to load pending approvals:', error);
    pendingApprovals.value = [];
  }
};

const loadDocumentCategories = async () => {
  try {
    const response = await documentService.getDocumentCategories();
    documentCategories.value = response.data || ['Pleading', 'Letter', 'Evidence', 'Court Issuance', 'Other'];
  } catch (error) {
    console.error('Failed to load document categories:', error);
  }
};

// Filters
const debouncedSearch = debounce(() => {
  currentPage.value = 1;
  loadDocuments();
}, 500);

const handleFilterChange = () => {
  currentPage.value = 1;
  loadDocuments();
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  loadDocuments();
};

// Pagination
const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    loadDocuments();
  }
};

const nextPage = () => {
  if (currentPage.value < pagination.value.last_page) {
    currentPage.value++;
    loadDocuments();
  }
};

const goToPage = (page) => {
  currentPage.value = page;
  loadDocuments();
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
  form.type = '';
  form.category = '';
  form.color = '#94a3b8';
  form.requires_approval = false;
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

const openCreateModal = async () => {
  resetForm();
  isEditing.value = false;
  editingItemId.value = null;
  
  // Get the next available sort order (excluding "Others")
  try {
    const response = await documentService.getDocuments({ 
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

// Submit form
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
      
      // Optimistic update
      const index = documents.value.findIndex(d => d.id === editingItemId.value);
      if (index !== -1) {
        documents.value[index] = {
          ...documents.value[index],
          ...payload
        };
      }

      await documentService.updateDocument(editingItemId.value, payload);

      await Swal.fire({
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
      const response = await documentService.createDocument(payload);
      
      if (response.data) {
        documents.value.unshift(response.data);
      }

      // Show appropriate message based on role and approval status
      let message = '';
      if (userRole.value === 'lawyer') {
        message = 'Document created successfully (auto-approved)';
      } else {
        message = payload.requires_approval 
          ? 'Document created and pending lawyer approval'
          : 'Document created successfully';
      }

      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    }

    closeModal();
    await loadDocuments();
    if (userRole.value === 'lawyer') {
      await loadPendingApprovals();
    }

  } catch (error) {
    if (error.errors) {
      if (error.errors.type) errors.type = error.errors.type[0] || error.errors.type;
      if (error.errors.category) errors.category = error.errors.category[0] || error.errors.category;
      if (error.errors.color) errors.color = error.errors.color[0] || error.errors.color;
      if (error.errors.sort_order) errors.sort_order = error.errors.sort_order[0] || error.errors.sort_order;
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

// Approve document
const approveDocument = async (item) => {
  approvingId.value = item.id;

  try {
    // Optimistic update
    const index = documents.value.findIndex(d => d.id === item.id);
    if (index !== -1) {
      documents.value[index] = {
        ...documents.value[index],
        approval_status: 'approved'
      };
    }

    // Remove from pending approvals
    pendingApprovals.value = pendingApprovals.value.filter(p => p.id !== item.id);

    await documentService.approveDocument(item.id);

    await Swal.fire({
      icon: 'success',
      title: 'Approved!',
      text: 'Document approved successfully',
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } catch (error) {
    // Revert on error
    await loadDocuments();
    await loadPendingApprovals();

    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'Failed to approve document',
      confirmButtonColor: '#dc2626'
    });

  } finally {
    approvingId.value = null;
  }
};

// Reject document
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
    // Optimistic update
    const index = documents.value.findIndex(d => d.id === documentToReject.value.id);
    if (index !== -1) {
      documents.value[index] = {
        ...documents.value[index],
        approval_status: 'rejected',
        rejection_reason: rejectionReason.value
      };
    }

    // Remove from pending approvals
    pendingApprovals.value = pendingApprovals.value.filter(p => p.id !== documentToReject.value.id);

    await documentService.rejectDocument(documentToReject.value.id, {
      rejection_reason: rejectionReason.value
    });

    showRejectDocModal.value = false;

    await Swal.fire({
      icon: 'success',
      title: 'Rejected',
      text: 'Document rejected',
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } catch (error) {
    // Revert on error
    await loadDocuments();
    await loadPendingApprovals();

    await Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.message || 'Failed to reject document',
      confirmButtonColor: '#dc2626'
    });

  } finally {
    rejectLoading.value = false;
    rejectingId.value = null;
    documentToReject.value = null;
  }
};

// Toggle status
const toggleStatus = async (item) => {
  togglingId.value = item.id;

  try {
    // Optimistic update
    const index = documents.value.findIndex(d => d.id === item.id);
    if (index !== -1) {
      documents.value[index] = {
        ...documents.value[index],
        is_active: !item.is_active
      };
    }

    await documentService.toggleDocument(item.id);

    await Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: `Document ${!item.is_active ? 'activated' : 'deactivated'} successfully`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });

  } catch (error) {
    // Revert on error
    await loadDocuments();

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
      // Optimistic delete
      documents.value = documents.value.filter(d => d.id !== item.id);

      await documentService.deleteDocument(item.id);

      await Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'Document deleted successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });

      await loadDocuments();
      await loadPendingApprovals();

    } catch (error) {
      // Revert on error
      await loadDocuments();

      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.message || 'Failed to delete document',
        confirmButtonColor: '#dc2626'
      });

    } finally {
      deletingId.value = null;
    }
  }
};

// Watch for page changes
watch(currentPage, () => {
  loadDocuments();
});

// Initial load
onMounted(() => {
  loadDocumentCategories();
  loadDocuments();
  loadPendingApprovals();
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