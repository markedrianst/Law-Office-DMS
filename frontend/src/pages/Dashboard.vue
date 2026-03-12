<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Welcome Header (same for all roles) -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold text-[#1a4972]">Welcome back, {{ userName }}!</h1>
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
      :cases-loading="casesLoading"
      :tasks-loading="tasksLoading"
      @toggle-task="handleToggleTask"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, markRaw } from 'vue';
import { useAuth } from '@/composables/useAuth';
import caseService from '@/services/caseService';
import approvalService from '@/services/approvalService';
import checklistService from '@/services/caseService'; // You might need a separate service

// Import role-specific dashboards
import AdminDashboard from '@/pages/Admin/AdminDashboard.vue';
import LawyerDashboard from '@/pages/Lawyer/LawyerDashboard.vue';
import ClerkDashboard from '@/pages/Clerk/ClerkDashboard.vue';

const { user, userRole } = useAuth();

// ========== ROLE-BASED DASHBOARD COMPONENT ==========
const dashboardComponent = computed(() => {
  switch(userRole.value) {
    case 'admin':
      return markRaw(AdminDashboard);
    case 'lawyer':
      return markRaw(LawyerDashboard);
    case 'clerk':
      return markRaw(ClerkDashboard);
    default:
      return markRaw(AdminDashboard);
  }
});

// ========== USER INFO ==========
const userName = computed(() => user.value?.full_name || 'User');
const getRoleMessage = computed(() => {
  const messages = {
    admin: 'Manage and oversee the entire system',
    lawyer: 'Manage your cases and documents',
    clerk: 'Handle daily tasks and records'
  };
  return messages[userRole.value] || 'Welcome to the Document Management System';
});

// ========== COMMON STATS ==========
const stats = ref({
  total_cases: 0,
  active_cases: 0,
  closed_cases: 0,
  archived_cases: 0,
  pending_approvals: 0,
  total_clients: 0,
  total_documents: 0
});

// ========== ROLE-SPECIFIC STATS ==========
const adminStats = ref({
  total_users: 0,
  active_users: 0,
  lawyers: 0,
  clerks: 0,
  active_today: 0,
  logins_today: 0,
  activities_last_7_days: 0
});

const lawyerStats = ref({
  assigned_cases: 0,
  active_cases: 0
});

const clerkStats = ref({
  assigned_cases: 0,
  total_tasks: 0,
  pending_tasks: 0,
  completed_tasks: 0
});

// ========== DATA COLLECTIONS ==========
const myCases = ref([]);
const myTasks = ref([]);
const recentActivities = ref([]);
const recentMovements = ref([]);
const pendingItems = ref([]);

// ========== LOADING STATES ==========
const casesLoading = ref(false);
const tasksLoading = ref(false);

// ========== SIMPLE SESSION HELPERS ==========
const getFromSession = (key) => {
  try {
    const stored = sessionStorage.getItem(key);
    if (!stored) return [];
    return JSON.parse(stored).data || [];
  } catch {
    return [];
  }
};

// ========== LOAD COMMON STATS ==========
const loadCommonStats = async () => {
  try {
    // Get clients count from session
    stats.value.total_clients = getFromSession('master_clients').length;
    
    // Get cases stats from API
    const casesResponse = await caseService.getCases({ per_page: 1 });
    stats.value.total_cases = casesResponse.meta?.total || 0;
    
    // Get pending approvals count
    const approvalsResponse = await approvalService.getPendingCount();
    stats.value.pending_approvals = approvalsResponse.count || 0;
    
  } catch (error) {
    console.error('Failed to load common stats:', error);
  }
};

// ========== LOAD ADMIN STATS ==========
const loadAdminStats = async () => {
  try {
    // Get users from session
    const users = getFromSession('master_users');
    adminStats.value.lawyers = users.filter(u => u.role === 'lawyer').length;
    adminStats.value.clerks = users.filter(u => u.role === 'clerk').length;
    adminStats.value.total_users = users.length;
    adminStats.value.active_users = users.length; // You might have active status
    
    // You'll need to create these endpoints
    // adminStats.value.active_today = await getActiveToday();
    // adminStats.value.logins_today = await getLoginsToday();
    // adminStats.value.activities_last_7_days = await getActivitiesLast7Days();
    
  } catch (error) {
    console.error('Failed to load admin stats:', error);
  }
};

// ========== LOAD LAWYER STATS ==========
const loadLawyerStats = async (lawyerId) => {
  try {
    // Get cases assigned to this lawyer
    const response = await caseService.getCases({ 
      assigned_lawyer_id: lawyerId,
      per_page: 100
    });
    
    const cases = response.data || [];
    lawyerStats.value.assigned_cases = cases.length;
    lawyerStats.value.active_cases = cases.filter(c => c.case_status === 'active').length;
    
    // Get lawyer's cases for display
    myCases.value = cases.slice(0, 5); // Recent 5 cases
    
    // Get pending items for this lawyer to review
    // You'll need to create this endpoint
    // pendingItems.value = await getPendingForLawyer(lawyerId);
    
  } catch (error) {
    console.error('Failed to load lawyer stats:', error);
  }
};

// ========== LOAD CLERK STATS ==========
const loadClerkStats = async (clerkId) => {
  tasksLoading.value = true;
  try {
    // Get cases assigned to this clerk
    const casesResponse = await caseService.getCases({ 
      assigned_clerk_id: clerkId,
      per_page: 100
    });
    
    const cases = casesResponse.data || [];
    clerkStats.value.assigned_cases = cases.length;
    
    // Get tasks assigned to this clerk
    // You'll need to create this endpoint
    // const tasksResponse = await checklistService.getMyTasks(clerkId);
    // myTasks.value = tasksResponse.data || [];
    
    // Placeholder for now
    myTasks.value = [
      { id: 1, task: 'Review document for Case 2024-001', case_code: '2024-001', status: 'todo', due_date: '2024-03-20' },
      { id: 2, task: 'Prepare folder for Case 2024-002', case_code: '2024-002', status: 'in-progress', due_date: '2024-03-18' },
      { id: 3, task: 'File motion for Case 2024-003', case_code: '2024-003', status: 'todo', due_date: '2024-03-15' }
    ];
    
    // Calculate task stats
    const tasks = myTasks.value;
    clerkStats.value.total_tasks = tasks.length;
    clerkStats.value.pending_tasks = tasks.filter(t => t.status !== 'done').length;
    clerkStats.value.completed_tasks = tasks.filter(t => t.status === 'done').length;
    
  } catch (error) {
    console.error('Failed to load clerk stats:', error);
  } finally {
    tasksLoading.value = false;
  }
};

// ========== LOAD RECENT MOVEMENTS ==========
const loadRecentMovements = async () => {
  try {
    // You'll need to create this endpoint
    // const response = await caseService.getRecentMovements();
    // recentMovements.value = response.data || [];
    
    // Placeholder for now
    recentMovements.value = [
      { id: 1, source: 'folder', type: 'OUT', from_to: 'Court', case_code: '2024-001', created_at: new Date() },
      { id: 2, source: 'checklist', type: 'IN', from_to: 'Client', task_name: 'Affidavit', case_code: '2024-002', created_at: new Date() }
    ];
    
  } catch (error) {
    console.error('Failed to load recent movements:', error);
  }
};

// ========== LOAD RECENT ACTIVITIES (Admin only) ==========
const loadRecentActivities = async () => {
  try {
    // You'll need to create this endpoint
    // const response = await caseService.getRecentActivities();
    // recentActivities.value = response.data || [];
    
    // Placeholder for now
    recentActivities.value = [
      { id: 1, user: 'Admin User', action: 'created case 2024-001', created_at: new Date() },
      { id: 2, user: 'John Doe', action: 'updated case 2024-002', created_at: new Date() }
    ];
    
  } catch (error) {
    console.error('Failed to load recent activities:', error);
  }
};

// ========== HANDLE TASK STATUS TOGGLE (Clerk) ==========
const handleToggleTask = async (task) => {
  const newStatus = task.status === 'done' ? 'todo' : 'done';
  
  try {
    // Optimistic update
    const index = myTasks.value.findIndex(t => t.id === task.id);
    if (index !== -1) {
      myTasks.value[index].status = newStatus;
    }
    
    // Update task stats
    clerkStats.value.pending_tasks = myTasks.value.filter(t => t.status !== 'done').length;
    clerkStats.value.completed_tasks = myTasks.value.filter(t => t.status === 'done').length;
    
    // API call
    // await checklistService.updateTaskStatus(task.id, newStatus);
    
  } catch (error) {
    // Revert on error
    const index = myTasks.value.findIndex(t => t.id === task.id);
    if (index !== -1) {
      myTasks.value[index].status = task.status;
    }
    console.error('Failed to toggle task:', error);
  }
};

// ========== BACKGROUND REFRESH ==========
const refreshing = new Set();

const refreshMasterData = async () => {
  const key = 'master_refresh';
  if (refreshing.has(key)) return;
  
  refreshing.add(key);
  try {
    const response = await caseService.getLookups();
    const data = response.data;
    
    if (data.categories) {
      sessionStorage.setItem('master_categories', JSON.stringify({
        data: data.categories,
        timestamp: Date.now()
      }));
    }
    
    if (data.stages) {
      sessionStorage.setItem('master_stages', JSON.stringify({
        data: data.stages,
        timestamp: Date.now()
      }));
    }
    
    if (data.courts) {
      sessionStorage.setItem('master_courts', JSON.stringify({
        data: data.courts,
        timestamp: Date.now()
      }));
    }
    
    if (data.users) {
      const users = data.users;
      sessionStorage.setItem('master_users', JSON.stringify({
        data: users,
        timestamp: Date.now()
      }));
      
      sessionStorage.setItem('master_lawyers', JSON.stringify({
        data: users.filter(u => u.role === 'lawyer'),
        timestamp: Date.now()
      }));
      
      sessionStorage.setItem('master_clerks', JSON.stringify({
        data: users.filter(u => u.role === 'clerk'),
        timestamp: Date.now()
      }));
    }
    
  } catch (error) {
    console.error('Background refresh failed:', error);
  } finally {
    refreshing.delete(key);
  }
};

const refreshClients = async () => {
  const key = 'clients_refresh';
  if (refreshing.has(key)) return;
  
  refreshing.add(key);
  try {
    const { default: clientService } = await import('@/services/clientService');
    const response = await clientService.getAll({ limit: 100 });
    
    sessionStorage.setItem('master_clients', JSON.stringify({
      data: response.data || [],
      timestamp: Date.now()
    }));
    
    stats.value.total_clients = response.data?.length || 0;
    
  } catch (error) {
    console.error('Clients refresh failed:', error);
  } finally {
    refreshing.delete(key);
  }
};

// ========== LOAD ALL DATA BASED ON ROLE ==========
const loadDashboardData = async () => {
  const role = userRole.value;
  
  // Load common stats for all roles
  await loadCommonStats();
  
  // Load role-specific data
  switch(role) {
    case 'admin':
      await loadAdminStats();
      await loadRecentActivities();
      break;
      
    case 'lawyer':
      await loadLawyerStats(user.value?.id);
      break;
      
    case 'clerk':
      await loadClerkStats(user.value?.id);
      break;
  }
  
  // Load recent movements for all roles (optional)
  await loadRecentMovements();
};

// ========== ON MOUNT ==========
onMounted(() => {
  // Load dashboard data
  loadDashboardData();
  
  // Refresh master data in background
  refreshMasterData();
  refreshClients();
});
</script>