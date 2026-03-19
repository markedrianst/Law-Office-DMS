<template>
  <div class="space-y-6">
    <!-- Lawyer Stats Overview -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <!-- Assigned Cases -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg bg-[#1a4972]/10 flex items-center justify-center">
            <svg class="w-5 h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ lawyerStats.assigned_cases || 0 }}</div>
        <div class="text-xs text-slate-500 mt-1">Assigned Cases</div>
      </div>

      <!-- Active Cases -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ lawyerStats.active_cases || 0 }}</div>
        <div class="text-xs text-slate-500 mt-1">Active Cases</div>
      </div>

      <!-- Pending Approvals -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-lg bg-amber-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <div class="text-2xl font-bold text-slate-800">{{ lawyerStats.pending_approvals || 0 }}</div>
        <div class="text-xs text-slate-500 mt-1">Pending Approvals</div>
        <div class="mt-3 flex items-center gap-3 text-xs">
          <span class="text-amber-600 font-semibold">{{ pendingItems.documents || 0 }} Docs</span>
          <span class="text-slate-400">•</span>
          <span class="text-amber-600 font-semibold">{{ pendingItems.movements || 0 }} Moves</span>
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

    <!-- My Cases -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            My Cases
          </h3>
          <span class="text-xs text-slate-400">Recent cases assigned to you</span>
        </div>
      </div>
      
      <div class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
        <div v-if="!myCases || myCases.length === 0" class="px-6 py-8 text-center">
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-3">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
          <p class="text-sm text-slate-500">No cases assigned yet</p>
        </div>

        <div 
          v-for="(caseItem, index) in myCases" 
          :key="caseItem.id" 
          class="px-6 py-4 hover:bg-slate-50/50 transition-colors"
        >
          <div class="flex items-start justify-between gap-4">
            <!-- Case Details -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-mono font-semibold text-[#1a4972] bg-[#1a4972]/5 px-2 py-1 rounded">
                  {{ caseItem.case_code }}
                </span>
                <span 
                  class="text-xs font-semibold px-2 py-1 rounded-full"
                  :class="getCaseStatusClass(caseItem.case_status)"
                >
                  {{ caseItem.case_status }}
                </span>
                <span 
                  v-if="caseItem.priority"
                  class="text-xs font-semibold px-2 py-1 rounded-full"
                  :class="getPriorityClass(caseItem.priority)"
                >
                  {{ caseItem.priority }}
                </span>
              </div>
              <p class="text-sm font-medium text-slate-800 mb-1">{{ caseItem.title }}</p>
              <div class="flex items-center gap-3 text-xs text-slate-500">
                <span v-if="caseItem.client" class="flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  {{ caseItem.client }}
                </span>
                <span v-if="caseItem.stage" class="flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                  {{ caseItem.stage }}
                </span>
              </div>
            </div>

            <!-- Quick Actions -->
            <button 
              class="px-3 py-1.5 text-xs font-semibold text-[#1a4972] hover:bg-[#1a4972]/5 rounded-lg transition-colors"
              @click="viewCase(caseItem.id)"
            >
              View
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending Items Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Pending Documents -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Documents Awaiting Approval
        </h3>
        <div class="flex items-center justify-between p-4 bg-amber-50 rounded-lg">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <div class="text-2xl font-bold text-amber-900">{{ pendingItems.documents || 0 }}</div>
              <div class="text-xs text-amber-700">Pending Documents</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Movements -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
          </svg>
          Movements Awaiting Approval
        </h3>
        <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
              </svg>
            </div>
            <div>
              <div class="text-2xl font-bold text-blue-900">{{ pendingItems.movements || 0 }}</div>
              <div class="text-xs text-blue-700">Pending Movements</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { useRouter } from 'vue-router'
import CalendarWidget from '@/components/CalendarWidget.vue'
import UpcomingHearings from '@/components/UpcomingHearings.vue'

const router = useRouter()

defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  lawyerStats: {
    type: Object,
    default: () => ({})
  },
  myCases: {
    type: Array,
    default: () => []
  },
  pendingItems: {
    type: Object,
    default: () => ({})
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

const getCaseStatusClass = (status) => {
  const classes = {
    'active': 'bg-emerald-100 text-emerald-700',
    'closed': 'bg-slate-100 text-slate-600',
    'pending': 'bg-amber-100 text-amber-700',
    'archived': 'bg-slate-100 text-slate-500'
  }
  return classes[status?.toLowerCase()] || 'bg-slate-100 text-slate-600'
}

const getPriorityClass = (priority) => {
  const classes = {
    'high': 'bg-red-100 text-red-700',
    'medium': 'bg-amber-100 text-amber-700',
    'low': 'bg-blue-100 text-blue-700'
  }
  return classes[priority?.toLowerCase()] || 'bg-slate-100 text-slate-600'
}

const viewCase = (caseId) => {
  router.push(`/cases/${caseId}`)
}
</script>