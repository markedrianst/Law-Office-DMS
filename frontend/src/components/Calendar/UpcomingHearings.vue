<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-md">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <div class="w-1 h-5 rounded-full bg-[#1a4972]"></div>
          <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Upcoming Hearings & Schedules
          </h3>
        </div>
        <button 
          @click="viewAll"
          class="text-xs font-semibold text-[#1a4972] hover:text-[#2d6db5] transition-all duration-200 hover:translate-x-0.5 flex items-center gap-1"
        >
          View All
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Hearings List -->
    <div class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto">
      <!-- Empty State -->
      <div v-if="!hearings || hearings.length === 0" class="px-6 py-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
          <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-sm font-medium text-slate-600">No upcoming hearings</p>
        <p class="text-xs text-slate-400 mt-1">All caught up for now</p>
      </div>

      <!-- Hearing Items -->
      <div 
        v-for="hearing in hearings" 
        :key="hearing.id"
        class="px-5 py-4 hover:bg-slate-50/80 transition-all duration-200 group cursor-pointer"
        @click="goToCase(hearing)"
      >
        <div class="flex items-start gap-4">
          <!-- Urgency Icon -->
          <div 
            class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-all duration-200 group-hover:scale-105"
            :class="getUrgencyBgClass(getComputedUrgency(hearing))"
          >
            <svg 
              class="w-5 h-5 transition-transform" 
              :class="getUrgencyIconClass(getComputedUrgency(hearing))"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path v-if="getComputedUrgency(hearing) === 'today'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              <path v-else-if="getComputedUrgency(hearing) === 'soon'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>

          <!-- Hearing Details -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-2">
              <div class="flex-1">
                <h4 class="text-sm font-bold text-slate-800 mb-1 group-hover:text-[#1a4972] transition-colors">
                  {{ hearing.title || 'Hearing' }}
                </h4>
                <div class="flex flex-wrap items-center gap-2">
                  <span 
                    class="text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="getUrgencyBadgeClass(getComputedUrgency(hearing))"
                  >
                    {{ getUrgencyLabel(hearing) }}
                  </span>
                  <span 
                    v-if="hearing.type"
                    class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium"
                  >
                    {{ hearing.type }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Date & Time -->
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mb-2">
              <span class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ formatDate(hearing.hearing_date) }}
              </span>
              <span v-if="hearing.start_time" class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ hearing.start_time }}
              </span>
              <span v-if="hearing.location" class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ truncate(hearing.location, 30) }}
              </span>
            </div>

            <!-- Case Info -->
            <div v-if="hearing.case_code" class="flex flex-wrap items-center gap-2 text-xs mt-1">
              <span class="inline-flex items-center gap-1.5 font-mono bg-slate-50 px-2 py-0.5 rounded-md text-[#1a4972] border border-slate-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                {{ hearing.case_code }}
              </span>
              <span v-if="hearing.case_title" class="text-slate-500">{{ truncate(hearing.case_title, 40) }}</span>
            </div>
            <div v-if="hearing.client_name" class="flex items-center gap-1.5 text-xs text-slate-400 mt-1.5">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ hearing.client_name }}
            </div>
          </div>

          <!-- Arrow indicator -->
          <div class="opacity-0 group-hover:opacity-100 transition-all duration-200 translate-x-0 group-hover:translate-x-0.5">
            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

defineProps({
  hearings: {
    type: Array,
    default: () => []
  }
})

const getComputedUrgency = (hearing) => {
  if (!hearing.hearing_date) return 'future'
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const hearingDate = new Date(hearing.hearing_date)
  hearingDate.setHours(0, 0, 0, 0)
  const diffDays = Math.round((hearingDate - today) / (1000 * 60 * 60 * 24))
  if (diffDays === 0) return 'today'
  if (diffDays <= 7) return 'soon'
  return 'future'
}

const getDaysUntil = (hearing) => {
  if (!hearing.hearing_date) return null
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const hearingDate = new Date(hearing.hearing_date)
  hearingDate.setHours(0, 0, 0, 0)
  return Math.round((hearingDate - today) / (1000 * 60 * 60 * 24))
}

const getUrgencyBgClass = (urgency) => {
  const classes = {
    'today': 'bg-red-100',
    'soon': 'bg-amber-100',
    'future': 'bg-emerald-100'
  }
  return classes[urgency] || 'bg-slate-100'
}

const getUrgencyIconClass = (urgency) => {
  const classes = {
    'today': 'text-red-600',
    'soon': 'text-amber-600',
    'future': 'text-emerald-600'
  }
  return classes[urgency] || 'text-slate-600'
}

const getUrgencyBadgeClass = (urgency) => {
  const classes = {
    'today': 'bg-red-100 text-red-700',
    'soon': 'bg-amber-100 text-amber-700',
    'future': 'bg-emerald-100 text-emerald-700'
  }
  return classes[urgency] || 'bg-slate-100 text-slate-600'
}

const getUrgencyLabel = (hearing) => {
  const urgency = getComputedUrgency(hearing)
  const days = getDaysUntil(hearing)
  if (urgency === 'today') return 'Today'
  if (days === 1) return 'Tomorrow'
  if (days !== null && days > 0) return `In ${days} days`
  return 'Scheduled'
}

const formatDate = (dateString) => {
  if (!dateString) return 'No date'
  const date = new Date(dateString)
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  if (date.toDateString() === today.toDateString()) return 'Today'
  if (date.toDateString() === tomorrow.toDateString()) return 'Tomorrow'
  
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
}

const truncate = (str, length) => {
  if (!str) return ''
  if (str.length <= length) return str
  return str.substring(0, length) + '...'
}

const viewAll = () => {
  router.push('/calendar')
}

const goToCase = (hearing) => {
  if (hearing.case_id) {
    router.push(`/casemaster/${hearing.case_id}`)
  }
}
</script>