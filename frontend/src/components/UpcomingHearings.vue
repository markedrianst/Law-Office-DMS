<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-slate-100">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          Upcoming Hearings & Schedules
        </h3>
        <button 
          @click="viewAll"
          class="text-xs font-semibold text-[#1a4972] hover:text-[#2d6db5] transition-colors"
        >
          View All →
        </button>
      </div>
    </div>

    <!-- Hearings List -->
    <div class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
      <!-- Empty State -->
      <div v-if="!hearings || hearings.length === 0" class="px-6 py-8 text-center">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-slate-100 mb-3">
          <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-sm text-slate-500">No upcoming hearings</p>
      </div>

      <!-- Hearing Items -->
      <div 
        v-for="hearing in hearings" 
        :key="hearing.id"
        class="px-6 py-4 hover:bg-slate-50/50 transition-colors"
      >
        <div class="flex items-start gap-3">
          <!-- Urgency Indicator -->
          <div 
            class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
            :class="getUrgencyBgClass(getComputedUrgency(hearing))"
          >
            <svg 
              v-if="getComputedUrgency(hearing) === 'today'"
              class="w-5 h-5" 
              :class="getUrgencyIconClass(getComputedUrgency(hearing))"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <svg 
              v-else-if="getComputedUrgency(hearing) === 'soon'"
              class="w-5 h-5" 
              :class="getUrgencyIconClass(getComputedUrgency(hearing))"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <svg 
              v-else
              class="w-5 h-5" 
              :class="getUrgencyIconClass(getComputedUrgency(hearing))"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>

          <!-- Hearing Details -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2 mb-1">
              <div class="flex-1">
                <h4 class="text-sm font-semibold text-slate-800 mb-0.5">{{ hearing.title }}</h4>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                  <span 
                    class="text-xs font-semibold px-2 py-0.5 rounded-full"
                    :class="getUrgencyBadgeClass(getComputedUrgency(hearing))"
                  >
                    {{ getUrgencyLabel(hearing) }}
                  </span>
                  <span 
                    class="text-xs font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600"
                  >
                    {{ hearing.type }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Date & Time -->
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 mb-2">
              <span class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                {{ formatDate(hearing.hearing_date) }}
              </span>
              <span v-if="hearing.start_time" class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ hearing.start_time }}
              </span>
              <span v-if="hearing.location" class="flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ hearing.location }}
              </span>
            </div>

            <!-- Case Info -->
            <div v-if="hearing.case_code" class="flex items-center gap-2 text-xs">
              <span class="inline-flex items-center gap-1 text-[#1a4972] font-medium">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                {{ hearing.case_code }}
              </span>
              <span v-if="hearing.case_title" class="text-slate-400">{{ hearing.case_title }}</span>
            </div>
            <div v-if="hearing.client_name" class="text-xs text-slate-400 mt-1">
              Client: {{ hearing.client_name }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const emit = defineEmits(['view-all'])

defineProps({
  hearings: {
    type: Array,
    default: () => []
  }
})

// Compute urgency from hearing_date so color is always accurate regardless of backend value
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
    'soon': 'bg-yellow-100',
    'future': 'bg-emerald-100'
  }
  return classes[urgency] || 'bg-slate-100'
}

const getUrgencyIconClass = (urgency) => {
  const classes = {
    'today': 'text-red-600',
    'soon': 'text-yellow-500',
    'future': 'text-emerald-600'
  }
  return classes[urgency] || 'text-slate-600'
}

const getUrgencyBadgeClass = (urgency) => {
  const classes = {
    'today': 'bg-red-100 text-red-700',
    'soon': 'bg-yellow-100 text-yellow-700',
    'future': 'bg-emerald-100 text-emerald-700'
  }
  return classes[urgency] || 'bg-slate-100 text-slate-600'
}

const getUrgencyLabel = (hearing) => {
  const urgency = getComputedUrgency(hearing)
  const days = getDaysUntil(hearing)
  if (urgency === 'today') return 'Today'
  if (days === 1) return 'Tomorrow'
  if (days !== null) return `In ${days} days`
  return 'Scheduled'
}

const formatDate = (dateString) => {
  if (!dateString) return 'No date'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const viewAll = () => {
  router.push('/calendar')
}
</script>