<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="$emit('close')">

      <div v-if="viewCase" class="relative bg-white w-full max-w-6xl max-h-[92vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        <!-- ══ HEADER ══════════════════════════════════════════════════════ -->
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
            <button @click="$emit('edit', viewCase)" 
              class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Edit Case
            </button>
            <button @click="$emit('close')" class="p-2 hover:bg-gray-100 rounded-xl transition text-gray-400 hover:text-gray-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- ══ BODY ════════════════════════════════════════════════════════ -->
        <div class="flex-1 overflow-y-auto px-8 py-6 space-y-5 bg-gray-50">

          <!-- ── Row 1: Case Information + Folder Status ─────────────────── -->
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
                  <div class="relative inline-block">
                    <select
                      :value="viewCase.current_stage_id"
                      @change="onStageChange($event.target.value)"
                      :disabled="stageUpdating"
                      class="appearance-none pl-3 pr-8 py-1.5 text-sm font-semibold text-gray-700 border border-gray-200 rounded-lg bg-white hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-400 cursor-pointer transition disabled:opacity-60 disabled:cursor-wait">
                      <option v-if="!stages.length" :value="viewCase.current_stage_id">{{ viewCase.stage || '—' }}</option>
                      <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
                    </select>
                    <svg v-if="!stageUpdating" class="pointer-events-none absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z"/>
                    </svg>
                    <svg v-else class="animate-spin absolute right-2 top-1/2 -translate-y-1/2 w-4 h-4 text-blue-500" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                  </div>
                </div>
                <div>
                  <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Folder Location</p>
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#1a4972] to-[#2a5a8c] flex items-center justify-center text-xs font-bold text-white shadow-sm">
                      {{ getInitials(viewCase.clerk) }}
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-gray-900">{{ viewCase.clerk || 'Unassigned' }}</p>
                      <p class="text-xs text-gray-400">{{ viewCase.is_out ? 'OUT of office' : 'IN office' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- ── Case Checklist ──────────────────────────────────────────── -->
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
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Due Date</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400 whitespace-nowrap">Assigned Clerk</th>
                    <th class="px-5 py-3 text-left text-[11px] font-bold uppercase tracking-wider text-gray-400">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                  <tr v-for="task in checklist" :key="task.id" class="hover:bg-blue-50/20 transition-colors group">
                    <td class="px-5 py-3.5">
                      <input type="checkbox" :checked="task.status === 'done'" @change="toggleDone(task)"
                        class="w-4.5 h-4.5 rounded border-2 border-gray-300 text-blue-600 cursor-pointer accent-blue-600"/>
                    </td>
                    <td class="px-5 py-3.5">
                      <p class="text-sm font-semibold" :class="task.status === 'done' ? 'line-through text-gray-400' : 'text-gray-800'">
                        {{ task.task || '—' }}
                      </p>
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
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400">
                      No tasks added yet. Click "Add Task" to create one.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ══ TABS ════════════════════════════════════════════════════════ -->
          <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="flex border-b border-gray-100 px-2 pt-1">
              <button v-for="tab in tabs" :key="tab" @click="activeTab = tab"
                class="px-5 py-3.5 text-sm font-semibold border-b-2 transition -mb-px"
                :class="activeTab === tab ? 'text-blue-600 border-blue-600' : 'text-gray-400 border-transparent hover:text-gray-700 hover:border-gray-300'">
                {{ tab }}
              </button>
            </div>

            <!-- ── Folder Tracker ──────────────────────────────────────── -->
            <div v-if="activeTab === 'Folder Tracker'" class="p-6">
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-bold text-gray-800">Folder Movement History</h4>
                <div class="flex gap-2">
                  <button @click="openFolderModal('OUT')" 
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Release (OUT)
                  </button>
                  <button @click="openFolderModal('IN')" 
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Receive (IN)
                  </button>
                </div>
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
                    <tr v-for="record in folderMovements" :key="record.id" class="hover:bg-gray-50/60 transition">
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
            </div>

            <!-- ── Checklist Tracker ───────────────────────────────────── -->
            <div v-if="activeTab === 'Checklist Tracker'" class="p-6">
              <div class="flex justify-between items-center mb-4">
                <h4 class="text-sm font-bold text-gray-800">Checklist Movement History</h4>
                <button @click="openChecklistTrackerModal" 
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  New Movement
                </button>
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
                    <tr v-for="record in checklistMovements" :key="record.id" class="hover:bg-gray-50/60 transition">
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
            </div>

            <!-- ── Activity Logs ───────────────────────────────────────── -->
            <div v-if="activeTab === 'Activity Logs'" class="p-6">
              <h4 class="text-sm font-bold text-gray-800 mb-4">Activity History</h4>
              <div class="space-y-3">
                <div v-for="log in activityLogs" :key="log.id" class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
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
            </div>
          </div>
        </div>
      </div>
    </div>
  </Transition>

  <!-- ══ TASK MODAL ══════════════════════════════════════════════════════════ -->
  <CaseTaskModal
    :show="taskModal.show"
    :mode="taskModal.mode"
    :task="taskModal.task"
    :clerks="clerks"
    @close="taskModal.show = false"
    @save="onTaskSave"
    @switch-to-edit="taskModal.mode = 'edit'"
  />

  <!-- ══ FOLDER MOVEMENT MODAL ══════════════════════════════════════════════ -->
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
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From / To</label>
            <input v-model="folderModal.form.from_to" type="text" placeholder="e.g. Court, Client, etc." 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Purpose / Remarks</label>
            <input v-model="folderModal.form.purpose" type="text" placeholder="e.g. For Review, For Submission…" 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Handled By</label>
            <input v-model="folderModal.form.handled_by" type="text" placeholder="Staff name…" 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
          <button @click="folderModal.show = false" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl border border-gray-200 transition">Cancel</button>
          <button @click="submitFolderMovement" :class="folderModal.type === 'OUT' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-emerald-600 hover:bg-emerald-700'" 
            class="px-5 py-2 text-sm font-bold text-white rounded-xl transition shadow-sm active:scale-95">
            Confirm {{ folderModal.type === 'OUT' ? 'Release' : 'Receive' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- ══ CHECKLIST TRACKER MODAL ════════════════════════════════════════════ -->
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
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Document / Task</label>
            <select v-model="checklistTrackerModal.form.checklist_id" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 bg-white">
              <option value="">All / General</option>
              <option v-for="task in checklist" :key="task.id" :value="task.id">{{ task.task }}</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">From / To</label>
            <input v-model="checklistTrackerModal.form.from_to" type="text" placeholder="e.g. Court, Client, etc." 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Purpose / Remarks</label>
            <input v-model="checklistTrackerModal.form.purpose" type="text" placeholder="e.g. For Review, For Submission…" 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Handled By</label>
            <input v-model="checklistTrackerModal.form.handled_by" type="text" placeholder="Staff name…" 
              class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-blue-300 transition"/>
          </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100 bg-gray-50">
          <button @click="checklistTrackerModal.show = false" class="px-5 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-100 rounded-xl border border-gray-200 transition">Cancel</button>
          <button @click="submitChecklistMovement" :class="checklistTrackerModal.type === 'OUT' ? 'bg-orange-500 hover:bg-orange-600' : 'bg-emerald-600 hover:bg-emerald-700'" 
            class="px-5 py-2 text-sm font-bold text-white rounded-xl transition shadow-sm active:scale-95">
            Confirm {{ checklistTrackerModal.type === 'OUT' ? 'Release' : 'Receive' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, computed, reactive, watch } from 'vue';
import CaseTaskModal from './CaseTaskModal.vue';
import { useAuth } from '@/composables/useAuth';
import caseService from '@/services/caseService';
import Swal from 'sweetalert2';

const props = defineProps({
  show: { type: Boolean, default: false },
  caseData: { type: Object, default: null },
  stages: { type: Array, default: () => [] },
  clerks: { type: Array, default: () => [] }
});

const emit = defineEmits(['close', 'edit', 'refresh']);

const { user } = useAuth();

// Local case data
const viewCase = ref(null);
const checklist = ref([]);
const folderMovements = ref([]);
const checklistMovements = ref([]);
const activityLogs = ref([]);
const stageUpdating = ref(false);

// Tabs
const tabs = ['Folder Tracker', 'Checklist Tracker', 'Activity Logs'];
const activeTab = ref('Folder Tracker');

// Task modal
const taskModal = reactive({
  show: false,
  mode: 'add',
  task: null
});

// Folder modal
const folderModal = reactive({
  show: false,
  type: 'OUT',
  form: {
    from_to: '',
    purpose: '',
    handled_by: ''
  }
});

// Checklist tracker modal
const checklistTrackerModal = reactive({
  show: false,
  type: 'OUT',
  form: {
    checklist_id: '',
    from_to: '',
    purpose: '',
    handled_by: ''
  }
});

// Computed
const donePercent = computed(() => {
  if (!checklist.value.length) return 0;
  const done = checklist.value.filter(t => t.status === 'done').length;
  return Math.round((done / checklist.value.length) * 100);
});

// Watch for caseData changes
watch(() => props.caseData, (newVal) => {
  if (newVal) {
    viewCase.value = newVal;
    checklist.value = newVal.checklists || [];
    folderMovements.value = newVal.folder_movements || [];
    checklistMovements.value = newVal.checklist_movements || [];
    activityLogs.value = newVal.activity_logs || [];
  }
}, { immediate: true });

// Watch for modal close
watch(() => props.show, (newVal) => {
  if (!newVal) {
    taskModal.show = false;
    folderModal.show = false;
    checklistTrackerModal.show = false;
    activeTab.value = 'Folder Tracker';
  }
});

// Helpers
const getInitials = (name) => {
  if (!name || name === '—') return '?';
  const parts = name.split(' ').filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0][0].toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const formatDate = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

const formatDateTime = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatStatus = (status) => {
  const map = {
    active: 'Active',
    closed: 'Closed',
    archived: 'Archived',
    pending: 'Pending'
  };
  return map[status] || status;
};

const statusBadgeClass = (status) => {
  const classes = {
    active: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-gray-100 text-gray-700',
    archived: 'bg-amber-100 text-amber-700',
    pending: 'bg-yellow-100 text-yellow-700'
  };
  return classes[status] || 'bg-gray-100 text-gray-700';
};

const statusDotClass = (status) => {
  const classes = {
    active: 'bg-emerald-500',
    closed: 'bg-gray-500',
    archived: 'bg-amber-500',
    pending: 'bg-yellow-500'
  };
  return classes[status] || 'bg-gray-400';
};

const isOverdue = (date) => {
  if (!date) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(date);
  due.setHours(0, 0, 0, 0);
  return due < today;
};

const taskStatusLabel = (status) => {
  const map = {
    todo: 'To-do',
    'in-progress': 'In Progress',
    done: 'Done'
  };
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

// Stage change
const onStageChange = async (stageId) => {
  if (!viewCase.value) return;
  
  const stage = props.stages.find(s => s.id === stageId);
  if (!stage) return;

  stageUpdating.value = true;
  
  try {
    await caseService.updateStage(viewCase.value.id, { stage_id: stageId });
    
    // Optimistic update
    viewCase.value = {
      ...viewCase.value,
      current_stage_id: stageId,
      stage: stage.name
    };
    
    Swal.fire({
      icon: 'success',
      title: 'Stage Updated',
      text: `Case stage changed to ${stage.name}`,
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
      text: 'Failed to update stage',
      confirmButtonColor: '#dc2626'
    });
  } finally {
    stageUpdating.value = false;
  }
};

// Task functions
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
    
    // Refresh checklist
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
  
  try {
    // Optimistic update
    const index = checklist.value.findIndex(t => t.id === task.id);
    if (index !== -1) {
      checklist.value[index] = { ...task, status: newStatus };
    }
    
    await caseService.updateChecklistTask(viewCase.value.id, task.id, {
      ...task,
      status: newStatus
    });
    
  } catch (error) {
    // Revert on error
    const response = await caseService.getChecklist(viewCase.value.id);
    checklist.value = response.data || [];
    
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: 'Failed to update task status',
      confirmButtonColor: '#dc2626'
    });
  }
};

// Folder movement
const openFolderModal = (type) => {
  folderModal.type = type;
  folderModal.form = {
    from_to: '',
    purpose: '',
    handled_by: user?.full_name || ''
  };
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
    
    // Refresh folder movements
    const response = await caseService.getFolderTracker(viewCase.value.id);
    folderMovements.value = response.data || [];
    
    // Update case is_out status if needed
    if (folderModal.type === 'OUT') {
      viewCase.value.is_out = true;
    } else if (folderModal.type === 'IN') {
      viewCase.value.is_out = false;
    }
    
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: `Folder ${folderModal.type === 'OUT' ? 'released' : 'received'} successfully`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
    
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to record folder movement',
      confirmButtonColor: '#dc2626'
    });
  }
};

// Checklist movement
const openChecklistTrackerModal = () => {
  checklistTrackerModal.type = 'OUT';
  checklistTrackerModal.form = {
    checklist_id: '',
    from_to: '',
    purpose: '',
    handled_by: user?.full_name || ''
  };
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
    
    // Refresh checklist movements
    const response = await caseService.getChecklistTracker(viewCase.value.id);
    checklistMovements.value = response.data || [];
    
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

.overflow-y-auto::-webkit-scrollbar { width: 5px; }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>