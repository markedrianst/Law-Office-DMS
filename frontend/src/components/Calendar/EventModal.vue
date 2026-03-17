<!-- src/components/Calendar/EventModal.vue -->
<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="$emit('close')">
      <div class="relative bg-white w-full max-w-2xl max-h-[90vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden">
        
        <!-- Header with dynamic styling based on mode -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100"
          :class="{
            'bg-gradient-to-r from-[#1a4972]/5 to-transparent': mode === 'add',
            'bg-amber-50/50': mode === 'edit',
            'bg-emerald-50/50': mode === 'view'
          }">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
              :class="{
                'bg-[#1a4972]/10': mode === 'add',
                'bg-amber-100': mode === 'edit',
                'bg-emerald-100': mode === 'view'
              }">
              <!-- Add Mode Icon -->
              <svg v-if="mode === 'add'" class="w-5 h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
              </svg>
              
              <!-- Edit Mode Icon -->
              <svg v-else-if="mode === 'edit'" class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              
              <!-- View Mode Icon -->
              <svg v-else class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-slate-800">
                {{ mode === 'add' ? 'Create New Event' : mode === 'edit' ? 'Edit Event' : 'Event Details' }}
              </h2>
              <p class="text-sm text-slate-500">
                {{ mode === 'add' ? 'Schedule a hearing, meeting, or task' : mode === 'edit' ? 'Update event information' : 'View event details' }}
              </p>
            </div>
          </div>
          <button @click="$emit('close')" class="p-2 hover:bg-slate-100 rounded-xl text-slate-400 transition hover:scale-110">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
          
          <!-- ========== VIEW MODE ========== -->
          <template v-if="mode === 'view'">
            <div class="space-y-4">
              
              <!-- Status Banner -->
              <div v-if="isPast" class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-amber-800">Past Event</p>
                  <p class="text-xs text-amber-600">This event has already passed and is view-only</p>
                </div>
              </div>

              <div v-if="localEvent.status === 'cancelled'" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-red-800">Cancelled</p>
                  <p v-if="localEvent.metadata?.cancellation_reason" class="text-xs text-red-600">
                    Reason: {{ localEvent.metadata.cancellation_reason }}
                  </p>
                </div>
              </div>

              <div v-if="localEvent.status === 'rescheduled'" class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-blue-800">Rescheduled</p>
                  <p v-if="localEvent.reschedule_reason" class="text-xs text-blue-600">
                    Reason: {{ localEvent.reschedule_reason }}
                  </p>
                </div>
              </div>

              <div v-if="localEvent.status === 'completed'" class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                  <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-green-800">Completed</p>
                  <p class="text-xs text-green-600">This event has been marked as completed</p>
                </div>
              </div>

              <!-- Event Details Card -->
              <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <!-- Title Section -->
                <div class="p-5 border-b border-slate-100">
                  <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Title</p>
                  <p class="text-xl font-bold text-slate-800">{{ localEvent.title }}</p>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-2 divide-x divide-slate-100">
                  <div class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Type</p>
                    <div class="flex items-center gap-2">
                      <span class="text-xl">{{ getEventIcon(localEvent.type) }}</span>
                      <span class="text-sm font-medium text-slate-700">{{ capitalize(localEvent.type) }}</span>
                    </div>
                  </div>
                  <div class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Status</p>
                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-full" :class="getStatusClass(localEvent.status)">
                      {{ capitalize(localEvent.status) }}
                    </span>
                  </div>
                </div>

                <div class="grid grid-cols-2 divide-x divide-slate-100 border-t border-slate-100">
                  <div class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Date</p>
                    <p class="text-sm font-medium text-slate-700">{{ formatDisplayDate(localEvent.hearing_date) }}</p>
                  </div>
                  <div class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Time</p>
                    <p class="text-sm font-medium text-slate-700">{{ formatTime(localEvent.start_time) || 'All day' }}</p>
                  </div>
                </div>

                <!-- Additional Info -->
                <div class="border-t border-slate-100 divide-y divide-slate-100">
                  <div v-if="localEvent.case" class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Case</p>
                    <p class="text-sm font-medium text-[#1a4972]">
                      {{ localEvent.case.case_code }} - {{ localEvent.case.title }}
                    </p>
                  </div>
                  <div v-else class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Case</p>
                    <p class="text-sm text-slate-400 italic">Personal event</p>
                  </div>

                  <div v-if="localEvent.location" class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Location</p>
                    <p class="text-sm text-slate-700">{{ localEvent.location }}</p>
                  </div>

                  <div v-if="localEvent.description" class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Description</p>
                    <p class="text-sm text-slate-600 bg-slate-50 p-3 rounded-lg">{{ localEvent.description }}</p>
                  </div>

                  <div v-if="localEvent.assignedTo" class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Assigned To</p>
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-full bg-gradient-to-br from-[#1a4972] to-[#2d6db5] flex items-center justify-center text-xs font-bold text-white">
                        {{ getInitials(localEvent.assignedTo.full_name) }}
                      </div>
                      <span class="text-sm text-slate-700">{{ localEvent.assignedTo.full_name }}</span>
                    </div>
                  </div>

                  <div class="p-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Created By</p>
                    <p class="text-sm text-slate-700">{{ localEvent.creator?.full_name || 'Unknown' }}</p>
                  </div>
                </div>

                <!-- Reschedule Info -->
                <div v-if="localEvent.rescheduled_from_id" class="bg-amber-50 p-4 border-t border-amber-200">
                  <div class="flex items-start gap-2">
                    <svg class="w-4 h-4 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <div>
                      <p class="text-xs text-amber-800 font-medium">
                        Rescheduled from {{ formatDisplayDate(localEvent.rescheduled_from?.hearing_date) }}
                      </p>
                      <p v-if="localEvent.reschedule_reason" class="text-xs text-amber-600 mt-1">
                        Reason: {{ localEvent.reschedule_reason }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- ========== ADD/EDIT MODE ========== -->
          <template v-else>
            <form @submit.prevent="handleSubmit" class="space-y-5">
              
              <!-- Past Date Warning -->
              <div v-if="form.hearing_date && isPastDate(form.hearing_date)" class="bg-red-50 border border-red-200 rounded-xl p-3 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span class="text-sm text-red-600">Cannot create/edit events in the past</span>
              </div>

              <!-- Title Field -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  Title <span class="text-red-500">*</span>
                </label>
                <input 
                  v-model="form.title" 
                  @input="clearFieldError('title')"
                  type="text" 
                  placeholder="e.g. Preliminary Hearing"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                  :class="{ 'border-red-400': errors.title }"
                  :disabled="form.hearing_date && isPastDate(form.hearing_date)" />
                <p v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</p>
              </div>

              <!-- Type & Status Grid -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Type <span class="text-red-500">*</span>
                  </label>
                  <select 
                    v-model="form.type" 
                    @change="clearFieldError('type')"
                    class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                    :disabled="form.hearing_date && isPastDate(form.hearing_date)">
                    <option value="hearing">⚖️ Hearing</option>
                    <option value="meeting">🤝 Meeting</option>
                    <option value="deadline">⏰ Deadline</option>
                    <option value="task">✅ Task</option>
                    <option value="personal">📌 Personal</option>
                    <option value="other">📅 Other</option>
                  </select>
                </div>

                <!-- Status (Edit only) -->
                <div v-if="mode === 'edit'">
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status</label>
                  <select 
                    v-model="form.status" 
                    @change="clearFieldError('status')"
                    class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                    :disabled="form.hearing_date && isPastDate(form.hearing_date)">
                    <option value="scheduled">📅 Scheduled</option>
                    <option value="completed">✅ Completed</option>
                    <option value="cancelled">❌ Cancelled</option>
                    <option value="rescheduled">🔄 Rescheduled</option>
                  </select>
                </div>
              </div>

              <!-- Date & Time Grid -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Date <span class="text-red-500">*</span>
                  </label>
                  <input 
                    v-model="form.hearing_date" 
                    @input="clearFieldError('hearing_date')"
                    type="date"
                    :min="today"
                    class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                    :class="{ 'border-red-400': errors.hearing_date }"
                    :disabled="form.hearing_date && isPastDate(form.hearing_date)" />
                  <p v-if="errors.hearing_date" class="text-xs text-red-500 mt-1">{{ errors.hearing_date }}</p>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Time</label>
                  <input 
                    v-model="form.start_time" 
                    @input="clearFieldError('start_time')"
                    type="time"
                    class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                    :disabled="form.hearing_date && isPastDate(form.hearing_date)" />
                  <p class="text-xs text-slate-400 mt-1">Leave empty for all-day event</p>
                </div>
              </div>

              <!-- Case Selection -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Link to Case</label>
                <select 
                  v-model="form.case_id" 
                  @change="clearFieldError('case_id')"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                  :disabled="form.hearing_date && isPastDate(form.hearing_date)">
                  <option value="">— Personal Event (No Case) —</option>
                  <option v-for="caseItem in cases" :key="caseItem.id" :value="caseItem.id">
                    {{ caseItem.case_code }} - {{ caseItem.title }}
                  </option>
                </select>
              </div>

              <!-- Location & Court Grid -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Location</label>
                  <input 
                    v-model="form.location" 
                    @input="clearFieldError('location')"
                    type="text" 
                    placeholder="e.g. Courtroom 3"
                    class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                    :disabled="form.hearing_date && isPastDate(form.hearing_date)" />
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Court</label>
                  <select 
                    v-model="form.court_id" 
                    @change="clearFieldError('court_id')"
                    class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                    :disabled="form.hearing_date && isPastDate(form.hearing_date)">
                    <option value="">— Select Court —</option>
                    <option v-for="court in courts" :key="court.id" :value="court.id">
                      {{ court.name }}
                    </option>
                  </select>
                </div>
              </div>

              <!-- Assignment -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Assign To</label>
                <select 
                  v-model="form.assigned_to" 
                  @change="clearFieldError('assigned_to')"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 transition"
                  :disabled="form.hearing_date && isPastDate(form.hearing_date)">
                  <option value="">— Unassigned —</option>
                  <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                    {{ user.full_name }} ({{ user.role }})
                  </option>
                </select>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Description</label>
                <textarea 
                  v-model="form.description" 
                  @input="clearFieldError('description')"
                  rows="4" 
                  placeholder="Additional details..."
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 resize-none transition"
                  :disabled="form.hearing_date && isPastDate(form.hearing_date)"></textarea>
              </div>
            </form>
          </template>
        </div>

        <!-- Footer with Actions -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
          
          <!-- Cancel/Close Button -->
          <button @click="$emit('close')"
            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:scale-105 transition-all duration-200">
            {{ mode === 'view' ? 'Close' : 'Cancel' }}
          </button>
          
          <!-- View Mode Action Buttons -->
          <template v-if="mode === 'view'">
            <div class="flex items-center gap-2">
              
              <!-- Reschedule Button -->
              <button v-if="!isPast && localEvent.status === 'scheduled' && canEdit" 
                @click="openRescheduleModal"
                class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-500 rounded-xl hover:bg-blue-600 hover:scale-105 transition-all duration-200 flex items-center gap-2 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Reschedule
              </button>
              
              <!-- Cancel Button -->
              <button v-if="!isPast && localEvent.status === 'scheduled' && canEdit" 
                @click="openCancelModal"
                class="px-4 py-2.5 text-sm font-semibold text-white bg-red-500 rounded-xl hover:bg-red-600 hover:scale-105 transition-all duration-200 flex items-center gap-2 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Cancel
              </button>
              
              <!-- Complete Button -->
              <button v-if="!isPast && localEvent.status === 'scheduled' && canEdit" 
                @click="() => $emit('status-change', { id: localEvent.id, status: 'completed' })"
                class="px-4 py-2.5 text-sm font-semibold text-white bg-green-500 rounded-xl hover:bg-green-600 hover:scale-105 transition-all duration-200 flex items-center gap-2 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Complete
              </button>
              
              <!-- Edit Button -->
              <button v-if="!isPast && canEdit && localEvent.status === 'scheduled'" 
                @click="$emit('switch-to-edit')"
                class="px-4 py-2.5 text-sm font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 hover:scale-105 transition-all duration-200 flex items-center gap-2 shadow-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
              </button>
            </div>
          </template>
          
          <!-- Add/Edit Mode Save Button -->
          <button v-else-if="mode !== 'view' && (!form.hearing_date || !isPastDate(form.hearing_date))" 
            @click="handleSubmit"
            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 min-w-[120px] justify-center bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] hover:from-[#1e5780] hover:to-[#1a4972] hover:scale-105 hover:shadow-lg transition-all duration-200"
            :disabled="!form.hearing_date || isPastDate(form.hearing_date)">
            <svg v-if="formLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ mode === 'add' ? 'Create Event' : 'Save Changes' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import Swal from 'sweetalert2';
import { 
  formatDate, 
  capitalize, 
  isPastDate,
  getEventIcon,
  getStatusClass,
  getInitials
} from '@/utils/appUtils';
import { useAuth } from '@/composables/useAuth';

const props = defineProps({
  show: Boolean,
  mode: String,
  event: Object,
  cases: Array,
  courts: Array,
  users: Array,
  isPast: Boolean,
  canEdit: { type: Boolean, default: false },
  formLoading: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'save', 'status-change', 'switch-to-edit', 'reschedule', 'cancel']);

const { user } = useAuth();

// Form state
const form = reactive({
  id: null,
  title: '',
  type: 'hearing',
  status: 'scheduled',
  hearing_date: '',
  start_time: '',
  case_id: '',
  location: '',
  court_id: '',
  assigned_to: '',
  description: '',
  reschedule_reason: ''
});

const errors = ref({});

// Computed today date in YYYY-MM-DD format
const today = computed(() => {
  const date = new Date();
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
});

// Check if user can edit this event
const canEdit = computed(() => {
  if (props.canEdit !== undefined) return props.canEdit;
  
  if (!props.event) return false;
  if (props.isPast) return false;
  if (props.event.status !== 'scheduled') return false;
  
  const userRole = user.value?.role?.toLowerCase();
  const userId = user.value?.id;
  
  if (userRole === 'admin') return true;
  if (props.event.created_by === userId) return true;
  if (props.event.assigned_to === userId) return true;
  
  if (userRole === 'lawyer' && props.event.case_id) {
    return props.event.case?.assigned_lawyer_id === userId;
  }
  
  if (userRole === 'clerk' && props.event.case_id) {
    return props.event.case?.assigned_clerk_id === userId;
  }
  
  return false;
});

// Available users for assignment
const availableUsers = computed(() => {
  if (!props.users || !Array.isArray(props.users)) return [];
  
  return props.users.filter(user => 
    user.role?.toLowerCase() === 'lawyer' || 
    user.role?.toLowerCase() === 'clerk'
  ).map(user => ({
    id: user.id,
    full_name: user.full_name,
    role: user.role
  }));
});

const localEvent = computed(() => props.event || {});

// Helper to format date for display
const formatDisplayDate = (date) => {
  if (!date) return '—';
  const d = new Date(date);
  if (isNaN(d.getTime())) return date;
  return d.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Helper to format time for display
const formatTime = (time) => {
  if (!time) return null;
  if (time.includes(':')) {
    const parts = time.split(':');
    return `${parts[0]}:${parts[1]}`;
  }
  return time;
};

// Clear field error
const clearFieldError = (field) => {
  if (errors.value[field]) {
    errors.value = { ...errors.value, [field]: '' };
  }
};

// Validate form
const validateForm = () => {
  const newErrors = {};
  
  if (!form.title) newErrors.title = 'Title is required';
  if (!form.hearing_date) newErrors.hearing_date = 'Date is required';
  if (form.hearing_date && isPastDate(form.hearing_date)) newErrors.hearing_date = 'Cannot select past date';
  
  errors.value = newErrors;
  return Object.keys(newErrors).length === 0;
};

const handleSubmit = () => {
  if (!validateForm()) return;
  
  const payload = {
    ...form,
    case_id: form.case_id || null,
    court_id: form.court_id || null,
    assigned_to: form.assigned_to || null
  };
  
  emit('save', { mode: props.mode, data: payload });
};

// Enhanced Reschedule Modal
const openRescheduleModal = async () => {
  const currentDate = new Date(localEvent.value.hearing_date);
  const year = currentDate.getFullYear();
  const month = String(currentDate.getMonth() + 1).padStart(2, '0');
  const day = String(currentDate.getDate()).padStart(2, '0');
  const formattedDate = `${year}-${month}-${day}`;
  
  const { value: formValues } = await Swal.fire({
    title: '<span class="text-xl font-bold text-slate-800">Reschedule Hearing</span>',
    html: `
      <div class="space-y-4 text-left p-2">
        <div class="bg-blue-50 p-4 rounded-lg mb-4">
          <div class="flex items-center gap-2 text-blue-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="font-medium">Current: ${formatDisplayDate(localEvent.value.hearing_date)} ${localEvent.value.start_time ? 'at ' + localEvent.value.start_time : ''}</span>
          </div>
        </div>
        
        <div class="space-y-3">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Date <span class="text-red-500">*</span></label>
            <input type="date" id="new_date" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10" value="${formattedDate}" min="${today.value}">
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Time <span class="text-slate-400 text-xs">(Optional)</span></label>
            <input type="time" id="new_time" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10" value="${localEvent.value.start_time || ''}">
            <p class="text-xs text-slate-400 mt-1">Leave empty for all-day event</p>
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Reason for Rescheduling <span class="text-red-500">*</span></label>
            <textarea id="reason" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 resize-none" rows="3" placeholder="Please provide a reason..."></textarea>
          </div>
        </div>
      </div>
    `,
    focusConfirm: false,
    showCancelButton: true,
    confirmButtonText: 'Reschedule',
    cancelButtonText: 'Cancel',
    confirmButtonColor: '#1a4972',
    customClass: {
      confirmButton: 'px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] rounded-xl hover:shadow-lg transition-all ml-2',
      cancelButton: 'px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all',
      popup: 'rounded-2xl p-6'
    },
    preConfirm: () => {
      const new_date = document.getElementById('new_date').value;
      const new_time = document.getElementById('new_time').value;
      const reason = document.getElementById('reason').value;
      
      if (!new_date) {
        Swal.showValidationMessage('New date is required');
        return false;
      }
      if (!reason) {
        Swal.showValidationMessage('Reason is required');
        return false;
      }
      
      return { new_date, new_time: new_time || null, reason };
    }
  });
  
  if (formValues) {
    emit('reschedule', {
      id: localEvent.value.id,
      new_date: formValues.new_date,
      new_time: formValues.new_time,
      reason: formValues.reason
    });
  }
};

// Enhanced Cancel Modal
const openCancelModal = async () => {
  const { value: reason } = await Swal.fire({
    title: '<span class="text-xl font-bold text-slate-800">Cancel Hearing</span>',
    html: `
      <div class="space-y-4 text-left p-2">
        <div class="bg-amber-50 p-4 rounded-lg mb-2">
          <div class="flex items-center gap-2 text-amber-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span class="font-medium">This action cannot be undone</span>
          </div>
        </div>
        
        <div class="bg-slate-50 p-4 rounded-lg mb-4">
          <p class="text-sm text-slate-600"><span class="font-semibold">Event:</span> ${localEvent.value.title}</p>
          <p class="text-sm text-slate-600 mt-1"><span class="font-semibold">Date:</span> ${formatDisplayDate(localEvent.value.hearing_date)}</p>
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Reason for Cancellation <span class="text-red-500">*</span></label>
          <textarea id="reason" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 resize-none" rows="3" placeholder="Please provide a reason..."></textarea>
        </div>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel',
    cancelButtonText: 'No, keep it',
    confirmButtonColor: '#dc2626',
    customClass: {
      confirmButton: 'px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-all ml-2',
      cancelButton: 'px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all',
      popup: 'rounded-2xl p-6'
    },
    preConfirm: () => {
      const reason = document.getElementById('reason').value;
      if (!reason) {
        Swal.showValidationMessage('Reason is required');
        return false;
      }
      return reason;
    }
  });
  
  if (reason) {
    emit('cancel', {
      id: localEvent.value.id,
      reason
    });
  }
};

// Watch for event changes
watch(() => props.event, (newVal) => {
  if (newVal) {
    let hearingDate = '';
    if (newVal.hearing_date) {
      const date = new Date(newVal.hearing_date);
      if (!isNaN(date.getTime())) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        hearingDate = `${year}-${month}-${day}`;
      } else {
        hearingDate = newVal.hearing_date;
      }
    }
    
    let startTime = newVal.start_time || '';
    if (startTime && startTime.includes(':')) {
      const parts = startTime.split(':');
      startTime = `${parts[0]}:${parts[1]}`;
    }
    
    Object.assign(form, {
      id: newVal.id || null,
      title: newVal.title || '',
      type: newVal.type || 'hearing',
      status: newVal.status || 'scheduled',
      hearing_date: hearingDate,
      start_time: startTime,
      case_id: newVal.case_id || '',
      location: newVal.location || '',
      court_id: newVal.court_id || '',
      assigned_to: newVal.assigned_to || '',
      description: newVal.description || '',
      reschedule_reason: newVal.reschedule_reason || ''
    });
    
    errors.value = {};
  }
}, { immediate: true });

// Reset form when modal closes
watch(() => props.show, (newVal) => {
  if (!newVal) {
    setTimeout(() => {
      errors.value = {};
    }, 300);
  }
});
</script>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: all 0.25s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>