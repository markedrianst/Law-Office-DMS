<!-- src/components/Calendar/DayEventsModal.vue -->
<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="$emit('close')">
      <div class="relative bg-white w-full max-w-2xl max-h-[80vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#1a4972]/10 flex items-center justify-center">
              <svg class="w-5 h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-slate-800">{{ formattedDate }}</h2>
              <p class="text-sm text-slate-500">{{ events.length }} event(s) scheduled</p>
            </div>
          </div>
          <button @click="$emit('close')" class="p-2 hover:bg-slate-100 rounded-xl text-slate-400 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto p-4">
          <div class="space-y-3">
            <div v-for="event in sortedEvents" :key="event.id" 
              class="p-4 rounded-xl border cursor-pointer transition-all hover:shadow-md"
              :class="{
                'bg-white border-slate-200 hover:border-[#1a4972]': !isPastDate(event.hearing_date),
                'bg-slate-50 border-slate-200 opacity-75': isPastDate(event.hearing_date)
              }"
              @click="openEvent(event)">
              
              <div class="flex items-start gap-3">
                <!-- Time/Icon -->
                <div class="w-16 text-center">
                  <span class="text-sm font-semibold" :style="{ color: getEventColor(event.type) }">
                    {{ event.formatted_start_time || 'All day' }}
                  </span>
                </div>
                
                <!-- Event Details -->
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="text-lg">{{ getEventIcon(event.type) }}</span>
                    <span class="text-sm font-semibold text-slate-800">{{ event.title }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full" :class="getStatusClass(event.status)">
                      {{ event.status }}
                    </span>
                  </div>
                  
                  <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span v-if="event.case_code" class="text-[#1a4972] font-medium">
                      {{ event.case_code }}
                    </span>
                    <span v-else class="italic">Personal</span>
                    
                    <span v-if="event.location">📍 {{ event.location }}</span>
                    <span v-if="event.assigned_to_name">👤 {{ event.assigned_to_name }}</span>
                  </div>
                </div>
                
                <!-- Past/Active Indicator -->
                <div v-if="isPastDate(event.hearing_date)" class="text-xs text-slate-400">
                  Past event
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end px-6 py-3 border-t border-slate-100 bg-slate-50">
          <button @click="$emit('close')" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-200 rounded-lg transition">
            Close
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { 
  isPastDate, 
  getEventColor, 
  getEventIcon, 
  getStatusClass,
  formatDate 
} from '@/utils/appUtils';

const props = defineProps({
  show: Boolean,
  date: String,
  events: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['close', 'view-event']);

const formattedDate = computed(() => {
  if (!props.date) return '';
  return new Date(props.date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
});

const sortedEvents = computed(() => {
  return [...props.events].sort((a, b) => {
    if (!a.start_time) return -1;
    if (!b.start_time) return 1;
    return a.start_time.localeCompare(b.start_time);
  });
});

const openEvent = (event) => {
  emit('view-event', event);
  emit('close');
};
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: all 0.25s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>