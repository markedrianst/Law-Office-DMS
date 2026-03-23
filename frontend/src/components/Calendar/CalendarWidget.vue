<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-300 hover:shadow-md">
    <!-- Header -->
    <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a]">
      <div class="flex items-center justify-between">
        <button 
          @click="previousMonth"
          class="p-2 hover:bg-white/10 rounded-lg transition-all duration-200 hover:scale-110"
        >
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <h3 class="text-sm font-bold text-white tracking-wide">
          {{ currentMonthYear }}
        </h3>
        <button 
          @click="nextMonth"
          class="p-2 hover:bg-white/10 rounded-lg transition-all duration-200 hover:scale-110"
        >
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="p-4">
      <!-- Weekday headers -->
      <div class="grid grid-cols-7 gap-1 mb-3">
        <div 
          v-for="day in weekDays" 
          :key="day"
          class="text-center text-xs font-semibold text-slate-400 py-1.5"
        >
          {{ day }}
        </div>
      </div>

      <!-- Calendar days -->
      <div class="grid grid-cols-7 gap-1">
        <button
          v-for="(day, index) in calendarDays"
          :key="index"
          @click="selectDate(day)"
          :disabled="!day.isCurrentMonth"
          class="relative aspect-square flex items-center justify-center text-sm rounded-lg transition-all duration-200"
          :class="getDayClass(day)"
        >
          <span class="relative z-10 font-medium">{{ day.date }}</span>
          
          <!-- Multiple event indicator -->
          <div 
            v-if="day.hasEvents && day.isCurrentMonth" 
            class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 flex gap-0.5"
          >
            <div class="w-1 h-1 rounded-full bg-[#1a4972]"></div>
            <div v-if="day.eventCount > 1" class="w-1 h-1 rounded-full bg-[#1a4972]"></div>
          </div>
          
          <!-- Single event dot -->
          <div 
            v-else-if="day.hasSingleEvent && day.isCurrentMonth" 
            class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-[#1a4972]"
          ></div>
        </button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/80">
      <div class="grid grid-cols-3 gap-3 text-center">
        <div class="p-2 rounded-lg hover:bg-white transition-all cursor-pointer group">
          <div class="text-xl font-bold text-red-600 group-hover:scale-110 transition-transform">
            {{ stats.today || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-0.5">Today</div>
        </div>
        <div class="p-2 rounded-lg hover:bg-white transition-all cursor-pointer group">
          <div class="text-xl font-bold text-amber-600 group-hover:scale-110 transition-transform">
            {{ stats.tomorrow || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-0.5">Tomorrow</div>
        </div>
        <div class="p-2 rounded-lg hover:bg-white transition-all cursor-pointer group">
          <div class="text-xl font-bold text-emerald-600 group-hover:scale-110 transition-transform">
            {{ stats.this_week || 0 }}
          </div>
          <div class="text-xs text-slate-500 mt-0.5">This Week</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, watch } from 'vue'

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({})
  },
  hearings: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['date-selected'])

const currentDate = ref(new Date())
const selectedDate = ref(new Date())

const weekDays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { 
    month: 'long', 
    year: 'numeric' 
  })
})

const getEventsByDate = computed(() => {
  const eventsMap = new Map()
  props.hearings.forEach(hearing => {
    if (hearing.hearing_date) {
      const dateStr = hearing.hearing_date.split('T')[0]
      if (!eventsMap.has(dateStr)) {
        eventsMap.set(dateStr, [])
      }
      eventsMap.get(dateStr).push(hearing)
    }
  })
  return eventsMap
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const prevLastDay = new Date(year, month, 0)
  
  const firstDayOfWeek = firstDay.getDay()
  const lastDateOfMonth = lastDay.getDate()
  const prevLastDate = prevLastDay.getDate()
  
  const days = []
  
  // Previous month days
  for (let i = firstDayOfWeek - 1; i >= 0; i--) {
    days.push({
      date: prevLastDate - i,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      hasEvents: false,
      hasSingleEvent: false,
      eventCount: 0,
      fullDate: null
    })
  }
  
  // Current month days
  const today = new Date()
  for (let i = 1; i <= lastDateOfMonth; i++) {
    const date = new Date(year, month, i)
    const dateStr = date.toISOString().split('T')[0]
    
    const events = getEventsByDate.value.get(dateStr) || []
    const hasEvents = events.length > 0
    
    const isToday = date.toDateString() === today.toDateString()
    const isSelected = date.toDateString() === selectedDate.value.toDateString()
    
    days.push({
      date: i,
      isCurrentMonth: true,
      isToday,
      isSelected,
      hasEvents,
      hasSingleEvent: events.length === 1,
      eventCount: events.length,
      fullDate: date,
      events
    })
  }
  
  // Next month days to fill the grid (6 rows)
  const remainingDays = 42 - days.length
  for (let i = 1; i <= remainingDays; i++) {
    days.push({
      date: i,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      hasEvents: false,
      hasSingleEvent: false,
      eventCount: 0,
      fullDate: null
    })
  }
  
  return days
})

const getDayClass = (day) => {
  const classes = []
  
  if (!day.isCurrentMonth) {
    classes.push('text-slate-300 cursor-not-allowed opacity-50')
  } else {
    classes.push('text-slate-700 hover:bg-slate-100 cursor-pointer font-medium')
  }
  
  if (day.isToday && day.isCurrentMonth) {
    classes.push('bg-[#1a4972] text-white font-bold hover:bg-[#2d6db5] shadow-md')
  }
  
  if (day.isSelected && !day.isToday && day.isCurrentMonth) {
    classes.push('bg-blue-50 text-[#1a4972] font-semibold border-2 border-[#1a4972]/30')
  }
  
  if (day.hasEvents && !day.isToday && !day.isSelected && day.isCurrentMonth) {
    classes.push('font-semibold text-[#1a4972]')
  }
  
  return classes.join(' ')
}

const previousMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() - 1,
    1
  )
}

const nextMonth = () => {
  currentDate.value = new Date(
    currentDate.value.getFullYear(),
    currentDate.value.getMonth() + 1,
    1
  )
}

const selectDate = (day) => {
  if (day.isCurrentMonth && day.fullDate) {
    selectedDate.value = day.fullDate
    emit('date-selected', day.fullDate, day.events)
  }
}

// Watch for hearings changes to re-render
watch(() => props.hearings, () => {
  // Force re-render
}, { deep: true })
</script>