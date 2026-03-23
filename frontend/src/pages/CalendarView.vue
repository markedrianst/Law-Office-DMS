<template>
  <div class="min-h-screen p-3 sm:p-6 bg-slate-50 font-sans">
    <!-- Silent refresh indicator -->
    <div
      v-if="isRefreshing"
      class="fixed top-4 right-4 z-50 flex items-center gap-2 px-3 py-1.5 bg-white/90 backdrop-blur-sm rounded-full shadow-lg border border-blue-100 animate-slide-down"
    >
      <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
      <span class="text-xs font-medium text-blue-600">Syncing...</span>
    </div>

    <!-- Header Section -->
    <div class="mb-4 sm:mb-6">
      <div class="flex items-center justify-between mb-1">
        <div class="flex items-center gap-3">
          <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
          <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1a4972]">Calendar</h1>
        </div>
      </div>
      <p class="text-xs sm:text-sm ml-4 pl-3 text-slate-500">Manage hearings, meetings, and deadlines</p>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 mb-4 space-y-2">
      <!-- Row 1: Month nav + New Event + Refresh -->
      <div class="flex items-center gap-2 flex-wrap">
        <!-- Month Navigation -->
        <div class="flex items-center gap-1 bg-slate-50 rounded-xl border border-slate-200 p-1 shrink-0">
          <button @click="previousMonth" class="p-1.5 hover:bg-white rounded-lg transition">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <span class="text-xs sm:text-sm font-semibold text-slate-700 px-1 whitespace-nowrap">{{ currentMonthName }} {{ currentYear }}</span>
          <button @click="nextMonth" class="p-1.5 hover:bg-white rounded-lg transition">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <button @click="goToToday" class="px-2 py-1 text-xs font-medium bg-white border border-slate-200 rounded-lg hover:bg-slate-50 whitespace-nowrap">Today</button>
        </div>
        <div class="flex-1"></div>
        <!-- New Event -->
        <button @click="openCreateModal"
          class="shrink-0 px-3 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold inline-flex items-center gap-1.5 transition-all whitespace-nowrap hover:shadow-lg active:scale-95 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white shadow-md shadow-[#1a4972]/30">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <span class="hidden sm:inline">New Event</span>
          <span class="sm:hidden">New</span>
        </button>
        <!-- Refresh -->
        <button @click="manualRefresh" :disabled="isRefreshing"
          class="shrink-0 px-3 sm:px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold inline-flex items-center gap-1.5 transition-all whitespace-nowrap hover:shadow-md active:scale-95 disabled:opacity-50 bg-white text-[#1a4972] border border-[#1a4972]/30 hover:bg-[#1a4972]/5">
          <svg v-if="isRefreshing" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          <span class="hidden sm:inline">Refresh</span>
        </button>
      </div>
      <!-- Row 2: Filters -->
      <div class="flex flex-wrap items-center gap-2">
        <select v-model="filters.type" @change="applyFilters"
          class="flex-1 min-w-[110px] px-3 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
          <option value="">All Types</option>
          <option value="hearing">⚖️ Hearing</option>
          <option value="meeting">🤝 Meeting</option>
          <option value="deadline">⏰ Deadline</option>
          <option value="task">✅ Task</option>
          <option value="personal">📌 Personal</option>
          <option value="other">📅 Other</option>
        </select>
        <select v-model="filters.status" @change="applyFilters"
          class="flex-1 min-w-[110px] px-3 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
          <option value="">All Status</option>
          <option value="scheduled">📅 Scheduled</option>
          <option value="completed">✅ Completed</option>
          <option value="cancelled">❌ Cancelled</option>
          <option value="rescheduled">🔄 Rescheduled</option>
        </select>
        <select v-model="filters.case_id" @change="applyFilters"
          class="flex-1 min-w-[150px] px-3 py-2.5 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
          <option value="">All Cases</option>
          <option v-for="caseItem in userCases" :key="caseItem.id" :value="caseItem.id">
            {{ caseItem.case_code }} - {{ caseItem.title }}
          </option>
        </select>
      </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-3 mb-4 flex flex-wrap items-center gap-4 text-xs">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>
        <span class="font-medium text-slate-700">Today</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-white border-2 border-emerald-500 flex items-center justify-center">
          <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <span class="font-medium text-slate-700">Can Add Events</span>
      </div>
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
          </svg>
        </div>
        <span class="font-medium text-slate-400">Past (View Only)</span>
      </div>
    </div>

    <!-- Calendar Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      
      <!-- Day headers -->
      <div class="grid grid-cols-7 border-b border-slate-100 bg-gradient-to-r from-[#1a4972]/5 to-[#2d6db5]/5">
        <div v-for="day in weekDays" :key="day" class="py-3 sm:py-4 text-center text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#1a4972]">
          <span class="hidden sm:inline">{{ day }}</span>
          <span class="sm:hidden">{{ day.substring(0, 1) }}</span>
        </div>
      </div>

      <!-- Calendar weeks -->
      <div class="grid grid-cols-7 divide-x divide-slate-100">
        <div v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex" class="contents">
          <div v-for="(day, dayIndex) in week" :key="dayIndex" 
            class="min-h-[90px] sm:min-h-[130px] p-1.5 sm:p-3 border-b border-slate-100 transition-all duration-200 relative group"
            :class="{
              'bg-gradient-to-br from-blue-50 to-blue-100 ring-2 ring-blue-500 ring-inset': day.isToday,
              'bg-white hover:bg-emerald-50/30 hover:ring-2 hover:ring-emerald-500 hover:ring-inset cursor-pointer hover:shadow-sm': !day.isPast && !day.isToday && day.isCurrentMonth,
              'bg-slate-50/80 cursor-not-allowed': day.isPast && !day.isToday,
              'opacity-40': !day.isCurrentMonth
            }"
            @click="selectDate(day.date)">
            
            <!-- Date Header -->
            <div class="flex items-center justify-between mb-1.5 sm:mb-2">
              <div class="flex items-center gap-1">
                <!-- TODAY Badge -->
                <div v-if="day.isToday" 
                  class="flex items-center gap-1 px-2 py-0.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full text-[10px] sm:text-xs font-bold shadow-sm">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  <span class="hidden sm:inline">TODAY</span>
                  <span class="sm:inline">{{ day.day }}</span>
                </div>
                
                <!-- Regular date -->
                <span v-else
                  class="text-xs sm:text-base font-bold px-1.5 sm:px-2 py-0.5 rounded-lg"
                  :class="{
                    'text-slate-900 bg-white': !day.isPast && !day.isToday && day.isCurrentMonth,
                    'text-slate-400': day.isPast && !day.isToday,
                    'text-slate-500': !day.isCurrentMonth
                  }">
                  {{ day.day }}
                </span>

                <!-- Future indicator -->
                <div v-if="!day.isPast && !day.isToday && day.isCurrentMonth"
                  class="hidden group-hover:flex items-center justify-center w-5 h-5 rounded-full bg-emerald-500 text-white">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                  </svg>
                </div>

                <!-- Past indicator -->
                <svg v-if="day.isPast && !day.isToday" class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>

              <!-- Event count badge -->
              <span v-if="day.events.length" 
                class="text-[9px] sm:text-xs font-bold px-1.5 sm:px-2 py-0.5 rounded-full"
                :class="{
                  'bg-blue-600 text-white': day.isToday,
                  'bg-[#1a4972] text-white': !day.isToday && !day.isPast,
                  'bg-slate-300 text-slate-600': day.isPast && !day.isToday
                }">
                {{ day.events.length }}
              </span>
            </div>
            
            <!-- Events for this day -->
            <div class="space-y-0.5 sm:space-y-1">
              <!-- Mobile: Show only first event -->
              <div class="sm:hidden">
                <div v-if="day.events.length > 0"
                  @click.stop="openDayEventsModal(day.date, day.events)"
                  class="text-[9px] p-1 rounded-lg cursor-pointer truncate hover:opacity-80 transition flex items-center gap-1 shadow-sm"
                  :class="{ 'opacity-50': day.isPast }"
                  :style="{ 
                    backgroundColor: getEventColor(day.events[0].type) + '30', 
                    color: getEventColor(day.events[0].type),
                    borderLeft: '3px solid ' + getEventColor(day.events[0].type) 
                  }">
                  <span>{{ getEventIcon(day.events[0].type) }}</span>
                  <span class="truncate font-medium">{{ day.events[0].title }}</span>
                </div>
              </div>

              <!-- Desktop: Show up to 3 events -->
              <div class="hidden sm:block space-y-1">
                <div v-for="event in day.events.slice(0, 3)" :key="event.id"
                  @click.stop="openViewModal(event)"
                  class="text-[10px] sm:text-xs p-1.5 rounded-lg cursor-pointer truncate hover:shadow-md transition-all flex items-center gap-1.5 font-medium"
                  :class="{
                    'opacity-50': day.isPast,
                    'line-through': event.status === 'cancelled'
                  }"
                  :style="{ 
                    backgroundColor: getEventColor(event.type) + '25', 
                    color: getEventColor(event.type),
                    borderLeft: '3px solid ' + getEventColor(event.type) 
                  }">
                  <span class="text-sm">{{ getEventIcon(event.type) }}</span>
                  <span class="truncate flex-1">{{ event.title }}</span>
                  <span v-if="event.status === 'cancelled'" class="text-[9px] px-1 py-0.5 rounded bg-red-100 text-red-600">✕</span>
                  <span v-if="event.status === 'completed'" class="text-[9px] px-1 py-0.5 rounded bg-green-100 text-green-600">✓</span>
                </div>
                
                <!-- Show "View All" if more than 3 events -->
                <div v-if="day.events.length > 3" 
                  @click.stop="openDayEventsModal(day.date, day.events)"
                  class="text-xs text-[#1a4972] font-semibold hover:underline cursor-pointer pl-1.5 py-1 flex items-center gap-1">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  {{ day.events.length - 3 }} more
                </div>
              </div>
            </div>

            <!-- Hover tooltip -->
            <div v-if="!day.isPast && !day.isToday && day.isCurrentMonth"
              class="absolute inset-x-0 bottom-0 bg-emerald-500 text-white text-[10px] font-semibold text-center py-1 opacity-0 group-hover:opacity-100 transition-opacity">
              Click to add event
            </div>

            <!-- Past date indicator -->
            <div v-if="day.isPast && !day.isToday"
              class="absolute inset-x-0 bottom-0 bg-slate-400 text-white text-[10px] font-medium text-center py-1 opacity-0 group-hover:opacity-100 transition-opacity">
              View only
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <EventModal
      :show="showModal"
      :mode="modalMode"
      :event="selectedEvent"
      :cases="userCases"
      :courts="lookups.courts"
      :users="lookups.users"
      :is-past="selectedEvent ? isPast(selectedEvent.hearing_date) : false"
      :can-edit="selectedEvent ? canEditEvent(selectedEvent) : false"
      @close="closeModal"
      @save="onEventSave"
      @status-change="onStatusChange"
      @switch-to-edit="switchToEditMode"
      @reschedule="onReschedule"
      @cancel="onCancel"
    />

    <!-- Day Events Modal -->
    <DayEventsModal
      :show="showDayModal"
      :date="selectedDate"
      :events="selectedDayEvents"
      @close="showDayModal = false"
      @view-event="openViewModal"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue';
import { useAuth } from '@/composables/useAuth';
import { useMasterData } from '@/composables/useMasterData';
import hearingService from '@/services/hearingService';
import caseService from '@/services/caseService';
import EventModal from '@/components/Calendar/EventModal.vue';
import DayEventsModal from '@/components/Calendar/DayEventsModal.vue';
import Swal from 'sweetalert2';

import {
  getHearings,
  getHearingStats,
  listenForUpdates,
  formatDate,
  getInitials,
  capitalize,
  isPast,
  isToday,
  getEventColor,
  getEventIcon,
  getStatusClass
} from '@/utils/appUtils';

// ========== AUTH ==========
const { user, getUserRole } = useAuth();
const { refreshClients } = useMasterData();

// ========== STATE ==========
const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const isRefreshing = ref(false);

// Data
const hearings = ref([]);
const stats = ref({
  today: 0,
  tomorrow: 0,
  this_week: 0,
  this_month: 0,
  upcoming: 0,
  past: 0,
  by_type: {}
});
const userCases = ref([]);
const lookups = ref({ courts: [], users: [] });

// Filters
const filters = reactive({
  type: '',
  status: '',
  case_id: '',
  personal_only: false
});

// Modals
const showModal = ref(false);
const modalMode = ref('add');
const selectedEvent = ref(null);
const showDayModal = ref(false);
const selectedDate = ref('');
const selectedDayEvents = ref([]);

// ========== COMPUTED ==========
const weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Get user role safely
const userRole = computed(() => {
  try {
    return getUserRole() || 'user';
  } catch (e) {
    return 'user';
  }
});

const isAdmin = computed(() => userRole.value === 'admin');
const isLawyer = computed(() => userRole.value === 'lawyer');
const isClerk = computed(() => userRole.value === 'clerk');

// Who can create events?
const canCreateEvents = computed(() => {
  return isAdmin.value || isLawyer.value || isClerk.value;
});

// Check if user can edit a specific event
const canEditEvent = (event) => {
  if (!event) return false;
  
  // Can't edit past events
  if (isPast(event.hearing_date)) return false;
  
  // Can only edit scheduled events
  if (event.status !== 'scheduled') return false;
  
  // Admin can edit anything
  if (isAdmin.value) return true;
  
  // User can edit if they created it
  if (event.created_by === user.value?.id) return true;
  
  // User can edit if assigned to it
  if (event.assigned_to === user.value?.id) return true;
  
  // Lawyer can edit events for their cases
  if (isLawyer.value && event.case_id) {
    return event.case?.assigned_lawyer_id === user.value?.id;
  }
  
  // Clerk can edit events for their cases
  if (isClerk.value && event.case_id) {
    return event.case?.assigned_clerk_id === user.value?.id;
  }
  
  return false;
};

// Check if user can delete a specific event
const canDeleteEvent = (event) => {
  return canEditEvent(event);
};

const calendarWeeks = computed(() => {
  const firstDay = new Date(currentYear.value, currentMonth.value, 1);
  const startDate = new Date(firstDay);
  startDate.setDate(startDate.getDate() - startDate.getDay());
  
  const weeks = [];
  let currentWeek = [];
  
  const hearingsList = Array.isArray(hearings.value) ? hearings.value : [];
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate);
    date.setDate(startDate.getDate() + i);
    
    // Create date string in YYYY-MM-DD format using local date parts
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}`;
    
    // Create date for comparison using local parts
    const dateForComparison = new Date(year, month - 1, day);
    dateForComparison.setHours(0, 0, 0, 0);
    const isPastDate = dateForComparison < today;
    
    // Filter events for this date - COMPARE STRINGS ONLY
    const events = hearingsList.filter(e => {
      if (!e || !e.hearing_date) return false;
      // hearing_date is already YYYY-MM-DD from API
      return e.hearing_date === dateStr;
    }).map(e => ({
      ...e,
      icon: getEventIcon(e.type),
      color: getEventColor(e.type)
    }));
    
    currentWeek.push({
      date: dateStr,
      day: date.getDate(),
      isToday: dateForComparison.getTime() === today.getTime(),
      isCurrentMonth: date.getMonth() === currentMonth.value,
      isPast: isPastDate,
      events
    });
    
    if (currentWeek.length === 7) {
      weeks.push(currentWeek);
      currentWeek = [];
    }
  }
  
  return weeks;
});

const currentMonthName = computed(() => {
  return new Date(currentYear.value, currentMonth.value).toLocaleString('default', { month: 'long' });
});

// ========== METHODS ==========
const fetchData = async (showRefresh = false) => {
  if (showRefresh) {
    isRefreshing.value = true;
  }
  
  try {
    const hearingsRes = await hearingService.getHearings({ 
      month: currentMonth.value + 1,
      year: currentYear.value,
      ...filters
    });
    
    if (hearingsRes.success) {
      hearings.value = Array.isArray(hearingsRes.data) ? hearingsRes.data : [];
    }
    
    const statsRes = await hearingService.getStats();
    if (statsRes.success) {
      stats.value = statsRes.data || stats.value;
    }
    
    const casesRes = await caseService.getCases({ per_page: 100 });
    if (casesRes.data) {
      userCases.value = Array.isArray(casesRes.data) ? casesRes.data : [];
    }
    
    const lookupsRes = await caseService.getLookups();
    if (lookupsRes.data) {
      lookups.value.courts = lookupsRes.data.courts || [];
      
      const lawyers = lookupsRes.data.lawyers || [];
      const clerks = lookupsRes.data.clerks || [];
      
      lookups.value.users = [
        ...lawyers.map(l => ({ id: l.id, full_name: l.full_name, role: 'lawyer' })),
        ...clerks.map(c => ({ id: c.id, full_name: c.full_name, role: 'clerk' }))
      ];
    }
    
  } catch (error) {
    if (showRefresh) {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to load calendar data',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    }
  } finally {
    if (showRefresh) {
      isRefreshing.value = false;
    }
  }
};

// Manual refresh (shows loading)
const manualRefresh = () => {
  fetchData(true);
};

// Apply filters (no loading)
const applyFilters = () => {
  fetchData(false);
};

// Month change (no loading)
const previousMonth = () => {
  if (currentMonth.value === 0) {
    currentMonth.value = 11;
    currentYear.value--;
  } else {
    currentMonth.value--;
  }
  fetchData(false);
};

const nextMonth = () => {
  if (currentMonth.value === 11) {
    currentMonth.value = 0;
    currentYear.value++;
  } else {
    currentMonth.value++;
  }
  fetchData(false);
};

const goToToday = () => {
  currentMonth.value = new Date().getMonth();
  currentYear.value = new Date().getFullYear();
  fetchData(false);
};

// Quick create when clicking on a date - FIXED
const selectDate = (date) => {
  // Parse the date string parts
  const [year, month, day] = date.split('-').map(Number);
  
  // Create date in local timezone
  const selectedDateObj = new Date(year, month - 1, day);
  selectedDateObj.setHours(0, 0, 0, 0);
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  
  if (selectedDateObj < today) {
    // If past date, show view-only mode
    const dayEvents = hearings.value.filter(e => {
      return e.hearing_date === date;
    });
    
    if (dayEvents.length > 0) {
      openDayEventsModal(date, dayEvents);
    } else {
      Swal.fire({
        icon: 'info',
        title: 'Past Date',
        text: 'Cannot create events in the past',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    }
  } else {
    // Future date - quick create with selected date
    modalMode.value = 'add';
    selectedEvent.value = { 
      hearing_date: date
    };
    showModal.value = true;
  }
};

const openDayEventsModal = (date, events) => {
  selectedDate.value = date;
  selectedDayEvents.value = events;
  showDayModal.value = true;
};

// New Event button
const openCreateModal = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  const formattedDate = `${year}-${month}-${day}`;
  
  modalMode.value = 'add';
  selectedEvent.value = { 
    hearing_date: formattedDate 
  };
  showModal.value = true;
};

const openViewModal = (event) => {
  modalMode.value = 'view';
  selectedEvent.value = event;
  showModal.value = true;
};

const switchToEditMode = () => {
  modalMode.value = 'edit';
};

const closeModal = () => {
  showModal.value = false;
  selectedEvent.value = null;
};

const onEventSave = async ({ mode, data }) => {
  try {
    if (isPast(data.hearing_date)) {
      Swal.fire({
        icon: 'error',
        title: 'Invalid Date',
        text: 'Cannot create events in the past'
      });
      return;
    }
    
    if (mode === 'add') {
      await hearingService.createHearing(data);
      Swal.fire({
        icon: 'success',
        title: 'Created!',
        text: 'Event created successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    } else {
      await hearingService.updateHearing(data.id, data);
      Swal.fire({
        icon: 'success',
        title: 'Updated!',
        text: 'Event updated successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
    }
    closeModal();
    await fetchData(false);
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to save event'
    });
  }
};

const onStatusChange = async ({ id, status }) => {
  try {
    await hearingService.updateStatus(id, status);
    Swal.fire({
      icon: 'success',
      title: 'Updated!',
      text: `Event marked as ${status}`,
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
    await fetchData(false);
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to update status'
    });
  }
};

const onReschedule = async ({ id, new_date, new_time, reason }) => {
  try {
    const response = await hearingService.rescheduleHearing(id, {
      new_date,
      new_time,
      reason
    });
    
    if (response.success) {
      Swal.fire({
        icon: 'success',
        title: 'Rescheduled!',
        text: 'Hearing rescheduled successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
      closeModal();
      await fetchData(false);
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to reschedule hearing'
    });
  }
};

const onCancel = async ({ id, reason }) => {
  try {
    const response = await hearingService.cancelHearing(id, { reason });
    
    if (response.success) {
      Swal.fire({
        icon: 'success',
        title: 'Cancelled!',
        text: 'Hearing cancelled successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
      closeModal();
      await fetchData(false);
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message || 'Failed to cancel hearing'
    });
  }
};

// ========== EVENT LISTENERS ==========
const handleHearingsUpdated = (event) => {
  hearings.value = Array.isArray(event.detail) ? event.detail : [];
};

const handleStatsUpdated = (event) => {
  stats.value = event.detail || stats.value;
};

let cleanupHearings = null;
let cleanupStats = null;

// ========== LIFECYCLE ==========
onMounted(async () => {
  await fetchData(false);
  
  cleanupHearings = listenForUpdates('hearings-updated', handleHearingsUpdated);
  cleanupStats = listenForUpdates('hearing-stats-updated', handleStatsUpdated);
});

onUnmounted(() => {
  if (cleanupHearings) cleanupHearings();
  if (cleanupStats) cleanupStats();
});

// ========== WATCHERS ==========
watch([currentMonth, currentYear], () => {
  fetchData(false);
});

watch(filters, () => {
  fetchData(false);
}, { deep: true });
</script>

<style scoped>
.animate-slide-down {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>