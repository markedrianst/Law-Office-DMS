<template>
  <!-- Show loading state while auth is initializing -->
  <div v-if="!isAuthReady" class="min-h-screen bg-slate-50 flex items-center justify-center">
    <div class="text-center">
      <svg class="animate-spin w-10 h-10 text-[#1a4972] mx-auto mb-4" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
      </svg>
      <p class="text-slate-500">Loading your dashboard...</p>
    </div>
  </div>

  <!-- Show dashboard when auth is ready -->
  <div v-else class="min-h-screen bg-slate-50">
    <!-- Welcome Header -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold text-[#1a4972]">Welcome back, {{ userName || 'User' }}!</h1>
      </div>
      <p class="text-sm text-slate-500 ml-4">{{ getRoleMessage }}</p>
      
      <!-- Live indicator - shows when refreshing -->
      <div class="flex items-center gap-2 mt-2 ml-4">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" v-if="isRefreshing"></span>
          <span class="relative inline-flex rounded-full h-3 w-3" :class="isRefreshing ? 'bg-emerald-500' : 'bg-emerald-500'"></span>
        </span>
        <span class="text-xs text-emerald-600">
          {{ isRefreshing ? 'Updating...' : 'Live' }}
        </span>
        <span class="text-xs text-slate-400 ml-2">
          Last updated: {{ lastUpdated }}
        </span>
      </div>
    </div>

    <!-- Role-Based Dashboard - Always shows data (cached while refreshing) -->
    <component
      :is="dashboardComponent"
      :stats="stats"
      :admin-stats="adminStats"
      :lawyer-stats="lawyerStats"
      :clerk-stats="clerkStats"
      :my-cases="myCases"
      :my-tasks="myTasks"
      :recent-activities="recentActivities"
      :recent-movements="recentMovements"
      :pending-items="pendingItems"
      :pending-documents="pendingDocuments"
      :pending-movements="pendingMovements"
      :pending-total="pendingTotal"
      :cases-loading="false"  
      :tasks-loading="false"
      @toggle-task="handleToggleTask"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, markRaw, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import cacheService from '@/services/cacheService';
import { 
  caseService, 
  approvalService, 
  clientService, 
  documentService 
} from '@/services/masterData';

// Import role-specific dashboards
import AdminDashboard from '@/pages/Admin/AdminDashboard.vue';
import LawyerDashboard from '@/pages/Lawyer/LawyerDashboard.vue';
import ClerkDashboard from '@/pages/Clerk/ClerkDashboard.vue';

// ========== AUTH ==========
const { user, userRole, userName, isAuthReady } = useAuth();

// ========== DASHBOARD DATA ==========
const stats = ref({});
const adminStats = ref({});
const lawyerStats = ref({});
const clerkStats = ref({});
const myCases = ref([]);
const myTasks = ref([]);
const recentActivities = ref([]);
const recentMovements = ref([]);
const pendingItems = ref([]);
const pendingDocuments = ref(0);
const pendingMovements = ref(0);
const pendingTotal = ref(0);
const pendingDocumentsList = ref([]);

// ========== LIVE/REFRESH STATE ==========
const isRefreshing = ref(false);
const lastUpdated = ref('Just now');
const refreshInterval = ref(null);
const refreshTimeout = ref(null);

// ========== ROLE-BASED DASHBOARD COMPONENT ==========
const dashboardComponent = computed(() => {
  switch(userRole.value) {
    case 'admin': return markRaw(AdminDashboard);
    case 'lawyer': return markRaw(LawyerDashboard);
    case 'clerk': return markRaw(ClerkDashboard);
    default: return markRaw(AdminDashboard);
  }
});

// ========== USER INFO ==========
const getRoleMessage = computed(() => {
  const messages = {
    admin: 'Manage and oversee the entire system',
    lawyer: 'Manage your cases and documents',
    clerk: 'Handle daily tasks and records'
  };
  return messages[userRole.value] || 'Welcome to the Document Management System';
});

// ========== LOAD FROM CACHE FIRST (INSTANT) ==========
const loadFromCache = () => {
  const role = userRole.value;
  
  // Get pending counts
  const pendingCounts = cacheService.getPendingCounts();
  pendingDocuments.value = pendingCounts.documents;
  pendingMovements.value = pendingCounts.movements;
  pendingTotal.value = pendingCounts.total;
  
  // Load role-specific dashboard data
  if (role === 'admin') {
    const data = cacheService.getAdminDashboard();
    if (data) {
      stats.value = data.stats || {};
      adminStats.value = data.adminStats || {};
    }
  } 
  else if (role === 'lawyer') {
    const data = cacheService.getLawyerDashboard();
    if (data) {
      lawyerStats.value = data.lawyerStats || {};
      myCases.value = data.myCases || [];
      pendingItems.value = data.pendingItems || [];
    }
    loadPendingDocumentsList();
  } 
  else if (role === 'clerk') {
    const data = cacheService.getClerkDashboard();
    if (data) {
      clerkStats.value = data.clerkStats || {};
      myTasks.value = data.myTasks || [];
    }
  }
  
  recentMovements.value = cacheService.getRecentMovements();
};

// ========== LOAD PENDING DOCUMENTS LIST ==========
const loadPendingDocumentsList = async () => {
  try {
    const response = await documentService.getPendingApprovals();
    pendingDocumentsList.value = response.data || [];
  } catch (error) {
    console.error('Failed to load pending documents:', error);
  }
};

// ========== FETCH LIVE DATA - UPDATES CACHE BUT KEEP OLD DATA VISIBLE ==========
const fetchLiveData = async () => {
  if (isRefreshing.value) return;
  
  isRefreshing.value = true;
  
  const role = userRole.value;
  const userId = user.value?.id;
  
  try {
    if (role === 'admin') {
      await refreshAdminData();
    } else if (role === 'lawyer' && userId) {
      await refreshLawyerData(userId);
    } else if (role === 'clerk' && userId) {
      await refreshClerkData(userId);
    }
    
    await refreshRecentMovements();
    updateLastUpdated();
    
  } catch (error) {
    console.error('Live data fetch failed:', error);
  } finally {
    isRefreshing.value = false;
  }
};

// ========== REFRESH FUNCTIONS - UPDATE DATA BUT KEEP OLD VISIBLE ==========
const refreshAdminData = async () => {
  try {
    const [casesCount, pendingCounts, clientsCount, docsCount, usersData] = await Promise.all([
      caseService.getCases({ per_page: 1 }).catch(() => ({ meta: { total: 0 } })),
      approvalService.getTotalPendingCount().catch(() => ({ documents: 0, movements: 0, total: 0 })),
      clientService.getAll({ limit: 1 }).catch(() => ({ meta: { total: 0 } })),
      documentService.getDocuments({ per_page: 1 }).catch(() => ({ meta: { total: 0 } })),
      caseService.getLookups().catch(() => ({ data: { users: [] } }))
    ]);
    
    const users = usersData.data?.users || [];
    
    // Update stats (keeps old values until new ones arrive)
    stats.value = {
      ...stats.value,
      total_cases: casesCount.meta?.total || 0,
      pending_approvals: pendingCounts.total || 0,
      total_clients: clientsCount.meta?.total || 0,
      total_documents: docsCount.meta?.total || 0
    };
    
    adminStats.value = {
      ...adminStats.value,
      total_users: users.length,
      lawyers: users.filter(u => u.role === 'lawyer').length,
      clerks: users.filter(u => u.role === 'clerk').length
    };
    
    pendingDocuments.value = pendingCounts.documents || 0;
    pendingMovements.value = pendingCounts.movements || 0;
    pendingTotal.value = pendingCounts.total || 0;
    
    // Update cache
    cacheService.setPendingCounts(pendingCounts);
    cacheService.setUsers(users);
    
    const adminData = {
      stats: { ...stats.value },
      adminStats: { ...adminStats.value },
      pendingDocuments: pendingDocuments.value,
      pendingMovements: pendingMovements.value,
      pendingTotal: pendingTotal.value,
      timestamp: Date.now()
    };
    cacheService.setAdminDashboard(adminData);
    
  } catch (error) {
    console.error('Admin refresh error:', error);
  }
};

const refreshLawyerData = async (userId) => {
  try {
    const [casesResponse, pendingCounts, movementsResponse, pendingDocs] = await Promise.all([
      caseService.getCases({ 
        assigned_lawyer_id: userId,
        per_page: 5,
        case_status: 'active'
      }).catch(() => ({ data: [], meta: { total: 0 } })),
      approvalService.getTotalPendingCount().catch(() => ({ documents: 0, movements: 0, total: 0 })),
      approvalService.getApprovals({ status: 'PENDING', per_page: 5 }).catch(() => ({ data: [] })),
      documentService.getPendingApprovals().catch(() => ({ data: [] }))
    ]);
    
    // Update data (keeps old visible)
    myCases.value = casesResponse.data || [];
    lawyerStats.value = {
      assigned_cases: casesResponse.meta?.total || 0,
      active_cases: (casesResponse.data || []).length
    };
    
    pendingItems.value = movementsResponse.data || [];
    pendingDocumentsList.value = pendingDocs.data || [];
    
    pendingDocuments.value = pendingCounts.documents || 0;
    pendingMovements.value = pendingCounts.movements || 0;
    pendingTotal.value = pendingCounts.total || 0;
    
    // Update cache
    cacheService.setPendingCounts(pendingCounts);
    
    const lawyerData = {
      lawyerStats: { ...lawyerStats.value },
      myCases: [...myCases.value],
      pendingItems: [...pendingItems.value],
      pendingDocuments: pendingDocuments.value,
      pendingMovements: pendingMovements.value,
      pendingTotal: pendingTotal.value,
      timestamp: Date.now()
    };
    cacheService.setLawyerDashboard(lawyerData);
    
  } catch (error) {
    console.error('Lawyer refresh error:', error);
  }
};

const refreshClerkData = async (userId) => {
  try {
    const [casesResponse] = await Promise.all([
      caseService.getCases({ 
        assigned_clerk_id: userId,
        per_page: 1
      }).catch(() => ({ meta: { total: 0 } }))
    ]);
    
    const tasks = myTasks.value; // Keep existing tasks
    
    clerkStats.value = {
      assigned_cases: casesResponse.meta?.total || 0,
      total_tasks: tasks.length,
      pending_tasks: tasks.filter(t => t.status !== 'done').length,
      completed_tasks: tasks.filter(t => t.status === 'done').length
    };
    
    const clerkData = {
      clerkStats: { ...clerkStats.value },
      myTasks: [...tasks],
      timestamp: Date.now()
    };
    cacheService.setClerkDashboard(clerkData);
    
  } catch (error) {
    console.error('Clerk refresh error:', error);
  }
};

const refreshRecentMovements = async () => {
  try {
    const response = await approvalService.getApprovals({ per_page: 5 });
    recentMovements.value = response.data || [];
    cacheService.setRecentMovements(recentMovements.value);
  } catch (error) {
    console.error('Failed to refresh movements:', error);
  }
};

// ========== UPDATE LAST UPDATED TIMER ==========
const updateLastUpdated = () => {
  lastUpdated.value = 'Just now';
  
  if (refreshTimeout.value) clearTimeout(refreshTimeout.value);
  
  refreshTimeout.value = setTimeout(() => {
    lastUpdated.value = 'Few seconds ago';
  }, 5000);
  
  refreshTimeout.value = setTimeout(() => {
    lastUpdated.value = 'Less than a minute';
  }, 10000);
};

// ========== AUTO-REFRESH EVERY 30 SECONDS ==========
const startLiveRefresh = () => {
  if (refreshInterval.value) clearInterval(refreshInterval.value);
  
  refreshInterval.value = setInterval(() => {
    console.log('🔄 Auto-refreshing live data...');
    fetchLiveData();
  }, 30000);
};

// ========== WATCH FOR TAB VISIBILITY ==========
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') {
    console.log('👋 Tab became visible, refreshing...');
    fetchLiveData();
  }
});

// ========== ON MOUNT ==========
onMounted(() => {
  if (isAuthReady.value) {
    // 1. Show cached data INSTANTLY
    loadFromCache();
    
    // 2. Fetch live data immediately (updates in background)
    fetchLiveData();
    
    // 3. Start auto-refresh
    startLiveRefresh();
  }
});

// ========== CLEANUP ==========
onUnmounted(() => {
  if (refreshInterval.value) clearInterval(refreshInterval.value);
  if (refreshTimeout.value) clearTimeout(refreshTimeout.value);
});

// ========== HANDLE TASK TOGGLE ==========
const handleToggleTask = async (task) => {
  const newStatus = task.status === 'done' ? 'todo' : 'done';
  
  // Optimistic update
  const index = myTasks.value.findIndex(t => t.id === task.id);
  if (index !== -1) {
    myTasks.value[index].status = newStatus;
  }
  
  if (clerkStats.value) {
    const tasks = myTasks.value;
    clerkStats.value.pending_tasks = tasks.filter(t => t.status !== 'done').length;
    clerkStats.value.completed_tasks = tasks.filter(t => t.status === 'done').length;
  }
  
  updateLastUpdated();
};
</script>