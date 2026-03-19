<template>
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Header -->
    <div class="px-4 py-3 border-b border-slate-100 bg-[#1a4972]">
      <div class="flex items-center justify-between">
        <button 
          @click="previousMonth"
          class="p-1 hover:bg-white/10 rounded-lg transition-colors"
        >
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <h3 class="text-sm font-semibold text-white">
          {{ currentMonthYear }}
        </h3>
        <button 
          @click="nextMonth"
          class="p-1 hover:bg-white/10 rounded-lg transition-colors"
        >
          <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Calendar Grid -->
    <div class="p-3">
      <!-- Weekday headers -->
      <div class="grid grid-cols-7 gap-1 mb-2">
        <div 
          v-for="day in weekDays" 
          :key="day"
          class="text-center text-xs font-semibold text-slate-400 py-1"
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
          class="relative aspect-square flex items-center justify-center text-xs rounded-lg transition-all"
          :class="getDayClass(day)"
        >
          <span class="relative z-10">{{ day.date }}</span>
          
          <!-- Event indicator dots -->
          <div 
            v-if="day.hasEvents && day.isCurrentMonth" 
            class="absolute bottom-0.5 left-1/2 -translate-x-1/2 flex gap-0.5"
          >
            <div class="w-1 h-1 rounded-full bg-[#1a4972]"></div>
          </div>
        </button>
      </div>
    </div>

    <!-- Quick Stats -->
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
      <div class="grid grid-cols-3 gap-2 text-center">
        <div>
          <div class="text-lg font-bold text-red-600">{{ stats.today || 0 }}</div>
          <div class="text-xs text-slate-500">Today</div>
        </div>
        <div>
          <div class="text-lg font-bold text-amber-600">{{ stats.tomorrow || 0 }}</div>
          <div class="text-xs text-slate-500">Tomorrow</div>
        </div>
        <div>
          <div class="text-lg font-bold text-emerald-600">{{ stats.this_week || 0 }}</div>
          <div class="text-xs text-slate-500">This Week</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps } from 'vue'

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

const currentDate = ref(new Date())
const selectedDate = ref(new Date())

const weekDays = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa']

const currentMonthYear = computed(() => {
  return currentDate.value.toLocaleDateString('en-US', { 
    month: 'long', 
    year: 'numeric' 
  })
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
      hasEvents: false
    })
  }
  
  // Current month days
  const today = new Date()
  for (let i = 1; i <= lastDateOfMonth; i++) {
    const date = new Date(year, month, i)
    const dateStr = date.toISOString().split('T')[0]
    
    const isToday = date.toDateString() === today.toDateString()
    const isSelected = date.toDateString() === selectedDate.value.toDateString()
    const hasEvents = props.hearings.some(h => h.hearing_date === dateStr)
    
    days.push({
      date: i,
      isCurrentMonth: true,
      isToday,
      isSelected,
      hasEvents,
      fullDate: date
    })
  }
  
  // Next month days to fill the grid
  const remainingDays = 42 - days.length // 6 weeks * 7 days
  for (let i = 1; i <= remainingDays; i++) {
    days.push({
      date: i,
      isCurrentMonth: false,
      isToday: false,
      isSelected: false,
      hasEvents: false
    })
  }
  
  return days
})

const getDayClass = (day) => {
  const classes = []
  
  if (!day.isCurrentMonth) {
    classes.push('text-slate-300 cursor-not-allowed')
  } else {
    classes.push('text-slate-700 hover:bg-slate-100 cursor-pointer')
  }
  
  if (day.isToday && day.isCurrentMonth) {
    classes.push('bg-[#1a4972] text-white font-bold hover:bg-[#2d6db5]')
  }
  
  if (day.isSelected && !day.isToday && day.isCurrentMonth) {
    classes.push('bg-blue-100 text-[#1a4972] font-semibold')
  }
  
  if (day.hasEvents && !day.isToday && !day.isSelected && day.isCurrentMonth) {
    classes.push('font-medium')
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
  }
}
</script>