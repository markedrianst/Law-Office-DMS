<template>
  <Transition name="modal">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
      <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
      
      <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#1a4972]/10 flex items-center justify-center">
              <svg class="w-5 h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-lg font-bold text-slate-800">{{ isEditing ? 'Edit Case' : 'Create New Case' }}</h2>
              <p class="text-sm text-slate-500">{{ isEditing ? 'Update case information' : 'Fill in the details to create a new case' }}</p>
            </div>
          </div>
          <button @click="$emit('close')" :disabled="formLoading" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">
          
          <!-- Case Code Preview (for new cases) -->
          <div v-if="!isEditing" class="flex items-center gap-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
            <div class="w-8 h-8 rounded-lg bg-[#1a4972]/10 flex items-center justify-center">
              <svg class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
              </svg>
            </div>
            <div>
              <p class="text-xs text-slate-500">Auto-generated Case Code</p>
              <p class="text-sm font-bold text-[#1a4972]">{{ previewCode }}</p>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="flex items-center justify-center py-12">
            <svg class="animate-spin w-8 h-8 text-[#1a4972]" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
          </div>

          <template v-else>
            <!-- Case Information Section -->
            <div>
              <h3 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100">Case Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Case Number -->
                <div class="md:col-span-1">
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Case Number <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.case_no" type="text" placeholder="e.g. CIV-2024-001"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                    :class="{ 'border-red-400': errors.case_no }" />
                  <p v-if="errors.case_no" class="text-xs text-red-500 mt-1">{{ errors.case_no }}</p>
                </div>

                <!-- Title -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Case Title <span class="text-red-500">*</span>
                  </label>
                  <input v-model="form.title" type="text" placeholder="e.g. Cruz vs. Santos — Civil Case for Annulment"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                    :class="{ 'border-red-400': errors.title }" />
                  <p v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <!-- Category -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Category</label>
                  <select v-model="form.category_id"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600">
                    <option value="">— Select Category —</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                      {{ cat.name }}
                    </option>
                  </select>
                </div>

                <!-- Client with Search and Quick Add -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Client <span class="text-red-500">*</span>
                  </label>
                  <div class="flex gap-2">
                    <div class="relative flex-1" ref="clientDropdownRef">
                      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" 
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                      </svg>
                      <input
                        v-model="clientSearch"
                        @focus="clientDropdownOpen = true"
                        @input="clientDropdownOpen = true"
                        type="text"
                        placeholder="Search client..."
                        class="w-full pl-9 pr-8 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                        :class="form.client_id ? 'border-[#1a4972] font-medium text-slate-800' : 'border-slate-200 text-slate-500'" />
                      
                      <!-- Clear button -->
                      <button v-if="clientSearch || form.client_id" type="button" @click.prevent="clearClient"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                      </button>

                      <!-- Dropdown list -->
                      <Transition name="dropdown">
                        <div v-if="clientDropdownOpen" 
                          class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                          <div class="max-h-48 overflow-y-auto">
                            <div v-if="filteredClients.length > 0">
                              <div v-for="client in filteredClients" :key="client.id"
                                @mousedown.prevent="selectClient(client)"
                                class="flex items-center gap-2.5 px-3.5 py-2.5 cursor-pointer hover:bg-blue-50/70 transition-colors"
                                :class="{ 'bg-blue-50/60': form.client_id === client.id }">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white bg-[#1a4972]">
                                  {{ getInitials(client.full_name) }}
                                </div>
                                <span class="text-sm text-slate-700 flex-1">{{ client.full_name }}</span>
                                <svg v-if="form.client_id === client.id" class="w-3.5 h-3.5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                              </div>
                            </div>
                            <div v-else class="px-4 py-4 text-center">
                              <p class="text-xs text-slate-500">No clients found</p>
                            </div>
                          </div>
                        </div>
                      </Transition>
                    </div>
                    
                    <button @click="openNewClientModal" type="button" 
                      class="px-3 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition-all flex items-center gap-1">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                    </button>
                  </div>
                  <p v-if="errors.client_id" class="text-xs text-red-500 mt-1">{{ errors.client_id }}</p>
                  
                  <!-- Newly created client indicator -->
                  <Transition name="fade">
                    <div v-if="newlyCreatedClient" class="mt-2 flex items-center gap-1 text-xs text-emerald-600">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      </svg>
                      <span>"{{ newlyCreatedClient }}" created and selected</span>
                    </div>
                  </Transition>
                </div>
              </div>
            </div>

            <!-- Court Information Section -->
            <div>
              <h3 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100">Court Information</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Court/Office with Search and N/A Checkbox -->
                <div>
               <div class="flex items-center gap-2 mb-1.5">
                <label class="text-sm font-semibold text-slate-700">Court / Office</label>
                <input type="checkbox" v-model="courtNA" @change="onCourtNAChange" id="courtNA" 
                    class="w-3.5 h-3.5 rounded border-slate-300 text-[#1a4972] focus:ring-[#1a4972]" />
                <label for="courtNA" class="text-xs text-slate-500 font-medium cursor-pointer">N/A</label>
                </div>

                  <!-- Active dropdown input -->
                  <div v-if="!courtNA" class="relative" ref="courtDropdownRef">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" 
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                      v-model="courtSearch"
                      @focus="courtDropdownOpen = true"
                      @input="courtDropdownOpen = true"
                      type="text"
                      placeholder="Search or type court / office..."
                      class="w-full pl-9 pr-8 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                      :class="form.court_or_office ? 'border-[#1a4972] font-medium text-slate-800' : 'border-slate-200 text-slate-500'" />
                    
                    <!-- Clear button -->
                    <button v-if="courtSearch || form.court_or_office" type="button" @click.prevent="clearCourt"
                      class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </button>

                    <!-- Dropdown list -->
                    <Transition name="dropdown">
                      <div v-if="courtDropdownOpen" 
                        class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                        <div class="max-h-48 overflow-y-auto">
                          <!-- Loading state -->
                          <div v-if="loadingCourts" class="px-4 py-3 flex items-center gap-2 text-xs text-slate-400">
                            <svg class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Loading courts...
                          </div>

                          <!-- Matching options -->
                          <template v-else>
                            <div v-for="court in filteredCourts" :key="court.id"
                              @mousedown.prevent="selectCourt(court)"
                              class="flex items-center gap-2.5 px-3.5 py-2.5 cursor-pointer hover:bg-blue-50/70 transition-colors"
                              :class="{ 'bg-blue-50/60': form.court_or_office === court.name }">
                              <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                              </svg>
                              <span class="text-sm text-slate-700 flex-1">{{ court.name }}</span>
                              <span class="text-xs text-slate-400">{{ court.type }}</span>
                              <svg v-if="form.court_or_office === court.name" class="w-3.5 h-3.5 text-[#1a4972] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                              </svg>
                            </div>

                            <!-- "Use custom" option -->
                            <div v-if="courtSearch.trim() && !exactCourtMatch"
                              @mousedown.prevent="useCustomCourt"
                              class="flex items-center gap-2.5 px-3.5 py-2.5 cursor-pointer hover:bg-indigo-50/70 transition-colors border-t border-slate-100">
                              <svg class="w-3.5 h-3.5 text-[#1a4972] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                              </svg>
                              <span class="text-sm flex-1">Use "<span class="font-bold text-[#1a4972]">{{ courtSearch.trim() }}</span>"</span>
                            </div>

                            <!-- Empty hint -->
                            <div v-if="filteredCourts.length === 0 && !courtSearch.trim() && !loadingCourts" 
                              class="px-4 py-4 text-center">
                              <p class="text-xs text-slate-400">Type to search or enter a custom court</p>
                            </div>
                          </template>
                        </div>
                      </div>
                    </Transition>
                  </div>

                  <!-- N/A disabled input -->
                  <input v-else type="text" value="Not Applicable" disabled
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-100 text-slate-400 cursor-not-allowed opacity-60" />
                </div>

                <!-- Docket Number with N/A Checkbox -->
                <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <label class="text-sm font-semibold text-slate-700">Docket Number</label>
                    <input type="checkbox" v-model="docketNA" @change="onDocketNAChange" id="docketNA" 
                        class="w-3.5 h-3.5 rounded border-slate-300 text-[#1a4972] focus:ring-[#1a4972]" />
                    <label for="docketNA" class="text-xs text-slate-500 font-medium cursor-pointer">N/A</label>
                </div>
                  <input v-model="form.docket_no" type="text" :placeholder="docketNA ? 'Not Applicable' : 'e.g. 2024-00123'" 
                    :disabled="docketNA"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-slate-100" />
                </div>
              </div>
            </div>

            <!-- Assignment Section -->
            <div>
              <h3 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100">Assignment</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Assigned Lawyer -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Assigned Lawyer <span class="text-red-500">*</span>
                  </label>
                  <select v-model="form.assigned_lawyer_id"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600"
                    :class="{ 'border-red-400': errors.assigned_lawyer_id }">
                    <option value="">— Select Lawyer —</option>
                    <option v-for="lawyer in lawyers" :key="lawyer.id" :value="lawyer.id">
                      {{ lawyer.full_name }}
                    </option>
                  </select>
                  <p v-if="errors.assigned_lawyer_id" class="text-xs text-red-500 mt-1">{{ errors.assigned_lawyer_id }}</p>
                </div>

                <!-- Assigned Clerk -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Assigned Clerk</label>
                  <select v-model="form.assigned_clerk_id"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600">
                    <option value="">— Select Clerk —</option>
                    <option v-for="clerk in clerks" :key="clerk.id" :value="clerk.id">
                      {{ clerk.full_name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Status & Priority Section -->
            <div>
              <h3 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100">Status & Priority</h3>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Priority -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Priority</label>
                  <select v-model="form.priority"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600">
                    <option value="low">Low</option>
                    <option value="normal">Normal</option>
                    <option value="urgent">Urgent</option>
                  </select>
                </div>

                <!-- Case Status -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Case Status</label>
                  <select v-model="form.case_status"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600">
                    <option value="active">Active</option>
                    <option value="closed">Closed</option>
                    <option value="archived">Archived</option>
                  </select>
                </div>

                <!-- Current Stage -->
                <!-- Current Stage -->
                <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Stage</label>
                <select v-model="form.current_stage_id"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600">
                    <option v-for="stage in stages" :key="stage.id" :value="stage.id">
                    {{ stage.name }}
                    </option>
                </select>
                </div>
              </div>
            </div>

            <!-- Summary -->
            <div>
              <h3 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100">Notes</h3>
              <textarea v-model="form.summary" rows="4" placeholder="Brief summary of the case..."
                class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all resize-none"></textarea>
            </div>
          </template>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
          <button @click="$emit('close')" :disabled="formLoading"
            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
            Cancel
          </button>
          <button @click="$emit('submit')" :disabled="formLoading || isLoading"
            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 min-w-[120px] justify-center bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md hover:shadow-lg transition-all">
            <svg v-if="formLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            {{ formLoading ? (isEditing ? 'Saving...' : 'Creating...') : (isEditing ? 'Save Changes' : 'Create Case') }}
          </button>
        </div>
      </div>
    </div>
  </Transition>

  <!-- New Client Modal -->
  <NewClientModal
    :show="showNewClientModal"
    @close="showNewClientModal = false"
    @saved="onClientCreated"
  />
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import NewClientModal from '@/components/Modals/Admin/CaseMasterModal/NewClientModal.vue';
import courtService from '@/services/courtService';

const props = defineProps({
  show: Boolean,
  isEditing: Boolean,
  formLoading: Boolean,
  form: Object,
  errors: Object,
  categories: Array,
  stages: Array,
  lawyers: Array,
  clerks: Array,
  clients: Array,
  courts: {
    type: Array,
    default: () => []
  },
  previewCode: String,
  isLoading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'submit', 'client-created']);

// New client modal
const showNewClientModal = ref(false);
const newlyCreatedClient = ref('');

// Client search
const clientSearch = ref('');
const clientDropdownOpen = ref(false);
const clientDropdownRef = ref(null);

// Court search
const courtSearch = ref('');
const courtDropdownOpen = ref(false);
const courtDropdownRef = ref(null);
const loadingCourts = ref(false);
const allCourts = ref([]);

// N/A checkboxes
const courtNA = ref(false);
const docketNA = ref(false);

// Computed for client filtering
const filteredClients = computed(() => {
  if (!clientSearch.value) return props.clients || [];
  
  const searchLower = clientSearch.value.toLowerCase().trim();
  return (props.clients || []).filter(client => 
    client.full_name.toLowerCase().includes(searchLower)
  );
});

// Computed for court filtering
const filteredCourts = computed(() => {
  if (!courtSearch.value) return allCourts.value;
  
  const searchLower = courtSearch.value.toLowerCase().trim();
  return allCourts.value.filter(court => 
    court.name.toLowerCase().includes(searchLower) ||
    court.type?.toLowerCase().includes(searchLower)
  );
});

const exactCourtMatch = computed(() => {
  if (!courtSearch.value) return false;
  const searchLower = courtSearch.value.toLowerCase().trim();
  return allCourts.value.some(court => 
    court.name.toLowerCase() === searchLower
  );
});

// Load courts
const loadCourts = async () => {
  loadingCourts.value = true;
  try {
    const response = await courtService.getActiveCourts();
    allCourts.value = response.data || [];
  } catch (error) {
    console.error('Failed to load courts:', error);
    allCourts.value = [];
  } finally {
    loadingCourts.value = false;
  }
};

// Client methods
const getInitials = (name) => {
  if (!name) return '?';
  const parts = name.split(' ').filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0][0].toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const selectClient = (client) => {
  props.form.client_id = client.id;
  clientSearch.value = client.full_name;
  clientDropdownOpen.value = false;
};

const clearClient = () => {
  props.form.client_id = '';
  clientSearch.value = '';
  clientDropdownOpen.value = false;
};

// Court methods
const selectCourt = (court) => {
  props.form.court_or_office = court.name;
  courtSearch.value = court.name;
  courtDropdownOpen.value = false;
  courtNA.value = false;
};

const clearCourt = () => {
  props.form.court_or_office = '';
  courtSearch.value = '';
  courtDropdownOpen.value = false;
};

const useCustomCourt = () => {
  if (courtSearch.value.trim()) {
    props.form.court_or_office = courtSearch.value.trim();
    courtDropdownOpen.value = false;
    courtNA.value = false;
  }
};

// N/A methods
const onCourtNAChange = () => {
  if (courtNA.value) {
    props.form.court_or_office = 'N/A';
    courtSearch.value = '';
  } else {
    props.form.court_or_office = '';
  }
};

const onDocketNAChange = () => {
  props.form.docket_no = docketNA.value ? 'N/A' : '';
};

// Click outside handlers
const handleClickOutside = (event) => {
  if (clientDropdownRef.value && !clientDropdownRef.value.contains(event.target)) {
    clientDropdownOpen.value = false;
  }
  if (courtDropdownRef.value && !courtDropdownRef.value.contains(event.target)) {
    courtDropdownOpen.value = false;
  }
};

// Modal methods
const openNewClientModal = () => {
  showNewClientModal.value = true;
};

const onClientCreated = async (newClient) => {
  newlyCreatedClient.value = newClient.full_name;
  
  // Set the new client as selected
  props.form.client_id = newClient.id;
  clientSearch.value = newClient.full_name;
  
  // Notify parent to refresh clients list
  try {
    const response = await import('@/services/clientService').then(m => m.default.getAll({ limit: 100 }));
    emit('client-created', response.data || []);
  } catch (error) {
    console.error('Failed to refresh clients:', error);
  }
  
  // Auto-hide after 3 seconds
  setTimeout(() => {
    newlyCreatedClient.value = '';
  }, 3000);
};

// Watch for form.court_or_office changes
watch(() => props.form.court_or_office, (newVal) => {
  if (newVal && newVal !== 'N/A' && courtSearch.value !== newVal) {
    courtSearch.value = newVal;
  }
  courtNA.value = newVal === 'N/A';
});

// Watch for form.docket_no changes
watch(() => props.form.docket_no, (newVal) => {
  docketNA.value = newVal === 'N/A';
});

// Watch for modal open to sync search fields
watch(() => props.show, (newVal) => {
  if (newVal) {
    // Sync client search
    if (props.form.client_id) {
      const selected = (props.clients || []).find(c => c.id === props.form.client_id);
      if (selected) {
        clientSearch.value = selected.full_name;
      }
    }
    
    // Sync court search and N/A
    if (props.form.court_or_office) {
      courtSearch.value = props.form.court_or_office;
      courtNA.value = props.form.court_or_office === 'N/A';
    }
    
    // Sync docket N/A
    docketNA.value = props.form.docket_no === 'N/A';

    // ✅ Default to first stage for new cases
    if (!props.isEditing && !props.form.current_stage_id && props.stages?.length) {
      props.form.current_stage_id = props.stages[0].id;
    }

  } else {
    // Reset on close
    clientSearch.value = '';
    courtSearch.value = '';
    courtNA.value = false;
    docketNA.value = false;
    newlyCreatedClient.value = '';
  }
});

// Lifecycle
onMounted(() => {
  loadCourts();
  document.addEventListener('mousedown', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleClickOutside);
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

.fade-enter-active, .fade-leave-active {
  transition: all 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-enter-active {
  transition: all 0.15s ease;
}
.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-6px);
}
.dropdown-leave-active {
  transition: all 0.1s ease;
}
.dropdown-leave-to {
  opacity: 0;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>