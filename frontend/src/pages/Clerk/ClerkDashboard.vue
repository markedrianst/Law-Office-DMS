<template>
  <div class="dashboard">
    <!-- Clerk Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Assigned Cases</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ clerkStats.assigned_cases }}</div>
        <div class="text-xs text-slate-500 mt-2">Cases you're handling</div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">My Tasks</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ clerkStats.total_tasks }}</div>
        <div class="flex items-center gap-2 mt-2">
          <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
            {{ clerkStats.pending_tasks }} Pending
          </span>
          <span class="text-xs text-emerald-600">{{ clerkStats.completed_tasks }} Done</span>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Due This Week</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ dueThisWeek }}</div>
        <div class="text-xs text-slate-500 mt-2">Tasks due soon</div>
      </div>
    </div>

    <!-- My Tasks -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">My Tasks</h3>
        <div class="flex items-center gap-2">
          <select v-model="taskFilter" class="text-xs border border-slate-200 rounded-lg px-2 py-1">
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="done">Done</option>
          </select>
        </div>
      </div>
      
      <div v-if="tasksLoading" class="py-8 text-center">
        <svg class="animate-spin w-6 h-6 text-[#1a4972] mx-auto" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
      </div>
      
      <div v-else class="divide-y divide-slate-50">
        <div v-for="task in filteredTasks" :key="task.id" class="px-6 py-4 hover:bg-slate-50/50">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-slate-800">{{ task.task }}</span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full" :class="taskStatusClass(task.status)">
                  {{ task.status }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1">Case: {{ task.case_code }} • Due: {{ formatDate(task.due_date) }}</p>
            </div>
            <button @click="toggleTaskStatus(task)" 
              class="px-3 py-1.5 text-xs font-semibold rounded-lg transition"
              :class="task.status === 'done' ? 'text-slate-400 hover:text-slate-600' : 'text-emerald-600 hover:bg-emerald-50'">
              {{ task.status === 'done' ? 'Undo' : 'Mark Done' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Movements -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-700">Recent Folder/Checklist Movements</h3>
      </div>
      <div class="divide-y divide-slate-50">
        <div v-for="movement in recentMovements" :key="movement.id" class="px-6 py-4 hover:bg-slate-50/50">
          <div class="flex items-center gap-3">
            <div :class="movement.type === 'OUT' ? 'bg-rose-100' : 'bg-emerald-100'" class="w-6 h-6 rounded-full flex items-center justify-center">
              <span class="text-xs font-bold" :class="movement.type === 'OUT' ? 'text-rose-700' : 'text-emerald-700'">
                {{ movement.type }}
              </span>
            </div>
            <div class="flex-1">
              <p class="text-sm text-slate-700">
                <span class="font-semibold">{{ movement.source === 'folder' ? 'Folder' : movement.task_name }}</span>
                {{ movement.type === 'OUT' ? 'released to' : 'received from' }} 
                <span class="font-semibold">{{ movement.from_to }}</span>
              </p>
              <p class="text-xs text-slate-400">Case: {{ movement.case_code }} • {{ formatDate(movement.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue';

const props = defineProps({
  clerkStats: Object,
  myTasks: Array,
  recentMovements: Array,
  tasksLoading: Boolean
});

const taskFilter = ref('all');

const filteredTasks = computed(() => {
  if (taskFilter.value === 'all') return props.myTasks || [];
  return (props.myTasks || []).filter(t => t.status === taskFilter.value);
});

const dueThisWeek = computed(() => {
  const tasks = props.myTasks || [];
  const now = new Date();
  const weekLater = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);
  
  return tasks.filter(t => {
    if (!t.due_date || t.status === 'done') return false;
    const due = new Date(t.due_date);
    return due >= now && due <= weekLater;
  }).length;
});

const taskStatusClass = (status) => ({
  'todo': 'bg-slate-100 text-slate-600',
  'in-progress': 'bg-amber-100 text-amber-700',
  'done': 'bg-emerald-100 text-emerald-700'
}[status] || 'bg-slate-100 text-slate-500');

const formatDate = (date) => {
  if (!date) return 'No date';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  });
};

const toggleTaskStatus = (task) => {
  // Emit event to parent to handle status change
  emit('toggle-task', task);
};
</script>