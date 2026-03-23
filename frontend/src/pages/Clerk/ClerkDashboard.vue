<template>
  <div class="space-y-4">

    <!-- ── Row 1: 3 stat tiles ── -->
    <div class="grid grid-cols-3 gap-3">

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
          </svg>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800 leading-none">{{ clerkStats.assigned_cases || 0 }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-1.5 leading-tight">Assigned Cases</p>
      </div>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center mb-3">
          <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
          </svg>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800 leading-none">{{ clerkStats.total_tasks || 0 }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-1.5 leading-tight">Total Tasks</p>
      </div>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-9 h-9 rounded-xl bg-violet-50 flex items-center justify-center mb-3">
          <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v1H3V7z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7z"/>
          </svg>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800 leading-none">{{ clerkStats.pending_folders || 0 }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-1.5 leading-tight">Pending Folders</p>
      </div>
    </div>

    <!-- ── Row 2: Task Status ── -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

      <!-- Pending Tasks -->
      <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-amber-600 uppercase tracking-wider mb-1">Pending Tasks</p>
          <p class="text-3xl font-bold text-amber-900 leading-none">{{ clerkStats.pending_tasks || 0 }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>

      <!-- Due This Week -->
      <div class="bg-red-50 border border-red-100 rounded-2xl p-4 flex items-center justify-between">
        <div>
          <p class="text-[11px] font-semibold text-red-600 uppercase tracking-wider mb-1">Due This Week</p>
          <p class="text-3xl font-bold text-red-900 leading-none">{{ clerkStats.due_tasks || 0 }}</p>
        </div>
        <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>
      </div>

      <!-- Completed -->
      <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4">
        <div class="flex items-center justify-between mb-3">
          <div>
            <p class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider mb-1">Completed</p>
            <p class="text-3xl font-bold text-emerald-900 leading-none">
              {{ clerkStats.completed_tasks || 0 }}
              <span class="text-sm font-normal text-emerald-400">/{{ clerkStats.total_tasks || 0 }}</span>
            </p>
          </div>
          <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="flex-1 h-1.5 bg-emerald-100 rounded-full overflow-hidden">
            <div class="h-full bg-emerald-500 rounded-full transition-all duration-700"
              :style="{ width: `${completionPercentage}%` }"></div>
          </div>
          <span class="text-xs font-bold text-emerald-600 shrink-0">{{ completionPercentage }}%</span>
        </div>
      </div>
    </div>

    <!-- ── Calendar + Hearings ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div><CalendarWidget :stats="hearingStats" :hearings="upcomingHearings"/></div>
      <div class="lg:col-span-2"><UpcomingHearings :hearings="upcomingHearings"/></div>
    </div>

    <!-- ── My Tasks ── -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <div class="w-1 h-4 rounded-full bg-[#1a4972]"></div>
          <h3 class="text-sm font-bold text-slate-700">My Tasks</h3>
        </div>
        <select v-model="taskFilter"
          class="text-xs border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:border-[#1a4972] bg-slate-50 text-slate-600 cursor-pointer">
          <option value="all">All Tasks</option>
          <option value="todo">To Do</option>
          <option value="in-progress">In Progress</option>
          <option value="done">Done</option>
        </select>
      </div>

      <!-- Empty state -->
      <div v-if="!filteredTasks || filteredTasks.length === 0" class="flex flex-col items-center py-12">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
          <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-slate-400">No tasks found</p>
      </div>

      <!-- Task rows -->
      <div class="divide-y divide-slate-50 max-h-[480px] overflow-y-auto">
        <div v-for="task in filteredTasks" :key="task.id"
          class="px-4 sm:px-5 py-4 hover:bg-slate-50/60 transition-colors">

          <!-- Mobile layout -->
          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <!-- Status pill + doc type -->
              <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="getTaskStatusClass(task.status)">
                  {{ formatStatus(task.status) }}
                </span>
                <span v-if="task.document_type" class="text-[11px] text-slate-400">{{ task.document_type }}</span>
              </div>
              <!-- Task name -->
              <p class="text-sm font-semibold text-slate-800 mb-1.5">{{ task.task }}</p>
              <!-- Meta chips -->
              <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                <span v-if="task.case_code"
                  class="text-[11px] font-bold text-[#1a4972] bg-[#1a4972]/8 px-2 py-0.5 rounded-lg">
                  {{ task.case_code }}
                </span>
                <span v-if="task.case_title" class="text-[11px] text-slate-400 truncate max-w-[120px] sm:max-w-none">{{ task.case_title }}</span>
                <span v-if="task.due_date" class="flex items-center gap-1 text-[11px]" :class="getDueDateClass(task.due_date, task.status)">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  {{ formatDate(task.due_date) }}
                </span>
              </div>
            </div>
            <button @click="reopenTask(task)"
              class="shrink-0 self-start px-3 py-1.5 text-xs font-semibold text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all active:scale-95 whitespace-nowrap">
              View
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'
import CalendarWidget from '@/components/Calendar/CalendarWidget.vue'
import UpcomingHearings from '@/components/Calendar/UpcomingHearings.vue'
import { useRouter } from 'vue-router'
const router = useRouter()
const props = defineProps({
  clerkStats: { type: Object, default: () => ({}) },
  myTasks: { type: Array, default: () => [] },
  upcomingHearings: { type: Array, default: () => [] },
  hearingStats: { type: Object, default: () => ({}) }
})

const emit = defineEmits(['task-updated'])
const taskFilter = ref('all')

const filteredTasks = computed(() => {
  if (taskFilter.value === 'all') return props.myTasks || []
  return (props.myTasks || []).filter(t => t.status === taskFilter.value)
})

const completionPercentage = computed(() => {
  const total = props.clerkStats.total_tasks || 0
  const completed = props.clerkStats.completed_tasks || 0
  if (total === 0) return 0
  return Math.round((completed / total) * 100)
})

const dueThisWeek = computed(() => {
  const tasks = props.myTasks || []
  const now = new Date()
  const weekLater = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000)
  return tasks.filter(t => {
    if (!t.due_date || t.status === 'done') return false
    const due = new Date(t.due_date)
    return due >= now && due <= weekLater
  }).length
})

const getTaskStatusClass = (status) => {
  const classes = {
    'todo': 'bg-slate-100 text-slate-600',
    'in-progress': 'bg-amber-100 text-amber-700',
    'done': 'bg-emerald-100 text-emerald-700'
  }
  return classes[status] || 'bg-slate-100 text-slate-500'
}

const formatStatus = (status) => {
  const labels = { 'todo': 'To Do', 'in-progress': 'In Progress', 'done': 'Done' }
  return labels[status] || status
}

const getDueDateClass = (dueDate, status) => {
  if (status === 'done') return 'text-slate-400'
  const due = new Date(dueDate)
  const now = new Date()
  const diffInDays = Math.ceil((due - now) / (1000 * 60 * 60 * 24))
  if (diffInDays < 0) return 'text-red-600 font-semibold'
  if (diffInDays <= 3) return 'text-amber-600 font-semibold'
  return 'text-slate-500'
}

const formatDate = (dateString) => {
  if (!dateString) return 'No date'
  return new Date(dateString).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const reopenTask = () => { router.push('/casemaster') }
</script>