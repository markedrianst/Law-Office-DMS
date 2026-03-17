<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="closeModal">

      <div v-if="viewCase" class="relative bg-white w-full max-w-6xl max-h-[92vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100 bg-white">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center shadow-sm">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-xl font-bold text-gray-900 leading-tight">Case Profile</h1>
              <p class="text-xs text-gray-400 font-medium">{{ viewCase.case_code || 'No Code' }} · {{ viewCase.case_no || 'No Number' }}</p>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button @click="closeModal" class="p-2 hover:bg-gray-100 rounded-xl transition text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5 bg-gray-50">

          <!-- Case Information + Folder Status -->
          <div class="grid grid-cols-3 gap-5">

            <!-- Case Information -->
            <div class="col-span-2 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
              <div class="flex items-center gap-2 mb-5">
                <div class="w-1 h-5 bg-blue-600 rounded-full"></div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Case Information</h3>
              </div>
              <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Case Code</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.case_code || '—' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Case Number</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.case_no || '—' }}</p>
                </div>
                <div class="col-span-2">
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Case Title</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.title || '—' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Client</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.client || '—' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Category</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.category || '—' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Assigned Lawyer</p>
                  <p class="text-sm font-bold text-gray-900">Atty. {{ viewCase.lawyer || '—' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Assigned Clerk</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.clerk || '—' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Court / Office</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.court_or_office || '—' }}</p>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Docket Number</p>
                  <p class="text-sm font-bold text-gray-900">{{ viewCase.docket_no || '—' }}</p>
                </div>
              </div>
            </div>

            <!-- Folder Status -->
            <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm flex flex-col">
              <div class="flex items-center gap-2 mb-5">
                <div class="w-1 h-5 bg-emerald-500 rounded-full"></div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Folder Status</h3>
              </div>
              <div class="space-y-4 flex-1">
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Current Status</p>
                  <span :class="statusBadgeClass(viewCase.case_status)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-bold rounded-lg">
                    <span class="w-2 h-2 rounded-full" :class="statusDotClass(viewCase.case_status)"></span>
                    {{ formatStatus(viewCase.case_status) }}
                  </span>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Stage</p>
                  <div>
                    <div class="flex items-center gap-2">
                      <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: viewCase.stage_color || '#64748b' }"></span>
                      <span class="text-sm font-semibold text-gray-700">{{ viewCase.stage || '—' }}</span>
                    </div>
                  </div>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Folder Location</p>
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#1a4972] to-[#2a5a8c] flex items-center justify-center text-xs font-bold text-white shadow-sm">
                      {{ getInitials(lastFolderHandler) }}
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-gray-900">{{ lastFolderHandler }}</p>
                      <p class="text-xs text-gray-400">{{ viewCase.is_out ? 'OUT of office' : 'IN office' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Case Checklist -->
          <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white">
              <div class="flex items-center gap-3">
                <div class="w-1 h-5 bg-violet-500 rounded-full"></div>
                <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wide">Case Checklist</h3>
                <span v-if="checklist.length" class="px-2 py-0.5 text-xs font-bold bg-violet-100 text-violet-700 rounded-full">
                  {{ checklist.length }}
                </span>
              </div>
              <div class="flex items-center gap-3">
                <div v-if="checklist.length" class="flex items-center gap-2">
                  <div class="w-32 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" :style="{ width: `${donePercent}%` }"></div>
                  </div>
                  <span class="text-xs font-bold text-gray-500">{{ donePercent }}%</span>
                </div>
                <button @click="openTaskModal(null, 'add')"
                  class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                  </svg>
                  Add Task
                </button>
              </div>
            </div>

            <!-- Checklist Table -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                  <tr>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400 w-10"></th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400">Task</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400">Document Type</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Due Date</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Assigned Clerk</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="task in paginatedChecklist" :key="task.id" class="hover:bg-blue-50/20 transition-colors group">
                    <td class="px-5 py-3.5">
                      <input type="checkbox" :checked="task.status === 'done'" @change="toggleDone(task)"
                        class="w-4.5 h-4.5 rounded border-2 border-gray-300 text-blue-600 cursor-pointer accent-blue-600"/>
                    </td>
                    <td class="px-5 py-3.5">
                      <p class="text-sm font-semibold" :class="task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-800'">
                        {{ task.task || task.document_type || '--' }}
                      </p>
                    </td>
                    <td class="px-5 py-3.5">
                      <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: task.document_color || '#94a3b8' }"></div>
                        <span class="text-sm text-gray-600">{{ task.document_category || task.document_type || '—' }}</span>
                      </div>
                    </td>
                    <td class="px-5 py-3.5">
                      <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full whitespace-nowrap" :class="taskStatusClass(task.status)">
                        <span class="w-1.5 h-1.5 rounded-full" :class="taskStatusDot(task.status)"></span>
                        {{ taskStatusLabel(task.status) }}
                      </span>
                    </td>
                    <td class="px-5 py-3.5">
                      <div class="flex items-center gap-1.5">
                        <span class="text-sm text-gray-600 whitespace-nowrap">{{ formatDate(task.due_date) }}</span>
                        <span v-if="isOverdue(task.due_date) && task.status !== 'done'"
                          class="px-1.5 py-0.5 text-[9px] font-bold bg-red-100 text-red-600 rounded-full uppercase">Overdue</span>
                      </div>
                    </td>
                    <td class="px-5 py-3.5">
                      <div v-if="task.assigned_to" class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white bg-gradient-to-br from-[#1a4972] to-[#2a5a8c] shadow-sm">
                          {{ getInitials(task.assigned_to) }}
                        </div>
                        <span class="text-sm text-gray-700 whitespace-nowrap font-medium">{{ task.assigned_to }}</span>
                      </div>
                      <span v-else class="text-sm text-gray-300 italic">—</span>
                    </td>
                    <td class="px-5 py-3.5">
                      <button v-if="task.status !== 'done'" @click="openTaskModal(task, 'edit')"
                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-blue-600 hover:bg-blue-50 border border-blue-200 transition">
                        Edit
                      </button>
                      <button v-else @click="openTaskModal(task, 'view')"
                        class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-gray-500 hover:bg-gray-100 border border-gray-200 transition">
                        View
                      </button>
                    </td>
                  </tr>
                  <tr v-if="checklist.length === 0">
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                      No tasks added yet. Click "Add Task" to create one.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Checklist Pagination -->
            <div v-if="checklist.length > 0" class="flex items-center justify-between px-5 py-3 border-t border-gray-100 bg-gray-50/50">
              <p class="text-xs text-gray-400">
                Showing {{ (checklistPage - 1) * PAGE_SIZE + 1 }}–{{ Math.min(checklistPage * PAGE_SIZE, checklist.length) }} of {{ checklist.length }}
              </p>
              <div class="flex items-center gap-1">
                <button @click="checklistPage--" :disabled="checklistPage === 1"
                  class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button v-for="p in checklistTotalPages" :key="p" @click="checklistPage = p"
                  class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-semibold transition"
                  :class="checklistPage === p ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'">
                  {{ p }}
                </button>
                <button @click="checklistPage++" :disabled="checklistPage === checklistTotalPages"
                  class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex border-b border-gray-100 px-2 pt-1">
              <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                class="px-5 py-3.5 text-sm font-semibold border-b-2 transition -mb-px"
                :class="activeTab === tab ? 'text-blue-600 border-blue-600' : 'text-gray-400 border-transparent hover:text-gray-700 hover:border-gray-300'">
                {{ tab }}
              </button>
            </div>

            <!-- Folder Tracker -->
            <div v-if="activeTab === 'Folder Tracker'" class="p-6">
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-bold text-gray-800">Folder Movement History</h4>
                <div class="flex gap-2">
                  <button @click="openFolderModal('OUT')" 
                    :disabled="viewCase.is_out || hasPendingFolderOut"
                    class="px-4 py-2 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2"
                    :class="(viewCase.is_out || hasPendingFolderOut) ? 'bg-gray-400 cursor-not-allowed' : 'bg-orange-500 hover:bg-orange-600'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Release (OUT)
                  </button>
                  
                  <button @click="openFolderModal('IN')" 
                    :disabled="!viewCase.is_out || hasPendingFolderIn"
                    class="px-4 py-2 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2"
                    :class="(!viewCase.is_out || hasPendingFolderIn) ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Receive (IN)
                  </button>
                </div>
              </div>

              <!-- Validation messages -->
              <div v-if="viewCase.is_out && !hasPendingFolderIn" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-700">
                <span class="font-semibold">Note:</span> Folder is currently OUT. You must receive it (IN) before releasing again.
              </div>
              <div v-else-if="!viewCase.is_out && !hasPendingFolderOut" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-sm text-emerald-700">
                <span class="font-semibold">Note:</span> Folder is currently IN. You can release it (OUT).
              </div>
              <div v-if="hasPendingFolderOut" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                <span class="font-semibold">Note:</span> There is already a pending OUT request awaiting approval.
              </div>
              <div v-if="hasPendingFolderIn" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                <span class="font-semibold">Note:</span> There is already a pending IN request awaiting approval.
              </div>

              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 border-y border-gray-100">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Date</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Type</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">From / To</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Purpose</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Handled By</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-50">
                    <tr v-for="record in paginatedFolderMovements" :key="record.id" class="hover:bg-gray-50/60 transition">
                      <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(record.date) }}</td>
                      <td class="px-4 py-3">
                        <span :class="record.type === 'OUT' ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700'" 
                          class="inline-block px-2.5 py-0.5 text-xs font-bold rounded">
                          {{ record.type }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-700">{{ record.from_to || '—' }}</td>
                      <td class="px-4 py-3 text-sm text-gray-700">{{ record.purpose || '—' }}</td>
                      <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ record.handled_by || '—' }}</td>
                      <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border"
                          :class="approvalStatusClass(record.approval_status)">
                          <span v-if="record.approval_status === 'PENDING'" class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                          {{ record.approval_status }}
                        </span>
                      </td>
                    </tr>
                    <tr v-if="folderMovements.length === 0">
                      <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">
                        No folder movements recorded
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <!-- Folder Tracker Pagination -->
              <div v-if="folderMovements.length > 0" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50/50 mt-2 rounded-b-xl">
                <p class="text-xs text-gray-400">
                  Showing {{ (folderPage - 1) * PAGE_SIZE + 1 }}–{{ Math.min(folderPage * PAGE_SIZE, folderMovements.length) }} of {{ folderMovements.length }}
                </p>
                <div class="flex items-center gap-1">
                  <button @click="folderPage--" :disabled="folderPage === 1"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                  </button>
                  <button v-for="p in folderTotalPages" :key="p" @click="folderPage = p"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-semibold transition"
                    :class="folderPage === p ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'">
                    {{ p }}
                  </button>
                  <button @click="folderPage++" :disabled="folderPage === folderTotalPages"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Checklist Tracker -->
            <div v-if="activeTab === 'Checklist Tracker'" class="p-6">
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-bold text-gray-800">Checklist Movement History</h4>
                <div class="flex gap-2">
                  <button @click="openChecklistTrackerModal('OUT')" 
                    :disabled="viewCase.is_out || hasPendingChecklistOut"
                    class="px-4 py-2 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2"
                    :class="(viewCase.is_out || hasPendingChecklistOut) ? 'bg-gray-400 cursor-not-allowed' : 'bg-orange-500 hover:bg-orange-600'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Release (OUT)
                  </button>
                  
                  <button @click="openChecklistTrackerModal('IN')" 
                    :disabled="!hasOutItems || hasPendingChecklistIn || viewCase.is_out"
                    class="px-4 py-2 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2"
                    :class="(!hasOutItems || hasPendingChecklistIn || viewCase.is_out) ? 'bg-gray-400 cursor-not-allowed' : 'bg-emerald-600 hover:bg-emerald-700'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Receive (IN)
                  </button>
                </div>
              </div>

              <!-- Validation messages -->
              <div v-if="viewCase.is_out" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-sm text-orange-700">
                <span class="font-semibold">Note:</span> Case folder is OUT. You must receive it back before managing checklist movements.
              </div>
              <div v-else-if="hasPendingChecklistOut" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                <span class="font-semibold">Note:</span> There is already a pending OUT request awaiting approval.
              </div>
              <div v-else-if="hasPendingChecklistIn" class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                <span class="font-semibold">Note:</span> There is already a pending IN request awaiting approval.
              </div>
              <div v-else-if="!hasOutItems" class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-700">
                <span class="font-semibold">Note:</span> No checklist items are currently OUT. Release items first before receiving.
              </div>

              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50 border-y border-gray-100">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Date</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Type</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Document / Task</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">From / To</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Purpose</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Handled By</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-50">
                    <tr v-for="record in paginatedChecklistMovements" :key="record.id" class="hover:bg-gray-50/60 transition">
                      <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(record.date) }}</td>
                      <td class="px-4 py-3">
                        <span :class="record.type === 'OUT' ? 'bg-orange-100 text-orange-700' : 'bg-emerald-100 text-emerald-700'" 
                          class="inline-block px-2.5 py-0.5 text-xs font-bold rounded">
                          {{ record.type }}
                        </span>
                      </td>
                      <td class="px-4 py-3 text-sm text-gray-700 font-medium">{{ record.task_name || record.checklist?.task || 'All / General' }}</td>
                      <td class="px-4 py-3 text-sm text-gray-700">{{ record.from_to || '—' }}</td>
                      <td class="px-4 py-3 text-sm text-gray-700">{{ record.purpose || '—' }}</td>
                      <td class="px-4 py-3 text-sm font-semibold text-gray-800">{{ record.handled_by || '—' }}</td>
                      <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full border"
                          :class="approvalStatusClass(record.approval_status)">
                          <span v-if="record.approval_status === 'PENDING'" class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                          {{ record.approval_status }}
                        </span>
                      </td>
                    </tr>
                    <tr v-if="checklistMovements.length === 0">
                      <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                        No checklist movements recorded
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <!-- Checklist Tracker Pagination -->
              <div v-if="checklistMovements.length > 0" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50/50 mt-2 rounded-b-xl">
                <p class="text-xs text-gray-400">
                  Showing {{ (checklistTrackerPage - 1) * PAGE_SIZE + 1 }}–{{ Math.min(checklistTrackerPage * PAGE_SIZE, checklistMovements.length) }} of {{ checklistMovements.length }}
                </p>
                <div class="flex items-center gap-1">
                  <button @click="checklistTrackerPage--" :disabled="checklistTrackerPage === 1"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                  </button>
                  <button v-for="p in checklistTrackerTotalPages" :key="p" @click="checklistTrackerPage = p"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-semibold transition"
                    :class="checklistTrackerPage === p ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'">
                    {{ p }}
                  </button>
                  <button @click="checklistTrackerPage++" :disabled="checklistTrackerPage === checklistTrackerTotalPages"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </button>
                </div>
              </div>
            </div>

            <!-- Activity Logs -->
            <div v-if="activeTab === 'Activity Logs'" class="p-6">
              <h4 class="text-sm font-bold text-gray-800 mb-4">Activity History</h4>
              <div class="space-y-3">
                <div v-for="log in paginatedActivityLogs" :key="log.id" class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                  <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                  <div class="flex-1">
                    <p class="text-sm text-gray-700">
                      <span class="font-semibold">{{ log.user }}</span>
                      {{ log.action }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ formatDateTime(log.created_at) }}</p>
                  </div>
                </div>
                <div v-if="activityLogs.length === 0" class="text-center py-8 text-sm text-gray-400">
                  No activity logs available
                </div>
              </div>
              
              <!-- Activity Logs Pagination -->
              <div v-if="activityLogs.length > 0" class="flex items-center justify-between pt-4 mt-2 border-t border-gray-100">
                <p class="text-xs text-gray-400">
                  Showing {{ (activityPage - 1) * PAGE_SIZE + 1 }}–{{ Math.min(activityPage * PAGE_SIZE, activityLogs.length) }} of {{ activityLogs.length }}
                </p>
                <div class="flex items-center gap-1">
                  <button @click="activityPage--" :disabled="activityPage === 1"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                  </button>
                  <button v-for="p in activityTotalPages" :key="p" @click="activityPage = p"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-xs font-semibold transition"
                    :class="activityPage === p ? 'bg-blue-600 text-white' : 'text-gray-500 hover:bg-gray-100'">
                    {{ p }}
                  </button>
                  <button @click="activityPage++" :disabled="activityPage === activityTotalPages"
                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Task Modal -->
  <CaseTaskModal
    :show="taskModal.show"
    :mode="taskModal.mode"
    :task="taskModal.task"
    :clerks="clerks"
    @close="taskModal.show = false"
    @save="onTaskSave"
    @switch-to-edit="taskModal.mode = 'edit'"
  />

  <!-- Folder Movement Modal -->
  <Transition name="modal">
    <div v-if="folderModal.show" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="folderModal.show = false">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div :class="folderModal.type === 'OUT' ? 'bg-orange-500' : 'bg-emerald-600'" class="w-8 h-8 rounded-xl flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="folderModal.type === 'OUT'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
            </div>
            <div>
              <h2 class="text-base font-bold text-gray-900">{{ folderModal.type === 'OUT' ? 'Release Folder' : 'Receive Folder' }}</h2>
              <p class="text-xs text-gray-400">Record folder movement</p>
            </div>
          </div>
          <button @click="folderModal.show = false" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date</label>
            <input :value="folderModal.form.date" type="text" disabled class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-gray-100 text-gray-700 cursor-not-allowed"/>
          </div>
          
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From / To <span class="text-red-400">*</span></label>
            <div class="relative" ref="fromToDropdownRef">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input
                v-model="fromToSearch"
                @focus="fromToDropdownOpen = true"
                @input="fromToDropdownOpen = true"
                type="text"
                placeholder="Search for recipient/sender..."
                class="w-full pl-9 pr-8 py-2.5 text-sm border border-gray-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                :class="folderModal.form.from_to ? 'border-[#1a4972] font-medium text-slate-800' : 'border-gray-200 text-slate-500'" />
              
              <button v-if="fromToSearch || folderModal.form.from_to" type="button" @click.prevent="clearFromTo"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>

              <Transition name="dropdown">
                <div v-if="fromToDropdownOpen"
                  class="absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                  <div v-if="filteredUsers.length > 0" class="max-h-44 overflow-y-auto">
                    <div v-for="user in filteredUsers" :key="user.id"
                      @mousedown.prevent="selectFromTo(user)"
                      class="flex items-center gap-2.5 px-3.5 py-2.5 cursor-pointer hover:bg-blue-50/70 transition-colors"
                      :class="{ 'bg-blue-50/60': folderModal.form.from_to === user.full_name }">
                      <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white bg-[#1a4972]">
                        {{ getInitials(user.full_name) }}
                      </div>
                      <span class="text-sm text-slate-700 flex-1">{{ user.full_name }}</span>
                      <span class="text-xs text-slate-400">{{ user.role }}</span>
                      <svg v-if="folderModal.form.from_to === user.full_name"
                        class="w-3.5 h-3.5 flex-shrink-0 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                      </svg>
                    </div>
                  </div>
                  <div v-else class="px-4 py-4 text-center">
                    <p class="text-xs text-slate-500">No users match "<span class="font-medium">{{ fromToSearch }}</span>"</p>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
          
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Purpose / Remarks</label>
            <input v-model="folderModal.form.purpose" type="text" placeholder="e.g. For Review, For Submission…" 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>
          
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Handled By</label>
            <input v-model="folderModal.form.handled_by" type="text" disabled
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-gray-100 text-gray-700 cursor-not-allowed"/>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
          <button @click="folderModal.show = false" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl border border-gray-200 transition">Cancel</button>
          <button @click="submitFolderMovement" 
            :disabled="!folderModal.form.from_to"
            :class="[
              folderModal.type === 'OUT' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-emerald-600 hover:bg-emerald-700',
              !folderModal.form.from_to ? 'opacity-50 cursor-not-allowed' : ''
            ]" 
            class="px-5 py-2 text-sm font-bold text-white rounded-xl transition shadow-sm active:scale-95">
            Confirm {{ folderModal.type === 'OUT' ? 'Release' : 'Receive' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- Checklist Tracker Modal -->
  <Transition name="modal">
    <div v-if="checklistTrackerModal.show" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="checklistTrackerModal.show = false">
      <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <div class="flex items-center gap-3">
            <div :class="checklistTrackerModal.type === 'OUT' ? 'bg-orange-500' : 'bg-emerald-600'" class="w-8 h-8 rounded-xl flex items-center justify-center shadow-sm">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path v-if="checklistTrackerModal.type === 'OUT'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
            </div>
            <div>
              <h2 class="text-base font-bold text-gray-900">{{ checklistTrackerModal.type === 'OUT' ? 'Release Checklist' : 'Receive Checklist' }}</h2>
              <p class="text-xs text-gray-400">Record checklist movement</p>
            </div>
          </div>
          <button @click="checklistTrackerModal.show = false" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date</label>
            <input :value="checklistTrackerModal.form.date" type="text" disabled
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-gray-100 text-gray-700 cursor-not-allowed"/>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Document / Task</label>
            <select v-model="checklistTrackerModal.form.checklist_id" 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 bg-white">
              <option value="">All / General</option>
              <option v-for="task in checklist" :key="task.id" :value="task.id" 
                :disabled="checklistTrackerModal.type === 'OUT' ? task.is_out : !task.is_out">
                {{ task.task || task.document_type }} ({{ task.is_out ? 'OUT' : 'IN' }})
              </option>
            </select>
            <p v-if="checklistTrackerModal.type === 'OUT'" class="text-xs text-amber-600 mt-1">
              Only IN items can be released
            </p>
            <p v-else class="text-xs text-emerald-600 mt-1">
              Only OUT items can be received
            </p>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From / To <span class="text-red-400">*</span></label>
            <div class="relative" ref="ctFromToDropdownRef">
              <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input
                v-model="ctFromToSearch"
                @focus="ctFromToDropdownOpen = true"
                @input="ctFromToDropdownOpen = true"
                type="text"
                placeholder="Search for recipient/sender..."
                class="w-full pl-9 pr-8 py-2.5 text-sm border border-gray-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                :class="checklistTrackerModal.form.from_to ? 'border-[#1a4972] font-medium text-slate-800' : 'border-gray-200 text-slate-500'" />
              
              <button v-if="ctFromToSearch || checklistTrackerModal.form.from_to" type="button" @click.prevent="clearCtFromTo"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>

              <Transition name="dropdown">
                <div v-if="ctFromToDropdownOpen"
                  class="absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                  <div v-if="filteredUsers.length > 0" class="max-h-44 overflow-y-auto">
                    <div v-for="user in filteredUsers" :key="user.id"
                      @mousedown.prevent="selectCtFromTo(user)"
                      class="flex items-center gap-2.5 px-3.5 py-2.5 cursor-pointer hover:bg-blue-50/70 transition-colors"
                      :class="{ 'bg-blue-50/60': checklistTrackerModal.form.from_to === user.full_name }">
                      <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white bg-[#1a4972]">
                        {{ getInitials(user.full_name) }}
                      </div>
                      <span class="text-sm text-slate-700 flex-1">{{ user.full_name }}</span>
                      <span class="text-xs text-slate-400">{{ user.role }}</span>
                      <svg v-if="checklistTrackerModal.form.from_to === user.full_name"
                        class="w-3.5 h-3.5 flex-shrink-0 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                      </svg>
                    </div>
                  </div>
                  <div v-else class="px-4 py-4 text-center">
                    <p class="text-xs text-slate-500">No users match "<span class="font-medium">{{ ctFromToSearch }}</span>"</p>
                  </div>
                </div>
              </Transition>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Purpose / Remarks</label>
            <input v-model="checklistTrackerModal.form.purpose" type="text" placeholder="e.g. For Review, For Submission…" 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Handled By</label>
            <input v-model="checklistTrackerModal.form.handled_by" type="text" disabled
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm bg-gray-100 text-gray-700 cursor-not-allowed"/>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
          <button @click="checklistTrackerModal.show = false" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl border border-gray-200 transition">Cancel</button>
          <button @click="submitChecklistMovement" 
            :disabled="!checklistTrackerModal.form.from_to"
            :class="[
              checklistTrackerModal.type === 'OUT' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-emerald-600 hover:bg-emerald-700',
              !checklistTrackerModal.form.from_to ? 'opacity-50 cursor-not-allowed' : ''
            ]" 
            class="px-5 py-2 text-sm font-bold text-white rounded-xl transition shadow-sm active:scale-95">
            Confirm {{ checklistTrackerModal.type === 'OUT' ? 'Release' : 'Receive' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>
<script setup>
import { ref, computed, reactive, watch, onMounted, onBeforeUnmount } from 'vue';
import CaseTaskModal from './CaseTaskModal.vue';
import { useAuth } from '@/composables/useAuth';
import caseService from '@/services/caseService';
import Swal from 'sweetalert2';

const props = defineProps({
  show: { type: Boolean, default: false },
  caseData: { type: Object, default: null },
  stages: { type: Array, default: () => [] },
  clerks: { type: Array, default: () => [] },
  allUsers: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'refresh']);

const { user } = useAuth();

// ========== STATE ==========
const viewCase = ref(null);
const checklist = ref([]);
const folderMovements = ref([]);
const checklistMovements = ref([]);
const activityLogs = ref([]);
const loading = ref(false);

// Pagination
const PAGE_SIZE = 5;
const checklistPage = ref(1);
const folderPage = ref(1);
const checklistTrackerPage = ref(1);
const activityPage = ref(1);

// Tabs
const tabs = ['Folder Tracker', 'Checklist Tracker', 'Activity Logs'];
const activeTab = ref('Folder Tracker');

// Modals
const taskModal = reactive({ show: false, mode: 'add', task: null });
const folderModal = reactive({ 
  show: false, 
  type: 'OUT', 
  form: { date: '', from_to: '', purpose: '', handled_by: '' } 
});
const checklistTrackerModal = reactive({ 
  show: false, 
  type: 'OUT', 
  form: { date: '', checklist_id: '', from_to: '', purpose: '', handled_by: '' } 
});

// Search states
const fromToSearch = ref('');
const fromToDropdownOpen = ref(false);
const fromToDropdownRef = ref(null);
const ctFromToSearch = ref('');
const ctFromToDropdownOpen = ref(false);
const ctFromToDropdownRef = ref(null);

// ========== SMART POLLING ==========
let poller = null;

const refreshMovements = async () => {
  if (!viewCase.value?.id) return;
  try {
    const [folderRes, checklistRes] = await Promise.all([
      caseService.getFolderTracker(viewCase.value.id),
      caseService.getChecklistTracker(viewCase.value.id)
    ]);
    folderMovements.value = folderRes.data || [];
    checklistMovements.value = checklistRes.data || [];
  } catch (error) {
    console.error('Failed to refresh movements:', error);
  }
};

const startPolling = () => {
  stopPolling();
  poller = setInterval(refreshMovements, 10000);
};

const stopPolling = () => {
  if (poller) {
    clearInterval(poller);
    poller = null;
  }
};

// ========== COMPUTED ==========
const lastFolderHandler = computed(() => {
  if (folderMovements.value?.length > 0) {
    const sorted = [...folderMovements.value].sort((a, b) => 
      new Date(b.date || b.created_at) - new Date(a.date || a.created_at)
    );
    const last = sorted[0];
    if (last) {
      return last.from_to || viewCase.value?.clerk || 'Unassigned';
    }
  }
  return viewCase.value?.clerk || 'Office';
});

const donePercent = computed(() => {
  if (!checklist.value.length) return 0;
  const done = checklist.value.filter(t => t.status === 'done').length;
  return Math.round((done / checklist.value.length) * 100);
});

const hasOutItems = computed(() => 
  checklist.value.some(task => task.is_out === true)
);

// Pending checks for folder
const hasPendingFolderOut = computed(() => 
  folderMovements.value.some(m => m.type === 'OUT' && m.approval_status === 'PENDING')
);
const hasPendingFolderIn = computed(() => 
  folderMovements.value.some(m => m.type === 'IN' && m.approval_status === 'PENDING')
);

// Pending checks for checklist
const hasPendingChecklistOut = computed(() => 
  checklistMovements.value.some(m => m.type === 'OUT' && m.approval_status === 'PENDING')
);
const hasPendingChecklistIn = computed(() => 
  checklistMovements.value.some(m => m.type === 'IN' && m.approval_status === 'PENDING')
);

// Pagination
const paginatedChecklist = computed(() => {
  const start = (checklistPage.value - 1) * PAGE_SIZE;
  return checklist.value.slice(start, start + PAGE_SIZE);
});

const paginatedFolderMovements = computed(() => {
  const start = (folderPage.value - 1) * PAGE_SIZE;
  return folderMovements.value.slice(start, start + PAGE_SIZE);
});

const paginatedChecklistMovements = computed(() => {
  const start = (checklistTrackerPage.value - 1) * PAGE_SIZE;
  return checklistMovements.value.slice(start, start + PAGE_SIZE);
});

const paginatedActivityLogs = computed(() => {
  const start = (activityPage.value - 1) * PAGE_SIZE;
  return activityLogs.value.slice(start, start + PAGE_SIZE);
});

const checklistTotalPages = computed(() => 
  Math.max(1, Math.ceil(checklist.value.length / PAGE_SIZE))
);
const folderTotalPages = computed(() => 
  Math.max(1, Math.ceil(folderMovements.value.length / PAGE_SIZE))
);
const checklistTrackerTotalPages = computed(() => 
  Math.max(1, Math.ceil(checklistMovements.value.length / PAGE_SIZE))
);
const activityTotalPages = computed(() => 
  Math.max(1, Math.ceil(activityLogs.value.length / PAGE_SIZE))
);

const filteredUsers = computed(() => {
  const searchTerm = folderModal.show ? fromToSearch.value.toLowerCase() : ctFromToSearch.value.toLowerCase();
  
  let users = [];
  if (props.allUsers?.length) {
    users = props.allUsers;
  } else if (props.clerks?.length) {
    users = props.clerks.map(c => ({ 
      id: c.id, 
      full_name: c.full_name, 
      role: 'clerk' 
    }));
  }
  
  if (viewCase.value?.lawyer && viewCase.value.lawyer !== '—') {
    const exists = users.some(u => u.full_name === viewCase.value.lawyer);
    if (!exists) {
      users.push({
        id: viewCase.value.assigned_lawyer_id || 'lawyer',
        full_name: viewCase.value.lawyer,
        role: 'lawyer'
      });
    }
  }
  
  if (searchTerm && users.length) {
    return users.filter(u => 
      u.full_name?.toLowerCase().includes(searchTerm)
    );
  }
  return users;
});

// ========== WATCHERS ==========
watch(checklist, () => { checklistPage.value = 1; });
watch(folderMovements, () => { folderPage.value = 1; });
watch(checklistMovements, () => { checklistTrackerPage.value = 1; });
watch(activityLogs, () => { activityPage.value = 1; });

watch(activeTab, () => {
  folderPage.value = 1;
  checklistTrackerPage.value = 1;
  activityPage.value = 1;
});

// MAIN FIX: Properly process case data when received
watch(() => props.caseData, async (newVal) => {
  if (newVal) {
    viewCase.value = newVal;
    
    // Process checklists with proper mapping - NO 'task' field
    checklist.value = (newVal.checklists || []).map(item => ({
      id: item.id,
      case_id: item.case_id,
      // Use document_type as the task display
      task: item.document_type || 'Untitled Task',
      document_type_id: item.document_type_id,
      document_type: item.document_type,
      document_category: item.document_category,
      document_color: item.document_color || '#94a3b8',
      status: item.status || 'todo',
      due_date: item.due_date,
      assigned_clerk_id: item.assigned_clerk_id,
      assigned_to: item.assigned_to,
      notes: item.notes,
      is_out: item.is_out || false,
      completed_at: item.completed_at,
      created_at: item.created_at,
      updated_at: item.updated_at
    }));
    
    folderMovements.value = newVal.folder_movements || [];
    checklistMovements.value = newVal.checklist_movements || [];
    activityLogs.value = newVal.activity_logs || [];
  }
}, { immediate: true });
watch(() => props.show, (newVal) => {
  if (newVal && viewCase.value) {
    refreshMovements();
    startPolling();
  } else {
    stopPolling();
    taskModal.show = false;
    folderModal.show = false;
    checklistTrackerModal.show = false;
    activeTab.value = 'Folder Tracker';
  }
});

// ========== CLICK OUTSIDE ==========
const handleClickOutside = (e) => {
  if (fromToDropdownRef.value && !fromToDropdownRef.value.contains(e.target)) {
    fromToDropdownOpen.value = false;
  }
  if (ctFromToDropdownRef.value && !ctFromToDropdownRef.value.contains(e.target)) {
    ctFromToDropdownOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
  stopPolling();
});

// ========== CLOSE MODAL ==========
const closeModal = () => {
  emit('close');
};

// ========== HELPERS ==========
const getInitials = (name) => {
  if (!name || name === '—') return '?';
  const parts = name.split(' ').filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const formatDate = (date) => {
  if (!date) return '—';
  try {
    return new Date(date).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric'
    });
  } catch {
    return '—';
  }
};

const formatDateTime = (date) => {
  if (!date) return '—';
  try {
    return new Date(date).toLocaleString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  } catch {
    return '—';
  }
};

const formatStatus = (status) => {
  const map = { active: 'Active', closed: 'Closed', archived: 'Archived' };
  return map[status] || status;
};

const statusBadgeClass = (status) => {
  const classes = {
    active: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-gray-100 text-gray-700',
    archived: 'bg-amber-100 text-amber-700'
  };
  return classes[status] || 'bg-gray-100 text-gray-700';
};

const statusDotClass = (status) => {
  const classes = {
    active: 'bg-emerald-500',
    closed: 'bg-gray-500',
    archived: 'bg-amber-500'
  };
  return classes[status] || 'bg-gray-400';
};

const isOverdue = (date) => {
  if (!date) return false;
  try {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const due = new Date(date);
    due.setHours(0, 0, 0, 0);
    return due < today;
  } catch {
    return false;
  }
};

const taskStatusLabel = (status) => {
  const map = { todo: 'To-do', 'in-progress': 'In Progress', done: 'Done' };
  return map[status] || status;
};

const taskStatusClass = (status) => {
  const classes = {
    todo: 'bg-slate-100 text-slate-600',
    'in-progress': 'bg-amber-100 text-amber-700',
    done: 'bg-emerald-100 text-emerald-700'
  };
  return classes[status] || 'bg-slate-100 text-slate-500';
};

const taskStatusDot = (status) => {
  const classes = {
    todo: 'bg-slate-400',
    'in-progress': 'bg-amber-400',
    done: 'bg-emerald-500'
  };
  return classes[status] || 'bg-slate-400';
};

const approvalStatusClass = (status) => {
  const classes = {
    PENDING: 'border-amber-400 text-amber-600',
    APPROVED: 'border-emerald-400 text-emerald-600',
    REJECTED: 'border-rose-400 text-rose-600'
  };
  return classes[status] || 'border-gray-300 text-gray-500';
};

// ========== TASK ACTIONS ==========
const openTaskModal = (task, mode) => {
  taskModal.task = task ? { ...task } : null;
  taskModal.mode = mode;
  taskModal.show = true;
};

const onTaskSave = async ({ mode, data }) => {
  try {
    if (mode === 'add') {
      await caseService.createChecklistTask(viewCase.value.id, data);
    } else {
      await caseService.updateChecklistTask(viewCase.value.id, data.id, data);
    }
    
    taskModal.show = false;
    
    const response = await caseService.getChecklist(viewCase.value.id);
    checklist.value = response.data || [];
    
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: `Task ${mode === 'add' ? 'added' : 'updated'} successfully`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || `Failed to ${mode} task`,
      confirmButtonColor: '#dc2626'
    });
  }
};

const toggleDone = async (task) => {
  const newStatus = task.status === 'done' ? 'todo' : 'done';
  const oldStatus = task.status;
  
  const index = checklist.value.findIndex(t => t.id === task.id);
  if (index !== -1) {
    checklist.value[index].status = newStatus;
  }
  
  try {
    await caseService.updateChecklistTask(viewCase.value.id, task.id, {
      ...task,
      status: newStatus
    });
  } catch (error) {
    if (index !== -1) {
      checklist.value[index].status = oldStatus;
    }
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to update task status',
      confirmButtonColor: '#dc2626'
    });
  }
};

// ========== DROPDOWN METHODS ==========
const selectFromTo = (user) => {
  folderModal.form.from_to = user.full_name;
  fromToSearch.value = user.full_name;
  fromToDropdownOpen.value = false;
};

const clearFromTo = () => {
  folderModal.form.from_to = '';
  fromToSearch.value = '';
  fromToDropdownOpen.value = false;
};

const selectCtFromTo = (user) => {
  checklistTrackerModal.form.from_to = user.full_name;
  ctFromToSearch.value = user.full_name;
  ctFromToDropdownOpen.value = false;
};

const clearCtFromTo = () => {
  checklistTrackerModal.form.from_to = '';
  ctFromToSearch.value = '';
  ctFromToDropdownOpen.value = false;
};

// ========== FOLDER MOVEMENT ==========
const openFolderModal = (type) => {
  if (type === 'OUT' && viewCase.value.is_out) {
    Swal.fire({
      icon: 'warning',
      title: 'Cannot Release',
      text: 'Folder is already OUT. Receive it back first.',
      confirmButtonColor: '#f59e0b'
    });
    return;
  }
  
  if (type === 'IN' && !viewCase.value.is_out) {
    Swal.fire({
      icon: 'warning',
      title: 'Cannot Receive',
      text: 'Folder is already IN. Release it first.',
      confirmButtonColor: '#f59e0b'
    });
    return;
  }
  
  const today = new Date();
  const formattedDate = today.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: '2-digit', 
    day: '2-digit' 
  }).replace(/\//g, ' / ');
  
  folderModal.type = type;
  folderModal.form = {
    date: formattedDate,
    from_to: '',
    purpose: '',
    handled_by: viewCase.value?.clerk || ''
  };
  fromToSearch.value = '';
  folderModal.show = true;
};

const submitFolderMovement = async () => {
  try {
    const payload = {
      type: folderModal.type,
      from_to: folderModal.form.from_to,
      purpose: folderModal.form.purpose,
      handled_by: folderModal.form.handled_by,
      date: new Date().toISOString().split('T')[0]
    };
    
    await caseService.createFolderTrackerEntry(viewCase.value.id, payload);
    
    folderModal.show = false;
    await refreshMovements();
    
    const caseRes = await caseService.getCase(viewCase.value.id);
    viewCase.value = caseRes.data;
    
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: `Folder ${folderModal.type === 'OUT' ? 'released' : 'received'} successfully`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
    
    emit('refresh');
    
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to record folder movement',
      confirmButtonColor: '#dc2626'
    });
  }
};

// ========== CHECKLIST MOVEMENT ==========
const openChecklistTrackerModal = (type) => {
  if (viewCase.value.is_out) {
    Swal.fire({
      icon: 'warning',
      title: 'Case Folder is OUT',
      text: 'You must receive the case folder back before managing checklist movements.',
      confirmButtonColor: '#f59e0b'
    });
    return;
  }
  
  console.log('📋 Current checklist items:', checklist.value.map(t => ({
    id: t.id,
    task: t.task,
    is_out: t.is_out
  })));
  
  if (type === 'OUT') {
    const inItems = checklist.value.filter(task => !task.is_out);
    if (inItems.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Nothing to Release',
        text: 'All checklist items are already OUT.',
        confirmButtonColor: '#f59e0b'
      });
      return;
    }
  } else {
    const outItems = checklist.value.filter(task => task.is_out);
    if (outItems.length === 0) {
      Swal.fire({
        icon: 'warning',
        title: 'Nothing to Receive',
        text: 'No checklist items are currently OUT.',
        confirmButtonColor: '#f59e0b'
      });
      return;
    }
  }
  
  const today = new Date();
  const formattedDate = today.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: '2-digit', 
    day: '2-digit' 
  }).replace(/\//g, ' / ');
  
  checklistTrackerModal.type = type;
  checklistTrackerModal.form = {
    date: formattedDate,
    checklist_id: '',
    from_to: '',
    purpose: '',
    handled_by: viewCase.value?.clerk || ''
  };
  ctFromToSearch.value = '';
  checklistTrackerModal.show = true;
};

const submitChecklistMovement = async () => {
  try {
    const payload = {
      type: checklistTrackerModal.type,
      checklist_id: checklistTrackerModal.form.checklist_id || null,
      from_to: checklistTrackerModal.form.from_to,
      purpose: checklistTrackerModal.form.purpose,
      handled_by: checklistTrackerModal.form.handled_by,
      date: new Date().toISOString().split('T')[0]
    };
    
    await caseService.createChecklistTrackerEntry(viewCase.value.id, payload);
    
    checklistTrackerModal.show = false;
    await refreshMovements();
    
    const checklistRes = await caseService.getChecklist(viewCase.value.id);
    checklist.value = (checklistRes.data || []).map(item => ({
      ...item,
      is_out: item.is_out || false
    }));
    
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: `Checklist movement recorded successfully`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
    
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to record checklist movement',
      confirmButtonColor: '#dc2626'
    });
  }
};
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s cubic-bezier(0.4,0,0.2,1); }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.97) translateY(8px); }

.toast-enter-active, .toast-leave-active { transition: all 0.25s cubic-bezier(0.4,0,0.2,1); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: scale(0.95) translateY(8px); }

.dropdown-enter-active { transition: all 0.15s ease; }
.dropdown-enter-from { opacity: 0; transform: translateY(-6px); }
.dropdown-leave-active { transition: all 0.1s ease; }
.dropdown-leave-to { opacity: 0; }

.overflow-y-auto::-webkit-scrollbar { width: 5px; }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }

@keyframes flash-highlight {
  0%   { background-color: #eff6ff; }
  60%  { background-color: #dbeafe; }
  100% { background-color: transparent; }
}
.animate-flash { animation: flash-highlight 1.5s ease-out forwards; }
</style>