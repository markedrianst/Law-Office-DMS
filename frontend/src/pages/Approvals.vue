<template>
  <div class="p-4 md:p-6 bg-slate-50 min-h-screen" style="font-family: 'Inter', sans-serif;">

    <!-- Header -->
    <div class="mb-5">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-blue-600"></div>
        <h1 class="text-xl md:text-2xl font-bold text-slate-900">Approvals Dashboard</h1>
      </div>
      <p class="text-sm text-slate-500 ml-4 pl-3">Review and manage pending requests</p>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 mb-4 space-y-3">
      <!-- Search -->
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <input v-model="filters.search" @input="applyFilters" type="text"
          placeholder="Search by case code, task, clerk name, purpose..."
          class="w-full pl-9 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"/>
      </div>
      <!-- Filter row -->
      <div class="flex flex-wrap items-center gap-2">
        <select v-model="filters.status" @change="applyFilters"
          class="flex-1 min-w-[110px] px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-blue-400 bg-white text-slate-600">
          <option value="ALL">All Status</option>
          <option value="PENDING">⏳ Pending</option>
          <option value="APPROVED">✓ Approved</option>
          <option value="REJECTED">✗ Rejected</option>
        </select>
        <select v-model="filters.type" @change="applyFilters"
          class="flex-1 min-w-[100px] px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-blue-400 bg-white text-slate-600">
          <option value="all">All Types</option>
          <option value="checklist">📋 Checklist</option>
          <option value="folder">📁 Folder</option>
        </select>
        <select v-model="filters.direction" @change="applyFilters"
          class="flex-1 min-w-[100px] px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-blue-400 bg-white text-slate-600">
          <option value="ALL">In & Out</option>
          <option value="OUT">↗ OUT</option>
          <option value="IN">↙ IN</option>
        </select>
        <button v-if="hasActiveFilters" @click="clearFilters"
          class="shrink-0 px-3 py-2.5 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors whitespace-nowrap">
          Clear
        </button>
        <button @click="manualRefresh"
          class="shrink-0 px-3 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors flex items-center gap-1.5 whitespace-nowrap">
          <svg class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="bg-white rounded-2xl shadow-sm border border-slate-100 py-20 flex flex-col items-center">
      <div class="relative mb-5">
        <div class="w-14 h-14 rounded-full border-4 border-blue-100 absolute"></div>
        <div class="w-14 h-14 rounded-full border-4 border-blue-600 border-t-transparent animate-spin"></div>
      </div>
      <p class="text-sm font-semibold text-slate-600 animate-pulse">Loading Approvals…</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="!approvals.length" class="bg-white rounded-2xl shadow-sm border border-slate-100 py-14 flex flex-col items-center">
      <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
        <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <p class="text-sm font-semibold text-slate-500 mb-1">No approvals found</p>
      <p class="text-xs text-slate-400">Try adjusting your filters</p>
    </div>

    <!-- Content -->
    <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

      <!-- ══ MOBILE: Card list (hidden sm+) ══ -->
      <div class="block sm:hidden divide-y divide-slate-100">
        <div v-for="item in approvals" :key="`${item.source}-${item.id}`"
          class="p-4 transition-colors"
          :class="item.approval_status === 'PENDING' ? 'bg-amber-50/40' : ''">

          <!-- Row 1: case code + type + direction + status -->
          <div class="flex items-start justify-between gap-2 mb-2.5">
            <div class="flex flex-wrap items-center gap-1.5">
              <span class="text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg font-mono">
                {{ item.case_code || `Case #${item.case_id}` }}
              </span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg"
                :class="item.source === 'checklist' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'">
                {{ item.source === 'checklist' ? '📋 Checklist' : '📁 Folder' }}
              </span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg"
                :class="item.type === 'OUT' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">
                {{ item.type }}
              </span>
            </div>
            <span class="shrink-0 inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-lg"
              :class="statusClassHelper(item.approval_status)">
              <span v-if="item.approval_status === 'PENDING'" class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
              {{ item.approval_status }}
            </span>
          </div>

          <!-- Row 2: task name + purpose -->
          <p class="text-sm font-semibold text-slate-800 mb-0.5">{{ getTaskDisplay(item) }}</p>
          <p v-if="item.purpose" class="text-xs text-slate-400 mb-2.5">{{ item.purpose }}</p>

          <!-- Row 3: meta grid -->
          <div class="grid grid-cols-2 gap-2 mb-3">
            <div class="bg-slate-50 rounded-xl p-2.5">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">From / To</p>
              <p class="text-xs font-medium text-slate-700">{{ item.from_to || '—' }}</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-2.5">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Recorded By</p>
              <div class="flex items-center gap-1.5">
                <div class="w-4 h-4 rounded-full bg-blue-100 flex items-center justify-center text-[9px] font-bold text-blue-700 shrink-0">
                  {{ getInitialsHelper(item.recorder?.full_name) }}
                </div>
                <p class="text-xs font-medium text-slate-700 truncate">{{ item.recorder?.full_name || '—' }}</p>
              </div>
            </div>
            <div class="bg-slate-50 rounded-xl p-2.5 col-span-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Date</p>
              <p class="text-xs font-medium text-slate-700">{{ formatDateHelper(item.date) }}</p>
            </div>
          </div>

          <!-- Row 4: action buttons -->
          <div class="flex gap-2">
            <template v-if="item.approval_status === 'PENDING'">
              <button @click="openApproveModal(item)"
                class="flex-1 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Approve
              </button>
              <button @click="openRejectModal(item)"
                class="flex-1 py-2 text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Reject
              </button>
            </template>
            <button v-else @click="openActionView(item)"
              class="flex-1 py-2 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-100 rounded-xl transition-all active:scale-95 flex items-center justify-center gap-1.5">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              View Details
            </button>
          </div>
        </div>
      </div>

      <!-- ══ DESKTOP: Table (hidden below sm) ══ -->
      <div class="hidden sm:block overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Case</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Direction</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Details</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">From/To</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Recorded By</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="item in approvals" :key="`${item.source}-${item.id}`"
              class="hover:bg-slate-50 transition-colors"
              :class="{ 'bg-amber-50/30': item.approval_status === 'PENDING' }">
              <td class="px-4 py-3">
                <span class="text-sm font-medium text-blue-700">{{ item.case_code || `Case #${item.case_id}` }}</span>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                  :class="item.source === 'checklist' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'">
                  {{ item.source === 'checklist' ? '📋 Checklist' : '📁 Folder' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                  :class="item.type === 'OUT' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">
                  {{ item.type }}
                </span>
              </td>
              <td class="px-4 py-3 max-w-[200px]">
                <div class="text-sm font-medium text-slate-800 truncate">{{ getTaskDisplay(item) }}</div>
                <div v-if="item.purpose" class="text-xs text-slate-500 truncate">{{ item.purpose }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-slate-600">{{ item.from_to || '—' }}</td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-medium text-blue-700 shrink-0">
                    {{ getInitialsHelper(item.recorder?.full_name) }}
                  </div>
                  <span class="text-sm text-slate-700">{{ item.recorder?.full_name || '—' }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-slate-600 whitespace-nowrap">{{ formatDateHelper(item.date) }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium"
                  :class="statusClassHelper(item.approval_status)">
                  <span v-if="item.approval_status === 'PENDING'" class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span>
                  {{ item.approval_status }}
                </span>
                <div v-if="item.notes" class="text-xs text-slate-500 mt-1 max-w-[150px] truncate" :title="item.notes">
                  📝 {{ item.notes }}
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <template v-if="item.approval_status === 'PENDING'">
                    <button @click="openApproveModal(item)"
                      class="px-3 py-1.5 text-xs font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                      </svg>
                      Approve
                    </button>
                    <button @click="openRejectModal(item)"
                      class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 rounded-lg transition-colors flex items-center gap-1">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                      Reject
                    </button>
                  </template>
                  <button v-else @click="openActionView(item)"
                    class="px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="px-4 sm:px-5 py-3 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-1.5">
        <p class="text-xs text-slate-500">
          Showing <span class="font-semibold text-slate-700">{{ approvals.length }}</span> of
          <span class="font-semibold text-slate-700">{{ stats.total }}</span> movements
        </p>
        <span class="text-xs text-slate-400">Last updated: {{ lastUpdated }}</span>
      </div>
    </div>

    <!-- ========== ACTION VIEW MODAL ========== -->
    <Transition name="modal">
      <div v-if="actionView.show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeActionView">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div class="relative bg-white w-full sm:max-w-3xl max-h-[95dvh] sm:max-h-[90vh] rounded-t-2xl sm:rounded-2xl shadow-xl flex flex-col overflow-hidden">

          <!-- Header -->
          <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100 bg-white flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-base font-bold text-slate-900">Movement Details</h3>
                <p class="text-xs text-slate-500">Complete information about this movement</p>
              </div>
            </div>
            <button @click="closeActionView" class="p-2 hover:bg-slate-100 rounded-xl transition-colors">
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Body -->
          <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 bg-slate-50 space-y-4">
            <div v-if="actionView.item">

              <!-- Case Info -->
              <div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-3">
                  <div class="w-1 h-4 rounded-full bg-blue-600"></div>
                  <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Case Information</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Case Code</p>
                    <p class="text-sm font-bold text-blue-700">{{ actionView.item.case_code || `Case #${actionView.item.case_id}` }}</p>
                  </div>
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Movement Type</p>
                    <div class="flex flex-wrap gap-1.5 mt-1">
                      <span class="text-xs font-bold px-2.5 py-1 rounded-lg"
                        :class="actionView.item.source === 'checklist' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'">
                        {{ actionView.item.source === 'checklist' ? '📋 Checklist' : '📁 Folder' }}
                      </span>
                      <span class="text-xs font-bold px-2.5 py-1 rounded-lg"
                        :class="actionView.item.type === 'OUT' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">
                        {{ actionView.item.type }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Task Details (checklist only) -->
              <div v-if="actionView.item.source === 'checklist'" class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-3">
                  <div class="w-1 h-4 rounded-full bg-indigo-600"></div>
                  <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Task Details</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Task Name</p>
                    <p class="text-sm font-semibold text-slate-800">{{ getTaskDisplay(actionView.item) }}</p>
                  </div>
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Document Type</p>
                    <p class="text-sm text-slate-700">{{ actionView.item.checklist?.document_type || actionView.item.task_name || '—' }}</p>
                  </div>
                  <div v-if="actionView.item.checklist?.status" class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Task Status</p>
                    <span class="text-xs font-bold px-2.5 py-1 rounded-lg"
                      :class="{
                        'bg-slate-100 text-slate-600': actionView.item.checklist.status === 'todo',
                        'bg-amber-100 text-amber-700': actionView.item.checklist.status === 'in-progress',
                        'bg-emerald-100 text-emerald-700': actionView.item.checklist.status === 'done'
                      }">
                      {{ actionView.item.checklist.status === 'todo' ? 'To-do' : actionView.item.checklist.status === 'in-progress' ? 'In Progress' : 'Done' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Movement Details -->
              <div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-3">
                  <div class="w-1 h-4 rounded-full bg-emerald-600"></div>
                  <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Movement Details</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date</p>
                    <p class="text-sm font-semibold text-slate-800">{{ formatDateHelper(actionView.item.date) }}</p>
                  </div>
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">From / To</p>
                    <p class="text-sm text-slate-800">{{ actionView.item.from_to || '—' }}</p>
                  </div>
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Handled By</p>
                    <p class="text-sm text-slate-800">{{ actionView.item.handled_by || '—' }}</p>
                  </div>
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Purpose</p>
                    <p class="text-sm text-slate-700">{{ actionView.item.purpose || '—' }}</p>
                  </div>
                  <div class="sm:col-span-2 bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Notes</p>
                    <p class="text-sm text-slate-700 italic">{{ actionView.item.notes || 'No notes provided' }}</p>
                  </div>
                </div>
              </div>

              <!-- Personnel -->
              <div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-3">
                  <div class="w-1 h-4 rounded-full bg-amber-500"></div>
                  <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Personnel</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Recorded By</p>
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold text-blue-700 shrink-0">
                        {{ getInitialsHelper(actionView.item.recorder?.full_name) }}
                      </div>
                      <p class="text-sm text-slate-800">{{ actionView.item.recorder?.full_name || '—' }}</p>
                    </div>
                  </div>
                  <div class="bg-slate-50 rounded-xl p-3">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Recorded At</p>
                    <p class="text-sm text-slate-800">{{ formatDateTimeHelper(actionView.item.created_at) }}</p>
                  </div>
                  <template v-if="actionView.item.approval_status !== 'PENDING'">
                    <div class="bg-slate-50 rounded-xl p-3">
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">
                        {{ actionView.item.approval_status === 'APPROVED' ? 'Approved By' : 'Rejected By' }}
                      </p>
                      <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-[10px] font-bold text-blue-700 shrink-0">
                          {{ getInitialsHelper(actionView.item.approver?.full_name) }}
                        </div>
                        <p class="text-sm text-slate-800">{{ actionView.item.approver?.full_name || '—' }}</p>
                      </div>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Decision Date</p>
                      <p class="text-sm text-slate-800">{{ formatDateTimeHelper(actionView.item.approved_at) }}</p>
                    </div>
                  </template>
                </div>
              </div>

              <!-- Status Timeline -->
              <div class="bg-white rounded-2xl border border-slate-100 p-4 sm:p-5">
                <div class="flex items-center gap-2 mb-3">
                  <div class="w-1 h-4 rounded-full bg-violet-600"></div>
                  <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Timeline</p>
                </div>
                <div class="space-y-3">
                  <div class="flex items-start gap-3">
                    <div class="relative shrink-0">
                      <div class="w-3 h-3 rounded-full bg-emerald-500 mt-1"></div>
                      <div v-if="actionView.item.approval_status !== 'PENDING'"
                        class="absolute top-4 left-1 w-0.5 h-10 bg-slate-200"></div>
                    </div>
                    <div class="flex-1 bg-slate-50 rounded-xl p-3">
                      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                        <p class="text-sm font-semibold text-slate-800">Movement Recorded</p>
                        <p class="text-xs text-slate-400">{{ formatDateTimeHelper(actionView.item.created_at) }}</p>
                      </div>
                      <p class="text-xs text-slate-500 mt-0.5">by {{ actionView.item.recorder?.full_name || 'System' }}</p>
                    </div>
                  </div>
                  <div v-if="actionView.item.approval_status !== 'PENDING'" class="flex items-start gap-3">
                    <div class="w-3 h-3 rounded-full mt-1 shrink-0"
                      :class="actionView.item.approval_status === 'APPROVED' ? 'bg-emerald-500' : 'bg-red-500'"></div>
                    <div class="flex-1 bg-slate-50 rounded-xl p-3">
                      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-0.5">
                        <p class="text-sm font-semibold text-slate-800">
                          {{ actionView.item.approval_status === 'APPROVED' ? 'Movement Approved' : 'Movement Rejected' }}
                        </p>
                        <p class="text-xs text-slate-400">{{ formatDateTimeHelper(actionView.item.approved_at) }}</p>
                      </div>
                      <p class="text-xs text-slate-500 mt-0.5">by {{ actionView.item.approver?.full_name || 'Unknown' }}</p>
                      <div v-if="actionView.item.notes" class="mt-2 p-2 bg-white rounded-lg border border-slate-100">
                        <p class="text-xs text-slate-600 italic">"{{ actionView.item.notes }}"</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Footer -->
          <div class="px-5 sm:px-6 py-4 border-t border-slate-100 bg-white flex-shrink-0">
            <button @click="closeActionView"
              class="w-full px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors active:scale-95">
              Close
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Approval/Rejection Modal -->
    <div v-if="modal.show" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeModal">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
      <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-xl p-5 sm:p-6">
        <div class="flex items-start gap-4 mb-4">
          <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0"
            :class="modal.action === 'APPROVED' ? 'bg-emerald-100' : 'bg-red-100'">
            <svg class="w-5 h-5" :class="modal.action === 'APPROVED' ? 'text-emerald-600' : 'text-red-600'"
              fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="modal.action === 'APPROVED'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </div>
          <div>
            <h3 class="text-base font-bold text-slate-900 mb-0.5">
              {{ modal.action === 'APPROVED' ? 'Approve Movement' : 'Reject Movement' }}
            </h3>
            <p class="text-sm text-slate-500">
              {{ modal.action === 'APPROVED' ? 'This will mark the movement as approved.' : 'Please provide a reason for rejection.' }}
            </p>
          </div>
        </div>

        <div class="bg-slate-50 rounded-xl p-3 mb-4 text-sm">
          <div class="flex items-center gap-2 mb-2">
            <span class="font-semibold text-slate-600">Case:</span>
            <span class="font-bold text-blue-700">{{ modal.item?.case_code || `Case #${modal.item?.case_id}` }}</span>
          </div>
          <div class="flex items-center gap-2">
            <span class="px-2 py-0.5 rounded-lg text-xs font-bold" :class="modal.item?.source === 'checklist' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700'">
              {{ modal.item?.source === 'checklist' ? 'Checklist' : 'Folder' }}
            </span>
            <span class="px-2 py-0.5 rounded-lg text-xs font-bold" :class="modal.item?.type === 'OUT' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700'">
              {{ modal.item?.type }}
            </span>
          </div>
        </div>

        <div class="mb-5">
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">
            Notes <span v-if="modal.action === 'REJECTED'" class="text-red-500">*</span>
          </label>
          <textarea v-model="modal.notes" rows="3"
            :placeholder="modal.action === 'APPROVED' ? 'Optional approval notes…' : 'Required: Reason for rejection'"
            class="w-full px-3 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
            :class="{ 'border-red-300': modal.action === 'REJECTED' && !modal.notes }"></textarea>
          <p v-if="modal.action === 'REJECTED' && !modal.notes" class="text-xs text-red-500 mt-1">Rejection reason is required</p>
        </div>

        <div class="flex gap-3">
          <button @click="closeModal"
            class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
            Cancel
          </button>
          <button @click="submitDecision"
            :disabled="modal.processing || (modal.action === 'REJECTED' && !modal.notes)"
            class="flex-1 px-4 py-2.5 text-sm font-semibold text-white rounded-xl transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95"
            :class="modal.action === 'APPROVED' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-red-600 hover:bg-red-700'">
            <svg v-if="modal.processing" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ modal.processing ? 'Processing…' : (modal.action === 'APPROVED' ? 'Approve' : 'Reject') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <Transition name="modal">
      <div v-if="toast.show"
        class="fixed bottom-4 right-4 z-50 flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-lg text-sm font-semibold"
        :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'">
        <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0"
          :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'">
          <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </div>
        {{ toast.message }}
      </div>
    </Transition>

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
  listenForUpdates,
  formatDate,
  formatDateTime,
  getInitials
} from '@/utils/appUtils';

// ========== STATE ==========
const initialApprovals = getApprovals();
const initialStats = getApprovalStats();

const approvals = ref(initialApprovals || []);
const stats = ref(initialStats || { total: 0, pending: 0, approved: 0, rejected: 0 });
const loading = ref(false);
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

// ========== ADDED ACTION VIEW STATE ==========
const actionView = reactive({
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

// ========== FETCH APPROVALS ==========
const fetchApprovals = async (showLoading = false) => {
  if (showLoading) loading.value = true;
  isRefreshing.value = true;
  
  try {
    const params = {
      status: filters.status !== 'ALL' ? filters.status : undefined,
      type: filters.type !== 'all' ? filters.type : undefined,
      direction: filters.direction !== 'ALL' ? filters.direction : undefined,
      search: filters.search || undefined
    };
    
    const response = await approvalService.getApprovals(params);
    
    lastUpdated.value = new Date().toLocaleTimeString();
    
  } catch (error) {
    console.error('Failed to load approvals:', error);
    showToast(error.message || 'Failed to load approvals', 'error');
  } finally {
    if (showLoading) loading.value = false;
    isRefreshing.value = false;
  }
};

// ========== INITIALIZE ==========
const initialize = async () => {
  fetchApprovals(false);
};

// ========== FILTER METHODS ==========
const applyFilters = () => {
  fetchApprovals(true);
};

const clearFilters = () => {
  filters.status = 'ALL';
  filters.type = 'all';
  filters.direction = 'ALL';
  filters.search = '';
  fetchApprovals(true);
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

// ========== ADDED ACTION VIEW METHODS ==========
const openActionView = (item) => {
  actionView.item = item;
  actionView.show = true;
};

const closeActionView = () => {
  actionView.show = false;
  actionView.item = null;
};

// ========== SUBMIT DECISION ==========
const submitDecision = async () => {
  
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
   
    showToast(
      `Movement ${modal.action === 'APPROVED' ? 'approved' : 'rejected'} successfully`,
      'success'
    );
    
    closeModal();
    await fetchApprovals(false);
    
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

// ========== TOAST METHODS ==========
const showToast = (message, type = 'success') => {
  toast.show = true;
  toast.message = message;
  toast.type = type;
  
  setTimeout(() => {
    toast.show = false;
  }, 3000);
};

// ========== HELPER FUNCTIONS ==========
const getTaskDisplay = (item) => {
  if (item.source === 'checklist') {
    return item.task_name || item.checklist?.task || 'Checklist Item';
  }
  return 'Folder Movement';
};

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

// ========== MANUAL REFRESH ==========
const manualRefresh = async () => {
  isRefreshing.value = true;
  await fetchApprovals(true);
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

// ========== LISTEN FOR UPDATES ==========
const handleApprovalsUpdated = (event) => {
  approvals.value = event.detail;
};

const handleStatsUpdated = (event) => {
  stats.value = event.detail;
};

let cleanupApprovals = null;
let cleanupStats = null;

// ========== LIFECYCLE ==========
onMounted(async () => {
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

.modal-enter-active, .modal-leave-active {
  transition: all 0.25s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>