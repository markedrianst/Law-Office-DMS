<template>
  <div class="min-h-screen bg-slate-100 p-6 font-sans">

    <!-- Header with Export -->
    <div class="flex items-start justify-between mb-5">
      <div class="flex items-center gap-3">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Activity Logs</h1>
      </div>

      <div class="flex items-center gap-2">
        <!-- Stats Summary -->
        <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-1.5 text-xs">
          <span class="text-slate-400">Total:</span>
          <span class="font-bold text-[#1a4972]">{{ stats.total_logs || 0 }}</span>
          <span class="text-slate-300 mx-1">|</span>
          <span class="text-emerald-600 font-semibold">{{ stats.login_stats?.success || 0 }} success</span>
          <span class="text-red-500 font-semibold">{{ stats.login_stats?.failed || 0 }} failed</span>
        </div>

        <!-- Export Dropdown -->
        <div class="relative" v-click-outside="closeExportMenu">
          <button
            @click="toggleExportMenu"
            :disabled="isExporting || logs.length === 0"
            :class="[
              'inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1a4972] text-white text-xs font-semibold transition-all',
              isExporting || logs.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-[#163d5e] cursor-pointer'
            ]"
          >
            <svg v-if="!isExporting" xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            <span v-if="isExporting" class="w-3 h-3 rounded-full border-2 border-white/30 border-t-white animate-spin"></span>
            {{ isExporting ? 'Exporting…' : 'Export' }}
            <svg v-if="!isExporting" xmlns="http://www.w3.org/2000/svg" class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
          </button>

          <transition
            enter-active-class="transition ease-out duration-150"
            enter-from-class="opacity-0 -translate-y-1 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0 -translate-y-1 scale-95"
          >
            <div v-if="showExportMenu" class="absolute top-full right-0 mt-2 w-64 bg-white border border-slate-200 rounded-2xl shadow-xl p-2 z-50">
              <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 px-2 pt-1 pb-2">Export as</p>
              <button @click="exportLogs('current')" class="flex items-center gap-3 w-full px-2.5 py-2 rounded-xl hover:bg-slate-50 transition-colors text-left">
                <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                </span>
                <div>
                  <p class="text-xs font-semibold text-slate-800">Excel — Current page</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">{{ logs.length }} rows</p>
                </div>
              </button>
              <button @click="exportLogs('all')" class="flex items-center gap-3 w-full px-2.5 py-2 rounded-xl hover:bg-slate-50 transition-colors text-left">
                <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-emerald-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/></svg>
                </span>
                <div>
                  <p class="text-xs font-semibold text-slate-800">Excel — All pages</p>
                  <p class="text-[11px] text-slate-400 mt-0.5">{{ pagination.total }} total rows</p>
                </div>
              </button>
            </div>
          </transition>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border border-slate-200 rounded-2xl p-4 mb-4">
      <div class="relative mb-3">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 w-4 h-4 pointer-events-none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="filters.search"
          @input="debouncedApply"
          type="text"
          placeholder="Search user, case code, action, IP…"
          class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[13px] text-slate-700 outline-none transition-all focus:border-[#1a4972] focus:bg-white focus:ring-2 focus:ring-[#1a4972]/10"
        />
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <button
          v-for="f in timeFilters" :key="f.value"
          @click="filterByTime(f.value)"
          :class="[
            'px-3.5 py-1.5 rounded-full border text-xs font-semibold transition-all cursor-pointer',
            timeFilter === f.value
              ? 'bg-[#1a4972] border-[#1a4972] text-white'
              : 'bg-slate-50 border-slate-200 text-slate-600 hover:border-[#1a4972] hover:text-[#1a4972]'
          ]"
        >{{ f.label }}</button>

        <select
          v-model="filters.type"
          @change="applyFilters"
          class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 cursor-pointer outline-none focus:border-[#1a4972]"
        >
          <option value="">All Types</option>
          <option value="system">🛡 System</option>
          <option value="case">📁 Case Activity</option>
        </select>

        <select
          v-if="filters.type !== 'case'"
          v-model="filters.status"
          @change="applyFilters"
          class="px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 cursor-pointer outline-none focus:border-[#1a4972]"
        >
          <option value="">All Status</option>
          <option value="success">✓ Success</option>
          <option value="failed">✗ Failed</option>
        </select>

        <button
          v-if="hasActiveFilters"
          @click="clearFilters"
          class="px-3.5 py-1.5 rounded-full bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors cursor-pointer"
        >Clear</button>
      </div>

      <!-- Active filter tags -->
      <div v-if="hasActiveFilters" class="flex flex-wrap gap-1.5 mt-3 pt-3 border-t border-slate-100">
        <span v-if="filters.search" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-[11px] font-semibold">
          "{{ filters.search }}"
          <button @click="filters.search='';applyFilters()" class="opacity-60 hover:opacity-100 bg-transparent border-none cursor-pointer text-sm leading-none p-0">×</button>
        </span>
        <span v-if="filters.type" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-[11px] font-semibold">
          {{ filters.type === 'system' ? '🛡 System' : '📁 Case Activity' }}
          <button @click="filters.type='';applyFilters()" class="opacity-60 hover:opacity-100 bg-transparent border-none cursor-pointer text-sm leading-none p-0">×</button>
        </span>
        <span v-if="filters.status" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold">
          {{ filters.status }}
          <button @click="filters.status='';applyFilters()" class="opacity-60 hover:opacity-100 bg-transparent border-none cursor-pointer text-sm leading-none p-0">×</button>
        </span>
      </div>
    </div>

    <!-- Pagination info -->
    <div v-if="pagination.total > 0" class="flex items-center justify-between bg-white border border-slate-200 rounded-xl px-4 py-2 mb-4 text-xs text-slate-500">
      <span>
        Showing <strong class="text-slate-700">{{ pagination.from }}–{{ pagination.to }}</strong>
        of <strong class="text-slate-700">{{ pagination.total }}</strong> activities
        <span class="mx-2 text-slate-300">·</span>
        Page {{ pagination.current_page }}/{{ pagination.last_page }}
      </span>
      <div class="flex items-center gap-2">
        Show:
        <select v-model="perPage" @change="changePerPage" class="px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-600 cursor-pointer outline-none">
          <option :value="10">10</option>
          <option :value="15">15</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
      </div>
    </div>

    <!-- Timeline view -->
    <div class="flex flex-col">
      <template v-for="(group, date) in groupedLogs" :key="date">
        <!-- Date separator -->
        <div class="flex items-center py-5 sticky top-4 z-10">
          <div class="inline-flex items-center gap-2 bg-white border border-[#1a4972]/10 rounded-full px-4 py-1.5 text-xs font-bold text-[#1a4972] shadow-sm">
            <span class="bg-[#1a4972] text-white px-2 py-0.5 rounded-full text-[10px]">{{ getDayOfWeek(date) }}</span>
            {{ formatDateHeader(date) }}
          </div>
        </div>

        <!-- Each log entry -->
        <div v-for="log in group" :key="`${log.type}-${log.id}`" class="flex">
          <!-- Timeline icon col -->
          <div class="flex flex-col items-center px-4 flex-shrink-0">
            <div :class="['w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 mt-4', getIconBg(log)]">
              {{ getIcon(log) }}
            </div>
            <div class="flex-1 w-0.5 bg-slate-200 my-1.5 min-h-3"></div>
          </div>

          <!-- Card -->
          <div class="flex-1 bg-white border border-slate-200 rounded-2xl p-4 my-2 transition-all hover:shadow-lg hover:border-slate-300">
            <!-- Header row -->
            <div class="flex items-start justify-between gap-3 mb-2">
              <div class="flex items-center gap-2 flex-wrap min-w-0">
                <span v-if="log.type === 'case'" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-[#1a4972]/8 text-[#1a4972] text-[10px] font-bold">
                  📁 Case
                </span>
                <span v-else class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-[10px] font-bold">
                  🛡 System
                </span>
                <p class="text-[13px] font-semibold text-slate-800">{{ getTitle(log) }}</p>
              </div>
              <div class="flex items-center gap-2 flex-shrink-0">
                <span v-if="log.status" :class="['px-2.5 py-0.5 rounded-full text-[11px] font-bold', log.status === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700']">
                  {{ log.status === 'success' ? '✓' : '✗' }} {{ log.status }}
                </span>
              <span class="text-[11px] text-slate-400 whitespace-nowrap">{{ formatTimeAgo(log.created_at) }}</span>
              </div>
            </div>

            <!-- Case badge -->
            <div v-if="log.type === 'case' && log.case_code" class="mb-2">
              <span class="inline-flex items-center gap-1.5 bg-[#1a4972]/5 border border-[#1a4972]/10 text-[#1a4972] rounded-lg px-2.5 py-1 text-[11px] font-bold">
                {{ log.case_code }}
                <span v-if="log.case_title" class="font-normal text-slate-500 max-w-xs truncate">— {{ log.case_title }}</span>
              </span>
            </div>

            <!-- System: email hint -->
            <p v-if="log.type === 'system' && !log.user?.name && log.email_attempted" class="text-xs text-slate-400 mb-1.5">
              📧 {{ log.email_attempted }}
            </p>

            <!-- System: details text -->
            <p v-if="log.type === 'system' && log.details" class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 mb-2.5 leading-relaxed">
              {{ formatSystemDetails(log.details) }}
            </p>

            <!-- Case: message -->
            <p v-if="log.type === 'case' && log.details?.message" class="text-xs text-slate-700 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 mb-2 leading-relaxed">
              {{ log.details.message }}
            </p>
            
            <p v-else-if="log.type === 'case' && log.details" class="text-xs text-slate-500 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 mb-2 leading-relaxed">
              {{ formatCaseDetails(log.details) }}
            </p>

            <!-- Meta row -->
            <div class="flex flex-wrap gap-3.5 text-[11px] text-slate-400">
              <span v-if="log.type === 'system'">🌐 {{ log.ip_address || 'Unknown IP' }}</span>
              <span v-if="log.type === 'system'">🖥 {{ formatUserAgent(log.user_agent) }}</span>
              <span v-if="log.type === 'case'">👤 {{ log.actor }}</span>
              <span>📅 {{ formatDateTime(log.created_at) }}</span>
            </div>

            <!-- Expand details -->
            <div v-if="log.type === 'system' && log.details && String(log.details).length > 150">
              <button @click="toggleExpand(log.id)" class="mt-2 text-[11px] font-bold text-[#1a4972] opacity-70 hover:opacity-100 bg-transparent border-none cursor-pointer">
                {{ expanded.includes(log.id) ? '▲ Less' : '▼ More' }}
              </button>
              <transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0 -translate-y-1"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
              >
                <pre v-if="expanded.includes(log.id)" class="mt-2 px-3 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-[11px] font-mono whitespace-pre-wrap text-slate-600">{{ JSON.stringify(log.details, null, 2) }}</pre>
              </transition>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <div v-if="logs.length === 0" class="text-center py-16">
        <div class="text-5xl mb-3 opacity-30">📋</div>
        <p class="text-base font-bold text-slate-500 mb-1">No activities found</p>
        <p class="text-[13px] text-slate-400">Try adjusting your search or filters</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="flex items-center gap-1.5 justify-center pt-6 pb-2">
        <button :disabled="pagination.current_page === 1" @click="changePage(pagination.current_page - 1)"
          class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:border-[#1a4972] hover:text-[#1a4972] disabled:opacity-35 disabled:cursor-not-allowed">
          ← Prev
        </button>
        <button v-for="p in displayedPages" :key="p" @click="changePage(p)"
          :class="['w-9 h-9 rounded-xl border text-xs font-semibold transition-all', pagination.current_page === p ? 'bg-[#1a4972] border-[#1a4972] text-white shadow-md' : 'border-slate-200 bg-white text-slate-600 hover:border-[#1a4972] hover:text-[#1a4972]']">
          {{ p }}
        </button>
        <button :disabled="pagination.current_page === pagination.last_page" @click="changePage(pagination.current_page + 1)"
          class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:border-[#1a4972] hover:text-[#1a4972] disabled:opacity-35 disabled:cursor-not-allowed">
          Next →
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { debounce } from 'lodash'
import auditLogService from '@/services/auditLogService'
import { useAuth } from '@/composables/useAuth'
import * as XLSX from 'xlsx'
import Swal from 'sweetalert2'

// Import from appUtils
import { 
  getAuditLogs,
  getAuditStats,
  setAuditLogs,
  setAuditStats,
  listenForUpdates,
  formatDateTime,
  formatTimeAgo
} from '@/utils/appUtils'

const router = useRouter()
const { userRole } = useAuth()

// Check if user is admin
if (userRole.value !== 'admin') {
  router.push('/dashboard')
}

// ==================== STATE ====================
// Get initial data from appUtils (INSTANT!)
const initialLogs = getAuditLogs();
const initialStats = getAuditStats();
const logs = ref(initialLogs || []);
const stats = ref(initialStats || { total_logs: 0, login_stats: { success: 0, failed: 0 } });
const isLoading = ref(!initialLogs || initialLogs.length === 0); // Only show loading if no data

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: logs.value.length,
  from: 1,
  to: logs.value.length
});

const expanded = ref([]);
const timeFilter = ref('');
const currentPage = ref(1);
const perPage = ref(15);
const isActive = ref(true);

const filters = reactive({
  search: '',
  type: '',
  status: '',
  date_from: '',
  date_to: ''
});

// Export
const showExportMenu = ref(false);
const isExporting = ref(false);

// ==================== COMPUTED ====================
const groupedLogs = computed(() => {
  const groups = {};
  logs.value.forEach(log => {
    if (!log.created_at) return;
    const date = new Date(log.created_at).toDateString();
    if (!groups[date]) groups[date] = [];
    groups[date].push(log);
  });
  return groups;
});

const displayedPages = computed(() => {
  const pages = [];
  const max = 5;
  const total = pagination.value.last_page || 1;
  const current = pagination.value.current_page || 1;
  
  if (total <= max) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    let s = Math.max(1, current - 2);
    let e = Math.min(total, s + max - 1);
    if (e - s + 1 < max) s = Math.max(1, e - max + 1);
    for (let i = s; i <= e; i++) pages.push(i);
  }
  return pages;
});

const hasActiveFilters = computed(() =>
  !!(filters.search || filters.type || filters.status || filters.date_from || filters.date_to)
);

const timeFilters = [
  { label: 'Today', value: 'today' },
  { label: 'Week', value: 'week' },
  { label: 'Month', value: 'month' }
];

// ==================== FETCH DATA ====================
const fetchData = async (showLoading = true) => {
  if (showLoading) isLoading.value = true;
  
  try {
    const params = {
      search: filters.search || undefined,
      type: filters.type || undefined,
      status: filters.status || undefined,
      date_from: filters.date_from || undefined,
      date_to: filters.date_to || undefined,
      page: currentPage.value,
      per_page: perPage.value
    };

    const response = await auditLogService.getCombinedLogs(params);
    
    if (response.data) {
      logs.value = response.data;
      pagination.value = response.meta || {
        current_page: currentPage.value,
        last_page: 1,
        per_page: perPage.value,
        total: logs.value.length,
        from: 1,
        to: logs.value.length
      };
    }
  } catch (error) {
    console.error('Failed to load logs:', error);
  } finally {
    if (showLoading) isLoading.value = false;
  }
};

// ==================== FETCH STATS ====================
const fetchStats = async () => {
  try {
    const response = await auditLogService.getStats();
    if (response.data) {
      stats.value = response.data;
    }
  } catch (error) {
    console.error('Failed to load stats:', error);
  }
};

// ==================== LISTEN FOR UPDATES ====================
const handleLogsUpdated = (event) => {
  console.log('🔄 Audit logs updated event received');
  logs.value = event.detail;
  if (logs.value.length > 0) {
    isLoading.value = false;
  }
};

const handleStatsUpdated = (event) => {
  console.log('🔄 Audit stats updated event received');
  stats.value = event.detail;
};

let cleanupLogs = null;
let cleanupStats = null;

// ==================== LOAD DATA ONLY IF NEEDED ====================
const initialize = async () => {
  
  // Fetch stats first (they're small)
  await fetchStats();
  
  // ONLY fetch logs if we have no data
  if (logs.value.length === 0) {
    await fetchData(true);
  } else {
    isLoading.value = false;
  }
};

// ==================== FILTER METHODS ====================
const applyFilters = () => {
  currentPage.value = 1;
  fetchData(true);
};

const debouncedApply = debounce(applyFilters, 450);

const clearFilters = () => {
  filters.search = '';
  filters.type = '';
  filters.status = '';
  filters.date_from = '';
  filters.date_to = '';
  timeFilter.value = '';
  applyFilters();
};

const filterByTime = (period) => {
  timeFilter.value = period;
  const now = new Date();
  
  if (period === 'today') {
    filters.date_from = formatDateForFilter(now);
    filters.date_to = formatDateForFilter(now);
  } else if (period === 'week') {
    const weekAgo = new Date(now);
    weekAgo.setDate(weekAgo.getDate() - 7);
    filters.date_from = formatDateForFilter(weekAgo);
    filters.date_to = formatDateForFilter(now);
  } else if (period === 'month') {
    const monthAgo = new Date(now);
    monthAgo.setMonth(monthAgo.getMonth() - 1);
    filters.date_from = formatDateForFilter(monthAgo);
    filters.date_to = formatDateForFilter(now);
  }
  applyFilters();
};

const formatDateForFilter = (date) => {
  return date.toISOString().split('T')[0];
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    currentPage.value = page;
    fetchData(true);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const changePerPage = () => {
  currentPage.value = 1;
  fetchData(true);
};

// ==================== UI HELPERS ====================
const getIcon = (log) => {
  if (log.type === 'system') {
    const icons = {
      login: '→',
      logout: '←',
      password_change: '🔑',
      user_create: '➕',
      user_update: '✏️',
      user_delete: '🗑️',
      activated: '✅',
      deactivated: '⛔'
    };
    return icons[log.action] || '•';
  }
  
  const action = (log.action || '').toLowerCase();
  if (action.includes('create')) return '➕';
  if (action.includes('archive')) return '📦';
  if (action.includes('assign')) return '👤';
  if (action.includes('update') || action.includes('edit')) return '✏️';
  if (action.includes('delete')) return '🗑️';
  if (action.includes('stage')) return '🔄';
  if (action.includes('priority')) return '🚨';
  if (action.includes('folder')) return '📁';
  if (action.includes('checklist')) return '✅';
  if (action.includes('task')) return '📋';
  return '•';
};

const getIconBg = (log) => {
  if (log.type === 'system') {
    const bgClasses = {
      login: 'bg-blue-50',
      logout: 'bg-orange-50',
      password_change: 'bg-amber-50',
      user_create: 'bg-emerald-50',
      user_update: 'bg-purple-50',
      user_delete: 'bg-red-50',
      activated: 'bg-emerald-50',
      deactivated: 'bg-red-50'
    };
    return bgClasses[log.action] || 'bg-slate-50';
  }
  
  const action = (log.action || '').toLowerCase();
  if (action.includes('create')) return 'bg-emerald-50';
  if (action.includes('archive')) return 'bg-slate-100';
  if (action.includes('assign')) return 'bg-blue-50';
  if (action.includes('update')) return 'bg-purple-50';
  if (action.includes('delete')) return 'bg-red-50';
  if (action.includes('stage')) return 'bg-amber-50';
  if (action.includes('folder')) return 'bg-orange-50';
  if (action.includes('checklist')) return 'bg-teal-50';
  if (action.includes('task')) return 'bg-indigo-50';
  return 'bg-slate-50';
};

const getTitle = (log) => {
  if (log.type === 'case') {
    return log.details?.message || `${log.actor || 'System'} ${log.action || ''}`;
  }

  const action = log.action;
  let name = 'Unknown User';
  
  if (log.user_name) name = log.user_name;
  else if (log.email_attempted) {
    const parts = log.email_attempted.split('@')[0];
    name = parts.charAt(0).toUpperCase() + parts.slice(1);
  }
  
  const email = log.email_attempted || '';
  
  if (action === 'login' && log.status === 'failed') {
    return `Failed login attempt${email ? ' for ' + email : ''}`;
  }
  if (action === 'password_change' && log.status === 'failed') {
    return `${name} failed to change password`;
  }
  
  const titles = {
    login: `${name} logged in`,
    logout: `${name} logged out`,
    password_change: `${name} changed password`,
    user_create: `Admin created ${email}`,
    user_update: `Admin updated ${email}`,
    user_delete: `Admin deleted ${email}`,
    activated: `${name}'s account activated`,
    deactivated: `${name}'s account deactivated`
  };
  
  return titles[action] || `${action} by ${name}`;
};

const getDayOfWeek = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('en-US', { weekday: 'short' });
};

const formatDateHeader = (dateStr) => {
  const date = new Date(dateStr);
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  
  if (date.toDateString() === today.toDateString()) return 'Today';
  if (date.toDateString() === yesterday.toDateString()) return 'Yesterday';
  return date.toLocaleDateString('en-US', { 
    weekday: 'long', 
    month: 'long', 
    day: 'numeric', 
    year: 'numeric' 
  });
};

const formatUserAgent = (ua) => {
  if (!ua) return 'Unknown';
  if (ua.includes('Firefox')) return 'Firefox';
  if (ua.includes('Chrome')) return 'Chrome';
  if (ua.includes('Safari')) return 'Safari';
  if (ua.includes('Edge')) return 'Edge';
  return ua.slice(0, 20) + '…';
};

const formatSystemDetails = (details) => {
  if (!details) return '';
  if (typeof details === 'string') return details;
  if (details.message) return details.message;
  return JSON.stringify(details);
};

const formatCaseDetails = (details) => {
  if (!details) return '';
  if (details.message) return details.message;
  if (details.note) return details.note;
  return JSON.stringify(details);
};

const toggleExpand = (id) => {
  if (expanded.value.includes(id)) {
    expanded.value = expanded.value.filter(x => x !== id);
  } else {
    expanded.value.push(id);
  }
};

// ==================== EXPORT ====================
const toggleExportMenu = () => {
  showExportMenu.value = !showExportMenu.value;
};

const closeExportMenu = () => {
  showExportMenu.value = false;
};

const exportLogs = async (scope) => {
  closeExportMenu();
  isExporting.value = true;

  try {
    let rows = [];
    
    if (scope === 'all') {
      const params = {
        search: filters.search || undefined,
        type: filters.type || undefined,
        status: filters.status || undefined,
        date_from: filters.date_from || undefined,
        date_to: filters.date_to || undefined,
        per_page: 9999
      };
      
      const response = await auditLogService.getCombinedLogs(params);
      rows = response.data || [];
    } else {
      rows = logs.value;
    }
    
    if (!rows.length) {
      Swal.fire({
        icon: 'warning',
        title: 'No Data',
        text: 'No logs to export',
        timer: 1500,
        showConfirmButton: false
      });
      return;
    }

    const excelRows = rows.map(log => {
      if (log.type === 'case') {
        return {
          'Type': 'Case Activity',
          'Date/Time': log.created_at ? new Date(log.created_at).toLocaleString() : '',
          'Actor': log.actor || '',
          'Case Code': log.case_code || '',
          'Case Title': log.case_title || '',
          'Action': log.action || '',
          'Details': log.details?.message || JSON.stringify(log.details) || ''
        };
      } else {
        return {
          'Type': 'System',
          'Date/Time': log.created_at ? new Date(log.created_at).toLocaleString() : '',
          'Actor': log.user_name || log.email_attempted || '',
          'Action': log.action || '',
          'Status': log.status || '',
          'Details': log.details?.message || (typeof log.details === 'string' ? log.details : JSON.stringify(log.details)) || '',
          'IP Address': log.ip_address || '',
          'Browser': formatUserAgent(log.user_agent)
        };
      }
    });

    const ws = XLSX.utils.json_to_sheet(excelRows);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Activity Logs');
    XLSX.writeFile(wb, `activity-logs_${new Date().toISOString().slice(0, 10)}.xlsx`);
    
  } catch (error) {
    console.error('Export failed', error);
    Swal.fire({
      icon: 'error',
      title: 'Export Failed',
      text: error.message || 'Failed to export logs',
      confirmButtonColor: '#dc2626'
    });
  } finally {
    isExporting.value = false;
  }
};

// ==================== LIFECYCLE ====================
onMounted(async () => {
  
  // Initialize - only fetches if no data
  await initialize();
  
  // Listen for real-time updates
  cleanupLogs = listenForUpdates('audit-logs-updated', handleLogsUpdated);
  cleanupStats = listenForUpdates('audit-stats-updated', handleStatsUpdated);
  
  isActive.value = true;
});

onUnmounted(() => {
  isActive.value = false;
  if (cleanupLogs) cleanupLogs();
  if (cleanupStats) cleanupStats();
});

// Watch for page focus
watch(() => document.visibilityState, () => {
  if (document.visibilityState === 'visible' && isActive.value) {
    // Refresh in background if needed
    if (logs.value.length === 0) {
      fetchData(false);
    }
  }
});

// ==================== DIRECTIVE ====================
const vClickOutside = {
  mounted(el, binding) {
    el._out = (e) => { if (!el.contains(e.target)) binding.value(e); };
    document.addEventListener('mousedown', el._out);
  },
  unmounted(el) { document.removeEventListener('mousedown', el._out); }
};
</script>

<style scoped>
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-slideIn {
  animation: slideIn 0.3s ease-out;
}
</style>