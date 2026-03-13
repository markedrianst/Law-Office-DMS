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
    </div>

    <!-- Role-Based Dashboard -->
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
      :cases-loading="casesLoading"
      :tasks-loading="tasksLoading"
      @toggle-task="handleToggleTask"
      @refresh="handleChildRefresh"
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
  documentService,
  userService 
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
const casesLoading = ref(false);
const tasksLoading = ref(false);
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
    // Load pending documents list
    loadPendingDocumentsList();
  } 
  else if (role === 'clerk') {
    const data = cacheService.getClerkDashboard();
    if (data) {
      clerkStats.value = data.clerkStats || {};
      myTasks.value = data.myTasks || [];
    }
  }
  
  // Load recent movements
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

// ========== FETCH LIVE DATA (FAST) ==========
const fetchLiveData = async (showLoading = false) => {
  if (isRefreshing.value) return; // Don't double-fetch
  
  isRefreshing.value = true;
  if (showLoading) {
    casesLoading.value = true;
    tasksLoading.value = true;
  }
  
  const role = userRole.value;
  const userId = user.value?.id;
  
  try {
    // Fetch only what's needed for each role - FAST queries
    if (role === 'admin') {
      await fetchAdminLiveData();
    } else if (role === 'lawyer' && userId) {
      await fetchLawyerLiveData(userId);
    } else if (role === 'clerk' && userId) {
      await fetchClerkLiveData(userId);
    }
    
    // Always fetch recent movements
    await fetchRecentMovementsLive();
    
    updateLastUpdated();
    
  } catch (error) {
    console.error('Live data fetch failed:', error);
  } finally {
    isRefreshing.value = false;
    casesLoading.value = false;
    tasksLoading.value = false;
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

// ========== ADMIN LIVE DATA ==========
const fetchAdminLiveData = async () => {
  try {
    // Fetch ALL data needed for admin dashboard in parallel
    const [casesCount, pendingCounts, clientsCount, docsCount, usersData] = await Promise.all([
      caseService.getCases({ per_page: 1 }).catch(() => ({ meta: { total: 0 } })),
      approvalService.getTotalPendingCount().catch(() => ({ documents: 0, movements: 0, total: 0 })),
      clientService.getAll({ limit: 1 }).catch(() => ({ meta: { total: 0 } })),
      documentService.getDocuments({ per_page: 1 }).catch(() => ({ meta: { total: 0 } })),
      caseService.getLookups().catch(() => ({ data: { users: [] } }))
    ]);
    
    // Get users from response
    const users = usersData.data?.users || [];
    
    // Update stats
    stats.value = {
      ...stats.value,
      total_cases: casesCount.meta?.total || 0,
      pending_approvals: pendingCounts.total || 0,
      total_clients: clientsCount.meta?.total || 0,
      total_documents: docsCount.meta?.total || 0
    };
    
    // Update adminStats
    adminStats.value = {
      ...adminStats.value,
      total_users: users.length,
      lawyers: users.filter(u => u.role === 'lawyer').length,
      clerks: users.filter(u => u.role === 'clerk').length,
      active_users: users.length
    };
    
    pendingDocuments.value = pendingCounts.documents || 0;
    pendingMovements.value = pendingCounts.movements || 0;
    pendingTotal.value = pendingCounts.total || 0;
    
    // Update cache
    cacheService.setPendingCounts(pendingCounts);
    cacheService.setUsers(users);
    
    // Update admin dashboard cache
    const adminData = {
      stats: { ...stats.value },
      adminStats: { ...adminStats.value },
      pendingDocuments: pendingDocuments.value,
      pendingMovements: pendingMovements.value,
      pendingTotal: pendingTotal.value,
      timestamp: Date.now()
    };
    cacheService.setAdminDashboard(adminData);
    
    console.log('✅ Admin dashboard updated');
    
  } catch (error) {
    console.error('Admin live data error:', error);
  }
};

// ========== LAWYER LIVE DATA ==========
const fetchLawyerLiveData = async (userId) => {
  try {
    // Fetch ALL data needed for lawyer dashboard in parallel
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
    
    // Update lawyer data
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
    
    // Update lawyer cache
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
    
    console.log('✅ Lawyer dashboard updated');
    
  } catch (error) {
    console.error('Lawyer live data error:', error);
  }
};

// ========== CLERK LIVE DATA ==========
const fetchClerkLiveData = async (userId) => {
  try {
    // Fetch ALL data needed for clerk dashboard in parallel
    const [casesResponse, tasksResponse] = await Promise.all([
      caseService.getCases({ 
        assigned_clerk_id: userId,
        per_page: 1
      }).catch(() => ({ meta: { total: 0 } })),
      Promise.resolve(getMockTasks()) // Replace with actual task endpoint
    ]);
    
    // Update clerk data
    clerkStats.value = {
      assigned_cases: casesResponse.meta?.total || 0,
      total_tasks: tasksResponse.length,
      pending_tasks: tasksResponse.filter(t => t.status !== 'done').length,
      completed_tasks: tasksResponse.filter(t => t.status === 'done').length
    };
    
    myTasks.value = tasksResponse;
    
    // Update cache
    const clerkData = {
      clerkStats: { ...clerkStats.value },
      myTasks: [...myTasks.value],
      timestamp: Date.now()
    };
    cacheService.setClerkDashboard(clerkData);
    
    console.log('✅ Clerk dashboard updated');
    
  } catch (error) {
    console.error('Clerk live data error:', error);
  }
};

// ========== MOCK TASKS (Replace with actual API) ==========
const getMockTasks = () => {
  return [
    { id: 1, task: 'Review document for Case 2024-001', case_code: '2024-001', status: 'todo', due_date: '2024-03-20' },
    { id: 2, task: 'Prepare folder for Case 2024-002', case_code: '2024-002', status: 'in-progress', due_date: '2024-03-18' },
    { id: 3, task: 'File motion for Case 2024-003', case_code: '2024-003', status: 'todo', due_date: '2024-03-15' }
  ];
};

// ========== RECENT MOVEMENTS LIVE ==========
const fetchRecentMovementsLive = async () => {
  try {
    const response = await approvalService.getApprovals({ per_page: 5 });
    recentMovements.value = response.data || [];
    cacheService.setRecentMovements(recentMovements.value);
  } catch (error) {
    console.error('Failed to fetch movements:', error);
  }
};

// ========== HANDLE CHILD COMPONENT REFRESH ==========
const handleChildRefresh = () => {
  console.log('🔄 Child component requested refresh');
  fetchLiveData(true);
};

// ========== AUTO-REFRESH EVERY 30 SECONDS ==========
const startLiveRefresh = () => {
  // Clear existing interval
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value);
  }
  
  // Refresh every 30 seconds
  refreshInterval.value = setInterval(() => {
    console.log('🔄 Auto-refreshing live data...');
    fetchLiveData(false);
  }, 30000); // 30 seconds
};

// ========== WATCH FOR ROUTE CHANGES ==========
watch(() => useRoute().path, (newPath) => {
  if (newPath === '/dashboard') {
    console.log('👋 Returned to dashboard, refreshing...');
    fetchLiveData(true);
  }
});

// ========== WATCH FOR USER ROLE CHANGES ==========
watch(() => userRole.value, (newRole, oldRole) => {
  if (newRole && oldRole && newRole !== oldRole) {
    console.log('🔄 User role changed, reloading dashboard');
    loadFromCache();
    fetchLiveData(true);
  }
});

// ========== ON MOUNT ==========
onMounted(() => {
  if (isAuthReady.value) {
    // 1. Show cached data INSTANTLY
    loadFromCache();
    
    // 2. Fetch live data immediately (shows loading)
    fetchLiveData(true);
    
    // 3. Start auto-refresh
    startLiveRefresh();
  }
});

// ========== CLEANUP ==========
onUnmounted(() => {
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value);
  }
  if (refreshTimeout.value) {
    clearTimeout(refreshTimeout.value);
  }
});

// ========== HANDLE TASK TOGGLE ==========
const handleToggleTask = async (task) => {
  const newStatus = task.status === 'done' ? 'todo' : 'done';
  
  // Optimistic update
  const index = myTasks.value.findIndex(t => t.id === task.id);
  if (index !== -1) {
    myTasks.value[index].status = newStatus;
  }
  
  // Update stats
  if (clerkStats.value) {
    const tasks = myTasks.value;
    clerkStats.value.pending_tasks = tasks.filter(t => t.status !== 'done').length;
    clerkStats.value.completed_tasks = tasks.filter(t => t.status === 'done').length;
  }
  
  // Update cache
  const clerkData = {
    clerkStats: { ...clerkStats.value },
    myTasks: [...myTasks.value],
    timestamp: Date.now()
  };
  cacheService.setClerkDashboard(clerkData);
  
  // Show live indicator
  updateLastUpdated();
  
  // API call in background
  try {
    // await caseService.updateChecklistTaskStatus(task.case_id, task.id, newStatus);
    console.log('Task status updated:', task.id, newStatus);
  } catch (error) {
    console.error('Failed to update task:', error);
    // Revert on error
    fetchLiveData(true);
  }
};
</script>