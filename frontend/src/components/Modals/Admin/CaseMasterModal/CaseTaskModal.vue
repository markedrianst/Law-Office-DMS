<template>
  <Transition name="task-modal">
    <div v-if="show" class="fixed inset-0 z-[70] flex items-center justify-center p-4" @click.self="$emit('close')">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

      <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
              :class="mode === 'add' ? 'bg-[#1a4972]/10' : mode === 'edit' ? 'bg-amber-50' : 'bg-emerald-50'">
              <svg v-if="mode === 'add'" class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
              </svg>
              <svg v-else-if="mode === 'edit'" class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              <svg v-else class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800">
                {{ mode === 'add' ? 'Add New Task' : mode === 'edit' ? 'Update Task' : 'Task Details' }}
              </h3>
              <p class="text-xs text-slate-500">
                {{ mode === 'add' ? 'Fill in the task information below' : mode === 'edit' ? 'Edit the task details' : 'View-only task information' }}
              </p>
            </div>
          </div>
          <button @click="$emit('close')" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">

          <!-- ── VIEW MODE ────────────────────────────────────────────── -->
          <template v-if="mode === 'view'">
            <div class="rounded-xl border border-slate-200 overflow-hidden">

              <!-- Document type + category badge + status -->
              <div class="bg-slate-50 px-4 py-3 border-b border-slate-100 flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-semibold text-slate-800 leading-snug">{{ localTask.task }}</p>
                  <div class="flex items-center gap-2 mt-1">
                    <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: localTask.document_color || '#94a3b8' }"></div>
                    <span class="text-xs font-medium text-slate-600">{{ localTask.document_type }}</span>
                    <span v-if="localTask.document_category" class="text-xs px-2 py-0.5 rounded-full" :class="categoryBadgeClass(localTask.document_category)">
                      {{ localTask.document_category }}
                    </span>
                  </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full flex-shrink-0"
                  :class="taskStatusClass(localTask.status)">
                  <span class="w-1.5 h-1.5 rounded-full" :class="taskStatusDotClass(localTask.status)"></span>
                  {{ taskStatusLabel(localTask.status) }}
                </span>
              </div>

              <!-- Grid: Due Date + Assigned Clerk -->
              <div class="grid grid-cols-2 gap-px bg-slate-100">
                <div class="bg-white px-4 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Due Date</p>
                  <div class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-semibold" :class="isOverdue(localTask.due_date) && localTask.status !== 'done' ? 'text-red-600' : 'text-slate-700'">
                      {{ formatDate(localTask.due_date) }}
                    </p>
                    <span v-if="isOverdue(localTask.due_date) && localTask.status !== 'done'"
                      class="px-1.5 py-0.5 text-[9px] font-bold bg-red-100 text-red-700 rounded-full uppercase">
                      Overdue
                    </span>
                  </div>
                </div>

                <div class="bg-white px-4 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Assigned Clerk</p>
                  <p v-if="resolvedClerkName" class="text-sm font-semibold text-slate-700">{{ resolvedClerkName }}</p>
                  <p v-else class="text-sm text-slate-400 italic">Unassigned</p>
                </div>
              </div>

              <!-- Notes -->
              <div class="bg-white px-4 py-3 border-t border-slate-100">
                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Notes</p>
                <p v-if="localTask.notes" class="text-sm text-slate-600 italic leading-relaxed">{{ localTask.notes }}</p>
                <p v-else class="text-sm text-slate-400 italic">No notes added.</p>
              </div>

              <!-- Meta: Created / Updated -->
              <div class="grid grid-cols-2 gap-px bg-slate-100 border-t border-slate-100">
                <div v-if="localTask.created_at" class="bg-white px-4 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Created</p>
                  <p class="text-xs text-slate-600">{{ formatDateTime(localTask.created_at) }}</p>
                </div>
                <div v-if="localTask.updated_at" class="bg-white px-4 py-3">
                  <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Last Updated</p>
                  <p class="text-xs text-slate-600">{{ formatDateTime(localTask.updated_at) }}</p>
                </div>
              </div>
            </div>
          </template>

          <!-- ── ADD / EDIT MODE ─────────────────────────────────────── -->
          <template v-else>

            <!-- ── Document Type (searchable dropdown) ── -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">
                Document Type <span class="text-red-400">*</span>
              </label>

              <!-- Loading state -->
              <div v-if="documentsLoading" class="flex items-center gap-2 px-4 py-3 text-xs text-slate-400 border border-slate-200 rounded-xl bg-slate-50">
                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Loading document types...
              </div>

              <!-- No documents available -->
              <div v-else-if="!documents || documents.length === 0" class="px-4 py-3 text-xs text-amber-600 border border-dashed border-amber-200 rounded-xl bg-amber-50">
                ⚠ No active document types found. Please add documents in Master Data first.
              </div>

              <!-- Searchable dropdown -->
              <div v-else class="relative" ref="docDropdownRef">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" 
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                  v-model="docSearch"
                  @focus="docDropdownOpen = true"
                  @input="docDropdownOpen = true"
                  type="text"
                  placeholder="Search document type..."
                  class="w-full pl-9 pr-8 py-2.5 text-sm border rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                  :class="[
                    errors.document_type_id ? 'border-red-300 bg-red-50' : '',
                    localTask.document_type_id && !errors.document_type_id
                      ? 'border-[#1a4972] font-medium text-slate-800'
                      : 'border-slate-200 text-slate-500'
                  ]" />

                <!-- Clear button -->
                <button v-if="docSearch || localTask.document_type_id" type="button" @click.prevent="clearDoc"
                  class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>

                <!-- Dropdown list -->
                <Transition name="dropdown">
                  <div v-if="docDropdownOpen" 
                    class="absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                    <div class="max-h-60 overflow-y-auto">
                      
                      <!-- Group by category -->
                      <template v-for="(group, category) in groupedDocuments" :key="category">
                        <div v-if="group.length > 0" class="sticky top-0 bg-slate-50 px-3.5 py-1.5 border-b border-slate-100">
                          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ category }}</span>
                          <span class="ml-2 text-[10px] text-slate-400">({{ group.length }})</span>
                        </div>
                        
                        <div v-for="doc in group" :key="doc.id"
                          @mousedown.prevent="selectDoc(doc)"
                          class="flex items-center gap-3 px-3.5 py-2.5 cursor-pointer hover:bg-blue-50/70 transition-colors border-b border-slate-50 last:border-0"
                          :class="{ 'bg-blue-50/60': localTask.document_type_id === doc.id }">
                          <div class="w-6 h-6 rounded-full" :style="{ backgroundColor: doc.color }"></div>
                          <div class="flex-1">
                            <span class="text-sm text-slate-700">{{ doc.type }}</span>
                            <span class="text-xs text-slate-400 ml-2">{{ doc.category }}</span>
                          </div>
                          <svg v-if="localTask.document_type_id === doc.id" 
                            class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                          </svg>
                        </div>
                      </template>

                      <!-- No results -->
                      <div v-if="filteredDocuments.length === 0" class="px-4 py-4 text-center">
                        <p class="text-xs text-slate-500">No document types match "<span class="font-medium">{{ docSearch }}</span>"</p>
                      </div>
                    </div>
                  </div>
                </Transition>
              </div>

              <!-- Validation error -->
              <p v-if="errors.document_type_id" class="text-xs text-red-500 mt-1">{{ errors.document_type_id }}</p>

              <!-- Selected document indicator -->
              <div v-if="localTask.document_type_id && selectedDoc" class="mt-2 flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: selectedDoc.color }"></div>
                <span class="text-xs font-medium text-slate-700">{{ selectedDoc.type }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full" :class="categoryBadgeClass(selectedDoc.category)">
                  {{ selectedDoc.category }}
                </span>
              </div>
            </div>

            <!-- Status + Due Date -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Status</label>
                <select v-model="localTask.status"
                  class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 bg-white transition-all">
                  <option value="todo">To-do</option>
                  <option value="in-progress">In-progress</option>
                  <option value="done">Done</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Due Date <span class="text-red-400">*</span></label>
                <input v-model="localTask.due_date" type="date"
                  class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 bg-white transition-all" />
                <p v-if="errors.due_date" class="mt-1 text-xs text-red-500 font-medium">{{ errors.due_date }}</p>
              </div>
            </div>

            <!-- Assigned Clerk (Searchable) -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Assigned Clerk</label>

              <div v-if="!clerks || clerks.length === 0" class="px-3.5 py-2.5 text-xs text-amber-600 border border-dashed border-amber-200 rounded-xl bg-amber-50">
                ⚠ No clerks available
              </div>

              <div v-else class="relative" ref="clerkDropdownRef">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                  fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                  v-model="clerkSearch"
                  @focus="clerkDropdownOpen = true"
                  @input="clerkDropdownOpen = true"
                  type="text"
                  placeholder="Search clerk..."
                  class="w-full pl-9 pr-8 py-2.5 text-sm border rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                  :class="localTask.assigned_clerk_id
                    ? 'border-[#1a4972] font-medium text-slate-800'
                    : 'border-slate-200 text-slate-500'" />

                <button v-if="clerkSearch || localTask.assigned_clerk_id" type="button" @click.prevent="clearClerk"
                  class="absolute right-2.5 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>

                <Transition name="dropdown">
                  <div v-if="clerkDropdownOpen"
                    class="absolute z-30 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                    <div v-if="filteredClerks.length > 0" class="max-h-44 overflow-y-auto">
                      <div v-for="clerk in filteredClerks" :key="clerk.id"
                        @mousedown.prevent="selectClerk(clerk)"
                        class="flex items-center gap-2.5 px-3.5 py-2.5 cursor-pointer hover:bg-blue-50/70 transition-colors"
                        :class="{ 'bg-blue-50/60': localTask.assigned_clerk_id === clerk.id }">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white bg-[#1a4972]">
                          {{ getInitials(clerk.full_name) }}
                        </div>
                        <span class="text-sm text-slate-700 flex-1">{{ clerk.full_name }}</span>
                        <svg v-if="localTask.assigned_clerk_id === clerk.id"
                          class="w-3.5 h-3.5 flex-shrink-0 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                      </div>
                    </div>
                    <div v-else class="px-4 py-4 text-center">
                      <p class="text-xs text-slate-500">No clerks match "<span class="font-medium">{{ clerkSearch }}</span>"</p>
                    </div>
                  </div>
                </Transition>
              </div>

              <!-- Selected clerk indicator -->
              <div v-if="localTask.assigned_clerk_id && selectedClerkName" class="mt-1.5 flex items-center gap-1">
                <svg class="w-3 h-3 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-xs font-medium text-emerald-700">{{ selectedClerkName }}</span>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5">Notes</label>
              <textarea v-model="localTask.notes" rows="3" placeholder="Optional notes or remarks..."
                class="w-full px-3.5 py-2.5 text-sm border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 resize-none transition-all"></textarea>
            </div>
          </template>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
          <button @click="$emit('close')"
            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">
            {{ mode === 'view' ? 'Close' : 'Cancel' }}
          </button>
          <button v-if="mode !== 'view'" @click="handleSave" :disabled="!isFormValid"
            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl active:scale-95 transition-all shadow-lg shadow-[#1a4972]/20 hover:shadow-xl"
            :class="isFormValid ? 'bg-gradient-to-br from-[#1a4972] to-[#0f2f4a]' : 'bg-gray-400 cursor-not-allowed'">
            {{ mode === 'add' ? 'Save Task' : 'Save Changes' }}
          </button>
          <button v-if="mode === 'view'" @click="$emit('switch-to-edit')"
            class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl active:scale-95 transition-all bg-gradient-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-200 hover:shadow-xl">
            Edit Task
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import { useMasterData } from '@/composables/useMasterData';
import documentService from '@/services/documentService';

const props = defineProps({
  show:   { type: Boolean, default: false },
  mode:   { type: String,  default: 'add' },
  task:   { type: Object,  default: null  },
  clerks: { type: Array,   default: () => [] }
});

const emit = defineEmits(['close', 'save', 'switch-to-edit']);

// ── Global Store Integration ──────────────────────────────────────────────
const { 
  documents, 
  documentsLoading, 
  setDocumentsLoading, 
  refreshDocuments 
} = useMasterData();

// ── Document dropdown state ────────────────────────────────────────────────
const docSearch       = ref('');
const docDropdownOpen = ref(false);
const docDropdownRef  = ref(null);

// Group documents by category
const groupedDocuments = computed(() => {
  const groups = {};
  const docs = documents.value || [];
  
  docs.forEach(doc => {
    const category = doc.category || 'Other';
    if (!groups[category]) groups[category] = [];
    groups[category].push(doc);
  });
  
  // Sort categories
  const categoryOrder = ['Pleading', 'Letter', 'Evidence', 'Court Issuance', 'Other'];
  const sortedGroups = {};
  categoryOrder.forEach(cat => {
    if (groups[cat]) {
      // Sort documents by type within category
      groups[cat].sort((a, b) => a.type.localeCompare(b.type));
      sortedGroups[cat] = groups[cat];
    }
  });
  
  return sortedGroups;
});

const filteredDocuments = computed(() => {
  if (!docSearch.value) return documents.value || [];
  
  const searchLower = docSearch.value.toLowerCase().trim();
  return (documents.value || []).filter(doc => 
    doc.type.toLowerCase().includes(searchLower) ||
    (doc.category && doc.category.toLowerCase().includes(searchLower))
  );
});

const selectedDoc = computed(() => {
  if (!localTask.document_type_id) return null;
  return (documents.value || []).find(d => d.id === localTask.document_type_id);
});

const selectDoc = (doc) => {
  localTask.document_type_id = doc.id;
  localTask.document_type = doc.type;
  localTask.document_category = doc.category || '';
  localTask.document_color = doc.color || '#94a3b8';
  localTask.task = doc.type; // Auto-fill task description with document type
  docSearch.value = doc.type;
  docDropdownOpen.value = false;
  errors.document_type_id = '';
};

const clearDoc = () => {
  localTask.document_type_id = null;
  localTask.document_type = '';
  localTask.document_category = '';
  localTask.document_color = '#94a3b8';
  localTask.task = '';
  docSearch.value = '';
  docDropdownOpen.value = false;
};

// ── Load documents function ────────────────────────────────────────────────
const loadDocuments = async () => {
  // Only load if documents array is empty
  if (!documents.value || documents.value.length === 0) {
    setDocumentsLoading(true);
    try {
      const response = await documentService.getActiveDocuments();
      refreshDocuments(response.data || []);
    } catch (error) {
      console.error('Failed to load documents:', error);
    } finally {
      setDocumentsLoading(false);
    }
  }
};

// ── Local form ─────────────────────────────────────────────────────────────
const defaultTask = () => ({
  task: '',
  document_type_id: null,
  document_type: '',
  document_category: '',
  document_color: '#94a3b8',
  status: 'todo',
  due_date: '',
  assigned_clerk_id: '',
  assigned_to: '',
  notes: '',
  created_at: null,
  updated_at: null,
});

const localTask = reactive(defaultTask());
const errors = reactive({ 
  document_type_id: '',
  due_date: '' 
});

// ── Clerk dropdown ─────────────────────────────────────────────────────────
const clerkSearch       = ref('');
const clerkDropdownOpen = ref(false);
const clerkDropdownRef  = ref(null);

const clerkDisplayName = (clerk) => clerk?.full_name ?? clerk?.name ?? '';

const filteredClerks = computed(() => {
  if (!props.clerks || props.clerks.length === 0) return [];
  
  const q = clerkSearch.value.toLowerCase().trim();
  if (q) {
    return props.clerks.filter(c => 
      clerkDisplayName(c).toLowerCase().includes(q)
    );
  }
  return props.clerks;
});

const selectedClerkName = computed(() => {
  if (!localTask.assigned_clerk_id) return '';
  const found = (props.clerks || []).find(c => c.id === localTask.assigned_clerk_id);
  return found ? clerkDisplayName(found) : '';
});

const resolvedClerkName = computed(() => {
  if (localTask.assigned_clerk_id) {
    const found = (props.clerks || []).find(c => c.id === localTask.assigned_clerk_id);
    if (found) return clerkDisplayName(found);
  }
  return localTask.assigned_to || '';
});

// ── Form validation ────────────────────────────────────────────────────────
const isFormValid = computed(() => {
  return localTask.document_type_id && localTask.due_date;
});

// ── Sync on open / task change ─────────────────────────────────────────────
const syncTask = () => {
  errors.document_type_id = '';
  errors.due_date = '';
  clerkDropdownOpen.value = false;
  docDropdownOpen.value = false;

  if (props.mode === 'add') {
    Object.assign(localTask, defaultTask());
    clerkSearch.value = '';
    docSearch.value = '';
    return;
  }

  if (props.task) {
    console.log('Syncing task:', props.task);
    
    // Map the task data to local form - NO 'task' field
    Object.assign(localTask, {
      // Use document_type as the task description
      task: props.task.document_type || 'Untitled Task',
      document_type_id: props.task.document_type_id || null,
      document_type: props.task.document_type || '',
      document_category: props.task.document_category || '',
      document_color: props.task.document_color || '#94a3b8',
      status: props.task.status || 'todo',
      due_date: props.task.due_date || '',
      assigned_clerk_id: props.task.assigned_clerk_id || '',
      assigned_to: props.task.assigned_to || '',
      notes: props.task.notes || '',
      created_at: props.task.created_at || null,
      updated_at: props.task.updated_at || null,
    });

    // Pre-fill doc search box
    docSearch.value = localTask.document_type || '';

    // Pre-fill clerk search box
    if (localTask.assigned_clerk_id && props.clerks) {
      const foundClerk = props.clerks.find(c => c.id === localTask.assigned_clerk_id);
      clerkSearch.value = foundClerk ? clerkDisplayName(foundClerk) : (localTask.assigned_to || '');
    } else {
      clerkSearch.value = localTask.assigned_to || '';
    }
  }
};

// Watch for modal open - load documents and sync task
watch(() => props.show, async (newVal) => {
  if (newVal) {
    await loadDocuments();
    syncTask();
  }
}, { immediate: true });

// Watch for task changes
watch(() => props.task, syncTask, { deep: true });

// Watch for documents to be loaded, then try to resolve document_type_id
watch(() => documents.value, (newDocs) => {
  if (newDocs?.length > 0 && localTask.document_type && !localTask.document_type_id) {
    const matched = newDocs.find(
      d => d.type.toLowerCase() === localTask.document_type.toLowerCase()
    );
    if (matched) {
      localTask.document_type_id = matched.id;
      localTask.document_category = matched.category || '';
      localTask.document_color = matched.color || '#94a3b8';
    }
  }
});

// ── Click outside ──────────────────────────────────────────────────────────
const handleClickOutside = (e) => {
  if (clerkDropdownRef.value && !clerkDropdownRef.value.contains(e.target)) {
    clerkDropdownOpen.value = false;
  }
  if (docDropdownRef.value && !docDropdownRef.value.contains(e.target)) {
    docDropdownOpen.value = false;
  }
};

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('mousedown', handleClickOutside));

// ── Clerk actions ──────────────────────────────────────────────────────────
const getInitials = (name) => {
  if (!name) return '??';
  const parts = name.split(' ').filter(Boolean);
  if (parts.length === 0) return '??';
  if (parts.length === 1) return parts[0][0].toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

const selectClerk = (clerk) => {
  localTask.assigned_clerk_id = clerk.id;
  localTask.assigned_to = clerkDisplayName(clerk);
  clerkSearch.value = clerkDisplayName(clerk);
  clerkDropdownOpen.value = false;
};

const clearClerk = () => {
  localTask.assigned_clerk_id = '';
  localTask.assigned_to = '';
  clerkSearch.value = '';
  clerkDropdownOpen.value = false;
};

// ── Save ───────────────────────────────────────────────────────────────────
const handleSave = () => {
  errors.document_type_id = '';
  errors.due_date = '';
  
  if (!localTask.document_type_id) {
    errors.document_type_id = 'Please select a document type.';
    return;
  }
  
  if (!localTask.due_date) {
    errors.due_date = 'Please select a due date.';
    return;
  }

  const payload = {
    // Use document_type as task
    task: localTask.document_type,
    document_type_id: localTask.document_type_id,
    document_type: localTask.document_type,
    document_category: localTask.document_category,
    document_color: localTask.document_color,
    status: localTask.status,
    due_date: localTask.due_date,
    assigned_clerk_id: localTask.assigned_clerk_id || null,
    assigned_to: localTask.assigned_to,
    notes: localTask.notes || null,
  };

  if (props.mode === 'edit' && props.task?.id) {
    payload.id = props.task.id;
  }
  
  emit('save', { mode: props.mode, data: payload });
};

// ── Category color helpers ─────────────────────────────────────────────────
const CATEGORY_COLORS = {
  'Pleading':      { badge: 'bg-blue-50 text-blue-700' },
  'Letter':        { badge: 'bg-sky-50 text-sky-700' },
  'Evidence':      { badge: 'bg-emerald-50 text-emerald-700' },
  'Court Issuance': { badge: 'bg-amber-50 text-amber-700' },
  'Other':         { badge: 'bg-slate-100 text-slate-600' },
};
const CAT_DEFAULT = { badge: 'bg-slate-100 text-slate-600' };

const categoryBadgeClass = (c) => CATEGORY_COLORS[c]?.badge || CAT_DEFAULT.badge;

// ── Misc helpers ───────────────────────────────────────────────────────────
const isOverdue = (d) => {
  if (!d) return false;
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const due = new Date(d); due.setHours(0, 0, 0, 0);
  return due < today;
};

const formatDate = (d) => {
  if (!d) return '—';
  const dt = new Date(d);
  if (isNaN(dt)) return d;
  const today = new Date(); today.setHours(0, 0, 0, 0);
  const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);
  const due = new Date(dt); due.setHours(0, 0, 0, 0);
  if (due.getTime() === today.getTime()) return 'Today';
  if (due.getTime() === tomorrow.getTime()) return 'Tomorrow';
  return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const formatDateTime = (d) => {
  if (!d) return '—';
  const dt = new Date(d);
  if (isNaN(dt)) return d;
  return dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
    ' ' + dt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
};
// Add this watch
watch(() => props.clerks, (newVal) => {
}, { immediate: true, deep: true });
const taskStatusLabel = (s) => ({ todo: 'To-do', 'in-progress': 'In-progress', done: 'Done' }[s] || s);
const taskStatusClass = (s) => ({ 
  todo: 'bg-slate-100 text-slate-600', 
  'in-progress': 'bg-amber-50 text-amber-700', 
  done: 'bg-emerald-50 text-emerald-700' 
}[s] || 'bg-slate-100 text-slate-500');
const taskStatusDotClass = (s) => ({ 
  todo: 'bg-slate-400', 
  'in-progress': 'bg-amber-400', 
  done: 'bg-emerald-500' 
}[s] || 'bg-slate-400');
</script>

<style scoped>
.task-modal-enter-active, .task-modal-leave-active { transition: all 0.2s ease; }
.task-modal-enter-from, .task-modal-leave-to { opacity: 0; transform: scale(0.97) translateY(6px); }

.dropdown-enter-active { transition: all 0.15s ease; }
.dropdown-enter-from { opacity: 0; transform: translateY(-6px); }
.dropdown-leave-active { transition: all 0.1s ease; }
.dropdown-leave-to { opacity: 0; }

.overflow-y-auto::-webkit-scrollbar { width: 6px; }
.overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>