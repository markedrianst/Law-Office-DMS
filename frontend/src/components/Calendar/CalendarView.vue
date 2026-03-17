<template>
  <div class="min-h-screen p-3 sm:p-6 bg-slate-50 font-sans">
    <!-- Silent refresh indicator - only shows during manual refresh -->
    <div
      v-if="isRefreshing"
      class="fixed top-4 right-4 z-50 flex items-center gap-2 px-3 py-1.5 bg-white/90 backdrop-blur-sm rounded-full shadow-lg border border-blue-100 animate-slide-down"
    >
      <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
      <span class="text-xs font-medium text-blue-600">Syncing...</span>
    </div>

    <!-- Header Section - Matching User Management -->
    <div class="mb-4 sm:mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1a4972]">Calendar</h1>
      </div>
      <p class="text-xs sm:text-sm ml-4 pl-3 text-slate-500">Manage hearings, meetings, and deadlines</p>
    </div>

    <!-- Filters Bar - Responsive -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-3 sm:p-4 mb-4">
      <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:gap-3">
        <!-- Month Navigation -->
        <div class="flex items-center gap-2 bg-slate-50 rounded-xl border border-slate-200 p-1 flex-shrink-0">
          <button @click="previousMonth" class="p-2 hover:bg-white rounded-lg transition">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <span class="text-xs sm:text-sm font-semibold text-slate-700 px-2 whitespace-nowrap">{{ currentMonthName }} {{ currentYear }}</span>
          <button @click="nextMonth" class="p-2 hover:bg-white rounded-lg transition">
            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <button @click="goToToday" class="px-2 sm:px-3 py-1 text-xs font-medium bg-white border border-slate-200 rounded-lg hover:bg-slate-50">
            Today
          </button>
        </div>

        <!-- Type Filter -->
        <select v-model="filters.type" @change="applyFilters"
          class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer flex-1 sm:flex-initial sm:min-w-[120px]">
          <option value="">All Types</option>
          <option value="hearing">⚖️ Hearing</option>
          <option value="meeting">🤝 Meeting</option>
          <option value="deadline">⏰ Deadline</option>
          <option value="task">✅ Task</option>
          <option value="personal">📌 Personal</option>
          <option value="other">📅 Other</option>
        </select>

        <!-- Status Filter -->
        <select v-model="filters.status" @change="applyFilters"
          class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer flex-1 sm:flex-initial sm:min-w-[120px]">
          <option value="">All Status</option>
          <option value="scheduled">📅 Scheduled</option>
          <option value="completed">✅ Completed</option>
          <option value="cancelled">❌ Cancelled</option>
          <option value="rescheduled">🔄 Rescheduled</option>
        </select>

        <!-- Case Filter -->
        <select v-model="filters.case_id" @change="applyFilters"
          class="px-3 sm:px-4 py-2 text-xs sm:text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer flex-1 sm:flex-initial sm:min-w-[200px]">
          <option value="">All Cases</option>
          <option v-for="caseItem in userCases" :key="caseItem.id" :value="caseItem.id">
            {{ caseItem.case_code }} - {{ caseItem.title }}
          </option>
        </select>

        <!-- Personal Only Checkbox -->
        <label class="flex items-center gap-2 px-3 py-2 bg-slate-50 rounded-xl cursor-pointer flex-shrink-0">
          <input type="checkbox" v-model="filters.personal_only" @change="applyFilters" class="rounded border-slate-300 text-[#1a4972]">
          <span class="text-xs sm:text-sm text-slate-600 whitespace-nowrap">Personal only</span>
        </label>

        <!-- Action Buttons - Responsive -->
        <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0 w-full sm:w-auto sm:ml-auto">
          <!-- Refresh Button -->
          <button @click="manualRefresh" :disabled="isRefreshing"
            class="flex-1 sm:flex-initial px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold inline-flex items-center justify-center transition-all duration-200 whitespace-nowrap hover:shadow-lg active:scale-95 disabled:opacity-50 bg-white text-[#1a4972] border border-[#1a4972]/30 hover:bg-[#1a4972]/5">
            <svg v-if="isRefreshing" class="animate-spin w-3 h-3 sm:w-4 sm:h-4 mr-2" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <svg v-else class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="hidden sm:inline">Refresh</span>
            <span class="sm:hidden">Sync</span>
          </button>

          <!-- New Event Button -->
          <button v-if="canCreateEvents" @click="openCreateModal"
            class="flex-1 sm:flex-initial px-4 sm:px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold inline-flex items-center justify-center transition-all duration-200 whitespace-nowrap hover:shadow-lg active:scale-95 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white shadow-md shadow-[#1a4972]/30">
            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="hidden sm:inline">New Event</span>
            <span class="sm:hidden">New</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Calendar Table - Responsive -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      
      <!-- Day headers -->
      <div class="grid grid-cols-7 border-b border-slate-100 bg-[#1a4972]/5">
        <div v-for="day in weekDays" :key="day" class="py-2 sm:py-3 text-center text-[10px] sm:text-xs font-semibold uppercase tracking-wider text-slate-500">
          <span class="hidden sm:inline">{{ day }}</span>
          <span class="sm:hidden">{{ day.substring(0, 1) }}</span>
        </div>
      </div>

      <!-- Calendar weeks -->
      <div class="grid grid-cols-7 divide-x divide-slate-100">
        <div v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex" class="contents">
          <div v-for="(day, dayIndex) in week" :key="dayIndex" 
            class="min-h-[80px] sm:min-h-[120px] p-1 sm:p-2 border-b border-slate-100 cursor-pointer transition-all hover:bg-blue-50/30"
            :class="{
              'bg-blue-50/30': day.isToday,
              'bg-slate-50/50': !day.isCurrentMonth,
              'opacity-60': day.isPast && !day.isToday
            }"
            @click="selectDate(day.date)">
            
            <div class="flex items-center justify-between mb-1">
              <span class="text-xs sm:text-sm font-medium"
                :class="{
                  'text-[#1a4972] font-bold': day.isToday,
                  'text-slate-400': !day.isCurrentMonth,
                  'text-slate-700': day.isCurrentMonth && !day.isToday
                }">
                {{ day.day }}
              </span>
              <span v-if="day.events.length" class="text-[9px] sm:text-xs bg-[#1a4972] text-white px-1 sm:px-1.5 py-0.5 rounded-full">
                {{ day.events.length }}
              </span>
            </div>
            
            <!-- Events for this day -->
            <div class="space-y-0.5 sm:space-y-1">
              <!-- Mobile: Show only count with first event icon -->
              <div class="sm:hidden">
                <div v-if="day.events.length > 0"
                  @click.stop="openDayEventsModal(day.date, day.events)"
                  class="text-[9px] p-1 rounded cursor-pointer truncate hover:opacity-80 transition flex items-center gap-1"
                  :style="{ 
                    backgroundColor: getEventColor(day.events[0].type) + '20', 
                    color: getEventColor(day.events[0].type),
                    borderLeft: '2px solid ' + getEventColor(day.events[0].type) 
                  }">
                  <span>{{ getEventIcon(day.events[0].type) }}</span>
                  <span class="truncate">{{ day.events[0].title }}</span>
                </div>
              </div>

              <!-- Desktop: Show up to 3 events -->
              <div class="hidden sm:block space-y-1">
                <div v-for="event in day.events.slice(0, 3)" :key="event.id"
                  @click.stop="openViewModal(event)"
                  class="text-xs p-1 rounded cursor-pointer truncate hover:opacity-80 transition flex items-center gap-1"
                  :class="{
                    'opacity-60': day.isPast,
                    'line-through': event.status === 'cancelled'
                  }"
                  :style="{ 
                    backgroundColor: getEventColor(event.type) + '20', 
                    color: getEventColor(event.type),
                    borderLeft: '3px solid ' + getEventColor(event.type) 
                  }">
                  <span>{{ getEventIcon(event.type) }}</span>
                  <span class="truncate">{{ event.title }}</span>
                  <span v-if="event.status === 'cancelled'" class="ml-1 text-red-600 text-[9px]">(Cancelled)</span>
                  <span v-if="event.status === 'rescheduled'" class="ml-1 text-blue-600 text-[9px]">(Rescheduled)</span>
                  <span v-if="event.status === 'completed'" class="ml-1 text-green-600 text-[9px]">(Completed)</span>
                </div>
                
                <!-- Show "View All" if more than 3 events -->
                <div v-if="day.events.length > 3" 
                  @click.stop="openDayEventsModal(day.date, day.events)"
                  class="text-xs text-[#1a4972] font-medium hover:underline cursor-pointer pl-1">
                  +{{ day.events.length - 3 }} more
                </div>
              </div>
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
import EventModal from './EventModal.vue';
import DayEventsModal from './DayEventsModal.vue';
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
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

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
    
    const dateStr = date.toISOString().split('T')[0];
    const isPastDate = date < today;
    
    const events = hearingsList.filter(e => {
      if (!e || !e.hearing_date) return false;
      const eventDate = e.hearing_date.split('T')[0];
      return eventDate === dateStr;
    }).map(e => ({
      ...e,
      icon: getEventIcon(e.type),
      color: getEventColor(e.type)
    }));
    
    currentWeek.push({
      date: dateStr,
      day: date.getDate(),
      isToday: date.toDateString() === today.toDateString(),
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

// Quick create when clicking on a date
const selectDate = (date) => {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const selectedDateObj = new Date(date);
  
  if (selectedDateObj < today) {
    // If past date, show view-only mode
    const dayEvents = hearings.value.filter(e => {
      const eventDate = e.hearing_date.split('T')[0];
      return eventDate === date;
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
    // Future date - quick create
    modalMode.value = 'add';
    selectedEvent.value = { hearing_date: date };
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
  today.setHours(0, 0, 0, 0);
  
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

const openEditModal = (event) => {
  if (!canEditEvent(event)) {
    Swal.fire({
      icon: 'warning',
      title: 'Cannot Edit',
      text: 'You do not have permission to edit this event',
      timer: 2000,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
    return;
  }
  
  modalMode.value = 'edit';
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

const confirmDelete = async (event) => {
  if (!canDeleteEvent(event)) {
    Swal.fire({
      icon: 'warning',
      title: 'Cannot Delete',
      text: 'You do not have permission to delete this event',
      timer: 2000,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
    return;
  }
  
  const result = await Swal.fire({
    title: 'Delete Event?',
    text: `Are you sure you want to delete "${event.title}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    try {
      await hearingService.deleteHearing(event.id);
      Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'Event deleted successfully',
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
        text: error.message || 'Failed to delete event'
      });
    }
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