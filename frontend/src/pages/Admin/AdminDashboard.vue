<template>
  <div class="space-y-6">
    <!-- System Overview Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Cases -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-lg hover:border-[#1a4972]/20 transition-all duration-300 group cursor-pointer">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-xl bg-blue-50 group-hover:bg-blue-100 transition-colors flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
          <span class="text-3xl font-bold text-slate-800">{{ stats.total_cases || 0 }}</span>
        </div>
        <div class="text-sm font-medium text-slate-600">Total Cases</div>
        <div class="mt-3 flex items-center gap-2">
          <div class="flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span class="text-xs text-slate-500">Active:</span>
            <span class="text-xs font-semibold text-emerald-600">{{ stats.active_cases || 0 }}</span>
          </div>
          <div class="w-px h-3 bg-slate-200"></div>
          <div class="flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-slate-400"></span>
            <span class="text-xs text-slate-500">Closed:</span>
            <span class="text-xs font-semibold text-slate-600">{{ (stats.total_cases || 0) - (stats.active_cases || 0) }}</span>
          </div>
        </div>
      </div>

      <!-- Total Clients -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-lg hover:border-[#1a4972]/20 transition-all duration-300 group cursor-pointer">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-xl bg-purple-50 group-hover:bg-purple-100 transition-colors flex items-center justify-center">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <span class="text-3xl font-bold text-slate-800">{{ stats.total_clients || 0 }}</span>
        </div>
        <div class="text-sm font-medium text-slate-600">Total Clients</div>
        <div class="mt-3 w-full bg-slate-100 rounded-full h-1.5">
          <div class="bg-purple-500 h-1.5 rounded-full" :style="{ width: `${Math.min((stats.total_clients || 0) / (stats.total_cases || 1) * 100, 100)}%` }"></div>
        </div>
      </div>

      <!-- Staff Members -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-lg hover:border-[#1a4972]/20 transition-all duration-300 group cursor-pointer">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-xl bg-indigo-50 group-hover:bg-indigo-100 transition-colors flex items-center justify-center">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <span class="text-3xl font-bold text-slate-800">{{ adminStats.total_users || 0 }}</span>
        </div>
        <div class="text-sm font-medium text-slate-600">Staff Members</div>
        <div class="mt-3 flex items-center gap-3">
          <div class="flex items-center gap-1.5 px-2 py-1 bg-[#1a4972]/5 rounded-lg">
            <div class="w-2 h-2 rounded-full bg-[#1a4972]"></div>
            <span class="text-xs font-medium text-[#1a4972]">{{ adminStats.lawyers || 0 }} Lawyers</span>
          </div>
          <div class="flex items-center gap-1.5 px-2 py-1 bg-emerald-50 rounded-lg">
            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
            <span class="text-xs font-medium text-emerald-700">{{ adminStats.clerks || 0 }} Clerks</span>
          </div>
        </div>
      </div>

      <!-- Pending Approvals -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-lg hover:border-[#1a4972]/20 transition-all duration-300 group cursor-pointer">
        <div class="flex items-start justify-between mb-3">
          <div class="w-11 h-11 rounded-xl bg-amber-50 group-hover:bg-amber-100 transition-colors flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-3xl font-bold text-slate-800">{{ adminStats.pending_total || 0 }}</span>
        </div>
        <div class="text-sm font-medium text-slate-600">Pending Approvals</div>
        <div class="mt-3 flex items-center gap-3">
          <div class="flex items-center gap-1.5 px-2 py-1 bg-amber-50 rounded-lg">
            <span class="text-xs font-medium text-amber-700">{{ adminStats.pending_documents || 0 }} Documents</span>
          </div>
          <div class="flex items-center gap-1.5 px-2 py-1 bg-blue-50 rounded-lg">
            <span class="text-xs font-medium text-blue-700">{{ adminStats.pending_movements || 0 }} Movements</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Calendar and Hearings Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Calendar Widget -->
      <div class="transition-all duration-300 hover:translate-y-[-2px]">
        <CalendarWidget 
          :stats="hearingStats"
          :hearings="upcomingHearings"
        />
      </div>

      <!-- Upcoming Hearings -->
      <div class="lg:col-span-2 transition-all duration-300 hover:translate-y-[-2px]">
        <UpcomingHearings 
          :hearings="upcomingHearings"
        />
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-md">
      <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
        <div class="flex items-center justify-between">
          <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <div class="w-1 h-4 rounded-full bg-[#1a4972]"></div>
            Recent Activities
          </h3>
          <span class="text-xs text-slate-400 flex items-center gap-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Last 10 activities
          </span>
        </div>
      </div>
      
      <div class="divide-y divide-slate-100 max-h-96 overflow-y-auto">
        <div v-if="!recentActivities || recentActivities.length === 0" class="px-6 py-12 text-center">
          <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-slate-100 mb-4">
            <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
          </div>
          <p class="text-sm text-slate-500">No recent activities</p>
        </div>

        <div 
          v-for="(activity, index) in recentActivities" 
          :key="index" 
          class="px-6 py-4 hover:bg-slate-50 transition-all duration-200 group"
        >
          <div class="flex items-start gap-4">
            <!-- Activity Icon -->
            <div 
              class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 transition-all duration-200 group-hover:scale-105"
              :class="activity.type === 'system' ? 'bg-blue-50' : 'bg-emerald-50'"
            >
              <svg 
                v-if="activity.type === 'system'" 
                class="w-4 h-4 text-blue-600" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              <svg 
                v-else 
                class="w-4 h-4 text-emerald-600" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>

            <!-- Activity Details -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex-1">
                  <p class="text-sm text-slate-700">
                    <span class="font-semibold">{{ activity.user_name || 'System' }}</span>
                    <span class="text-slate-500"> {{ activity.action }}</span>
                  </p>
                  <div v-if="activity.case_code" class="mt-1.5 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-mono bg-slate-100 px-2 py-0.5 rounded-md text-slate-600">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                      </svg>
                      {{ activity.case_code }}
                    </span>
                    <span v-if="activity.case_title" class="text-xs text-slate-500">{{ truncate(activity.case_title, 50) }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs text-slate-400 whitespace-nowrap">
                    {{ formatDateTime(activity.created_at) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Document Status -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <div class="w-1 h-4 rounded-full bg-amber-500"></div>
            Document Management
          </h3>
          <div class="text-xs text-slate-400 flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            <span>Pending Review</span>
          </div>
        </div>
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-white rounded-xl">
          <div>
            <p class="text-3xl font-bold text-amber-700">{{ adminStats.pending_documents || 0 }}</p>
            <p class="text-xs text-slate-500 mt-1">Documents awaiting approval</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Movement Status -->
      <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-all duration-300">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
            <div class="w-1 h-4 rounded-full bg-blue-500"></div>
            Folder Movements
          </h3>
          <div class="text-xs text-slate-400 flex items-center gap-1">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
            <span>Pending Actions</span>
          </div>
        </div>
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-white rounded-xl">
          <div>
            <p class="text-3xl font-bold text-blue-700">{{ adminStats.pending_movements || 0 }}</p>
            <p class="text-xs text-slate-500 mt-1">Movements awaiting processing</p>
          </div>
          <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import CalendarWidget from '@/components/Calendar/CalendarWidget.vue'
import UpcomingHearings from '@/components/Calendar/UpcomingHearings.vue'

defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  adminStats: {
    type: Object,
    default: () => ({})
  },
  recentActivities: {
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

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  const now = new Date()
  const diffInMs = now - date
  const diffInMins = Math.floor(diffInMs / 60000)
  
  if (diffInMins < 1) return 'Just now'
  if (diffInMins < 60) return `${diffInMins}m ago`
  
  const diffInHours = Math.floor(diffInMins / 60)
  if (diffInHours < 24) return `${diffInHours}h ago`
  
  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) return `${diffInDays}d ago`
  
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}

const truncate = (str, length) => {
  if (!str) return ''
  if (str.length <= length) return str
  return str.substring(0, length) + '...'
}
</script>