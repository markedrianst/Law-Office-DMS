<template>
  <div class="space-y-6">
    <!-- Clerk Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <!-- Assigned Cases -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ clerkStats.assigned_cases || 0 }}</div>
        <div class="text-xs text-slate-500 mt-1">Assigned Cases</div>
      </div>

      <!-- Total Tasks -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ clerkStats.total_tasks || 0 }}</div>
        <div class="text-xs text-slate-500 mt-1">Total Tasks</div>
      </div>

        <!-- Pendings tracker -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v1H3V7z"
              />
              <path 
                stroke-linecap="round" 
                stroke-linejoin="round" 
                stroke-width="2" 
                d="M3 10h18v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7z"
              />
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ clerkStats.pending_folders || 0 }}</div>
        <div class="text-xs text-slate-500 mt-1">Pending Folders</div>
      </div>
    </div>

    <!-- Task Status Breakdown -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <!-- Pending Tasks -->
      <div class="bg-amber-50 rounded-xl border border-amber-100 p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs font-medium text-amber-600 mb-1">Pending Tasks</div>
            <div class="text-2xl font-bold text-amber-900">{{ clerkStats.pending_tasks || 0 }}</div>
          </div>
          <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Due This Week -->
      <div class="bg-red-50 rounded-xl border border-red-100 p-4">
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs font-medium text-red-600 mb-1">Due This Week</div>
            <div class="text-2xl font-bold text-red-900">{{ clerkStats.due_tasks || 0 }}</div>
          </div>
          <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Completed -->
      <div class="bg-emerald-50 rounded-xl border border-emerald-100 p-4">
        <div class="flex items-center justify-between">
          <div>
          <div class="text-xs font-medium text-emerald-600 mb-1">Completed</div>
          <span class="text-2xl font-bold text-slate-800">{{ clerkStats.completed_tasks || 0 }}</span>
          <span class="text-sm text-slate-400">/{{ clerkStats.total_tasks || 0 }}</span>
          </div>
          <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
        </div>
              <div class="mt-3 flex items-center gap-2">
          <div class="flex-1 bg-slate-100 rounded-full h-1.5">
            <div 
              class="bg-emerald-500 h-1.5 rounded-full transition-all duration-500"
              :style="{ width: `${completionPercentage}%` }"
            ></div>
          </div>
          <span class="text-xs font-semibold text-emerald-600">{{ completionPercentage }}%</span>
        </div>
      </div>
    </div>

    <!-- Calendar and Hearings Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Calendar Widget -->
      <div>
        <CalendarWidget 
          :stats="hearingStats"
          :hearings="upcomingHearings"
        />
      </div>

      <!-- Upcoming Hearings -->
      <div class="lg:col-span-2">
        <UpcomingHearings 
          :hearings="upcomingHearings"
        />
      </div>
    </div>

    <!-- My Tasks -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            My Tasks
          </h3>
          <div class="flex items-center gap-2">
            <select v-model="taskFilter" class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 focus:outline-none focus:border-[#1a4972]">
              <option value="all">All Tasks</option>
              <option value="todo">To Do</option>
              <option value="in-progress">In Progress</option>
              <option value="done">Done</option>
            </select>
          </div>
        </div>
      </div>
      
      <div class="divide-y divide-slate-50 max-h-[500px] overflow-y-auto">
        <div v-if="!filteredTasks || filteredTasks.length === 0" class="px-6 py-8 text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-3">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <p class="text-sm text-slate-500">No tasks found</p>
        </div>

        <div 
          v-for="task in filteredTasks" 
          :key="task.id" 
          class="px-6 py-4 hover:bg-slate-50/50 transition-colors"
        >
          <div class="flex items-start justify-between gap-4">
            <!-- Task Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-2">
                <span 
                  class="text-xs font-semibold px-2 py-1 rounded-full"
                  :class="getTaskStatusClass(task.status)"
                >
                  {{ formatStatus(task.status) }}
                </span>
                <span v-if="task.document_type" class="text-xs text-slate-400">
                  {{ task.document_type }}
                </span>
              </div>
              <p class="text-sm font-medium text-slate-800 mb-1">{{ task.task }}</p>
              <div class="flex items-center gap-3 text-xs text-slate-500">
                <span v-if="task.case_code" class="flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                  </svg>
                  {{ task.case_code }}
                </span>
                <span v-if="task.case_title" class="text-slate-400">{{ task.case_title }}</span>
                <span v-if="task.due_date" class="flex items-center gap-1" :class="getDueDateClass(task.due_date, task.status)">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  Due: {{ formatDate(task.due_date) }}
                </span>
              </div>
            </div>

            <button 
              @click="reopenTask(task)"
              class="px-3 py-1.5 text-xs font-semibold text-slate-400 hover:bg-slate-50 rounded-lg transition-colors whitespace-nowrap"
            >
              View Details
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'
import CalendarWidget from '@/components/CalendarWidget.vue'
import UpcomingHearings from '@/components/UpcomingHearings.vue'
import { useRouter } from 'vue-router'
const router = useRouter()
const props = defineProps({
  clerkStats: {
    type: Object,
    default: () => ({})
  },
  myTasks: {
    type: Array,
    default: () => []
  },
  upcomingHearings: {
    type: Array,
    default: () => []
  },
  hearingStats: {
    type: Object,
    default: () => ({})
  }
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
  const labels = {
    'todo': 'To Do',
    'in-progress': 'In Progress',
    'done': 'Done'
  }
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
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const reopenTask = () => {
  router.push('/casemaster')
}
</script>