<template>
  <div class="space-y-4">

    <!-- ── Row 1: 3 stat tiles ── -->
    <div class="grid grid-cols-3 gap-3">

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-9 h-9 rounded-xl bg-[#1a4972]/10 flex items-center justify-center mb-3">
          <svg class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
          </svg>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800 leading-none">{{ lawyerStats.assigned_cases || 0 }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-1.5 leading-tight">Assigned Cases</p>
      </div>

      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="w-9 h-9 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-slate-800 leading-none">{{ lawyerStats.active_cases || 0 }}</p>
        <p class="text-[11px] text-slate-400 font-medium mt-1.5 leading-tight">Active Cases</p>
      </div>

      <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4">
        <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center mb-3">
          <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-2xl sm:text-3xl font-bold text-amber-900 leading-none">{{ lawyerStats.pending_approvals || 0 }}</p>
        <p class="text-[11px] text-amber-600 font-medium mt-1.5 leading-tight">Pending Approvals</p>
        <div class="flex items-center gap-1.5 mt-2">
          <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-lg">{{ pendingItems.documents || 0 }} Docs</span>
          <span class="text-[10px] font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-lg">{{ pendingItems.movements || 0 }} Moves</span>
        </div>
      </div>
    </div>

    <!-- ── Calendar + Hearings ── -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div><CalendarWidget :stats="hearingStats" :hearings="upcomingHearings"/></div>
      <div class="lg:col-span-2"><UpcomingHearings :hearings="upcomingHearings"/></div>
    </div>

    <!-- ── My Cases ── -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <div class="w-1 h-4 rounded-full bg-[#1a4972]"></div>
          <h3 class="text-sm font-bold text-slate-700">My Cases</h3>
        </div>
        <span class="text-xs text-slate-400">Recent cases assigned to you</span>
      </div>

      <!-- Empty state -->
      <div v-if="!myCases || myCases.length === 0" class="flex flex-col items-center py-12">
        <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
          <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
          </svg>
        </div>
        <p class="text-sm font-semibold text-slate-400">No cases assigned yet</p>
      </div>

      <!-- Case rows -->
      <div class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
        <div v-for="(caseItem, index) in myCases" :key="caseItem.id"
          class="px-4 sm:px-5 py-4 hover:bg-slate-50/60 transition-colors">

          <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
              <!-- Badges -->
              <div class="flex flex-wrap items-center gap-1.5 mb-1.5">
                <span class="text-[11px] font-mono font-bold text-[#1a4972] bg-[#1a4972]/8 px-2 py-0.5 rounded-lg">
                  {{ caseItem.case_code }}
                </span>
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="getCaseStatusClass(caseItem.case_status)">
                  {{ caseItem.case_status }}
                </span>
                <span v-if="caseItem.priority" class="text-[10px] font-bold px-2 py-0.5 rounded-full" :class="getPriorityClass(caseItem.priority)">
                  {{ caseItem.priority }}
                </span>
              </div>
              <!-- Title -->
              <p class="text-sm font-semibold text-slate-800 mb-1.5">{{ caseItem.title }}</p>
              <!-- Meta -->
              <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                <span v-if="caseItem.client" class="flex items-center gap-1 text-[11px] text-slate-400">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  {{ caseItem.client }}
                </span>
                <span v-if="caseItem.stage" class="text-[11px] text-slate-400">{{ caseItem.stage }}</span>
              </div>
            </div>
            <button class="shrink-0 self-start px-3 py-1.5 text-xs font-semibold text-[#1a4972] bg-[#1a4972]/8 hover:bg-[#1a4972]/15 rounded-xl transition-all active:scale-95 whitespace-nowrap"
              @click="viewCase(caseItem.id)">
              View
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Pending Breakdown ── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

      <!-- Documents -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-1 h-4 rounded-full bg-amber-500"></div>
          <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Documents Awaiting Approval</p>
        </div>
        <div class="flex items-center gap-4 bg-amber-50 rounded-xl border border-amber-100 p-4">
          <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <div>
            <p class="text-3xl font-bold text-amber-900 leading-none">{{ pendingItems.documents || 0 }}</p>
            <p class="text-[11px] font-semibold text-amber-600 mt-1">Pending Documents</p>
          </div>
        </div>
      </div>

      <!-- Movements -->
      <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-1 h-4 rounded-full bg-blue-500"></div>
          <p class="text-xs font-bold text-slate-600 uppercase tracking-wide">Movements Awaiting Approval</p>
        </div>
        <div class="flex items-center gap-4 bg-blue-50 rounded-xl border border-blue-100 p-4">
          <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
            </svg>
          </div>
          <div>
            <p class="text-3xl font-bold text-blue-900 leading-none">{{ pendingItems.movements || 0 }}</p>
            <p class="text-[11px] font-semibold text-blue-600 mt-1">Pending Movements</p>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { useRouter } from 'vue-router'
import CalendarWidget from '@/components/Calendar/CalendarWidget.vue'
import UpcomingHearings from '@/components/Calendar/UpcomingHearings.vue'

const router = useRouter()

defineProps({
  stats: { type: Object, default: () => ({}) },
  lawyerStats: { type: Object, default: () => ({}) },
  myCases: { type: Array, default: () => [] },
  pendingItems: { type: Object, default: () => ({}) },
  upcomingHearings: { type: Array, default: () => [] },
  hearingStats: { type: Object, default: () => ({}) }
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

const viewCase = (caseId) => { router.push(`/cases/${caseId}`) }
</script>