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
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" v-if="isLoading"></span>
          <span class="relative inline-flex rounded-full h-3 w-3" :class="isLoading ? 'bg-emerald-500' : 'bg-emerald-500'"></span>
        </span>
        <span class="text-xs text-emerald-600">
          {{ isLoading ? 'Loading...' : 'Live' }}
        </span>
        <span class="text-xs text-slate-400 ml-2">
          Last updated: {{ lastUpdated }}
        </span>
      </div>
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
      :cases-loading="isLoading"
      :tasks-loading="isLoading"
      @toggle-task="handleToggleTask"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, markRaw } from 'vue';
import { useAuth } from '@/composables/useAuth';
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

// ========== LOADING STATE ==========
const isLoading = ref(false);
const lastUpdated = ref('Just now');
let refreshTimeout = null;

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

// ========== LOAD PENDING DOCUMENTS LIST ==========
const loadPendingDocumentsList = async () => {
  try {
    const response = await documentService.getPendingApprovals();
    pendingDocumentsList.value = response.data || [];
  } catch (error) {
    console.error('Failed to load pending documents:', error);
  }
};

// ========== FETCH DASHBOARD DATA ==========
const fetchDashboardData = async () => {
  if (isLoading.value) return;
  
  isLoading.value = true;
  
  const role = userRole.value;
  const userId = user.value?.id;
  
  try {
    if (role === 'admin') {
      await fetchAdminData();
    } else if (role === 'lawyer' && userId) {
      await fetchLawyerData(userId);
    } else if (role === 'clerk' && userId) {
      await fetchClerkData(userId);
    }
    
    await fetchRecentMovements();
    updateLastUpdated();
    
  } catch (error) {
    console.error('Dashboard data fetch failed:', error);
  } finally {
    isLoading.value = false;
  }
};

// ========== FETCH ADMIN DATA ==========
const fetchAdminData = async () => {
  try {
    const [casesCount, pendingCounts, clientsCount, docsCount, usersData] = await Promise.all([
      caseService.getCases({ per_page: 1 }),
      approvalService.getTotalPendingCount(),
      clientService.getAll({ limit: 1 }),
      documentService.getDocuments({ per_page: 1 }),
      caseService.getLookups()
    ]);
    
    const users = usersData.data?.users || [];
    
    stats.value = {
      total_cases: casesCount.meta?.total || 0,
      active_cases: 0,
      closed_cases: 0,
      archived_cases: 0,
      pending_approvals: pendingCounts.total || 0,
      total_clients: clientsCount.meta?.total || 0,
      total_documents: docsCount.meta?.total || 0
    };
    
    adminStats.value = {
      total_users: users.length,
      lawyers: users.filter(u => u.role === 'lawyer').length,
      clerks: users.filter(u => u.role === 'clerk').length,
      active_today: 0,
      logins_today: 0,
      activities_last_7_days: 0
    };
    
    pendingDocuments.value = pendingCounts.documents || 0;
    pendingMovements.value = pendingCounts.movements || 0;
    pendingTotal.value = pendingCounts.total || 0;
    
  } catch (error) {
    console.error('Admin data fetch error:', error);
  }
};

// ========== FETCH LAWYER DATA ==========
const fetchLawyerData = async (userId) => {
  try {
    const [casesResponse, pendingCounts, movementsResponse, pendingDocs] = await Promise.all([
      caseService.getCases({ 
        assigned_lawyer_id: userId,
        per_page: 5,
        case_status: 'active'
      }),
      approvalService.getTotalPendingCount(),
      approvalService.getApprovals({ status: 'PENDING', per_page: 5 }),
      documentService.getPendingApprovals()
    ]);
    
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
    
  } catch (error) {
    console.error('Lawyer data fetch error:', error);
  }
};

// ========== FETCH CLERK DATA ==========
const fetchClerkData = async (userId) => {
  try {
    const [casesResponse] = await Promise.all([
      caseService.getCases({ 
        assigned_clerk_id: userId,
        per_page: 1
      })
    ]);
    
    // You'll need to implement actual task fetching
    const tasks = [
      { id: 1, task: 'Review document for Case 2024-001', case_code: '2024-001', status: 'todo', due_date: '2024-03-20' },
      { id: 2, task: 'Prepare folder for Case 2024-002', case_code: '2024-002', status: 'in-progress', due_date: '2024-03-18' },
      { id: 3, task: 'File motion for Case 2024-003', case_code: '2024-003', status: 'todo', due_date: '2024-03-15' }
    ];
    
    myTasks.value = tasks;
    
    clerkStats.value = {
      assigned_cases: casesResponse.meta?.total || 0,
      total_tasks: tasks.length,
      pending_tasks: tasks.filter(t => t.status !== 'done').length,
      completed_tasks: tasks.filter(t => t.status === 'done').length
    };
    
  } catch (error) {
    console.error('Clerk data fetch error:', error);
  }
};

// ========== FETCH RECENT MOVEMENTS ==========
const fetchRecentMovements = async () => {
  try {
    const response = await approvalService.getApprovals({ per_page: 5 });
    recentMovements.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch movements:', error);
  }
};

// ========== UPDATE LAST UPDATED TIMER ==========
const updateLastUpdated = () => {
  lastUpdated.value = 'Just now';
  
  if (refreshTimeout) clearTimeout(refreshTimeout);
  
  refreshTimeout = setTimeout(() => {
    lastUpdated.value = 'Few seconds ago';
  }, 5000);
  
  refreshTimeout = setTimeout(() => {
    lastUpdated.value = 'Less than a minute';
  }, 10000);
};

// ========== WATCH FOR TAB VISIBILITY ==========
document.addEventListener('visibilitychange', () => {
  if (document.visibilityState === 'visible') {
    console.log('👋 Tab became visible, refreshing...');
    fetchDashboardData();
  }
});

// ========== ON MOUNT ==========
onMounted(() => {
  if (isAuthReady.value) {
    fetchDashboardData();
  }
});

// ========== CLEANUP ==========
onUnmounted(() => {
  if (refreshTimeout) clearTimeout(refreshTimeout);
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