<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Welcome Header - Always visible -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold text-[#1a4972]">Welcome back, {{ userName || 'User' }}!</h1>
      </div>
      <p class="text-sm text-slate-500 ml-4">{{ getRoleMessage }}</p>
    </div>

    <!-- Error message (if any) -->
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
      {{ error }}
    </div>

    <!-- Dashboard Content - ALWAYS RENDERED, just with empty data first -->
    <component
      v-if="dashboardComponent"
      :is="dashboardComponent"
      :stats="dashboardStats"
      :admin-stats="dashboardAdminStats"
      :recent-activities="dashboardRecentActivities"
      :pending-documents="dashboardPendingDocuments"
      :pending-movements="dashboardPendingMovements"
      :pending-total="dashboardPendingTotal"
      :lawyer-stats="dashboardLawyerStats"
      :my-cases="dashboardMyCases"
      :pending-items="dashboardPendingItems"
      :clerk-stats="dashboardClerkStats"
      :my-tasks="dashboardMyTasks"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, shallowRef } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/Useauth';
import dashboardService from '@/services/dashboardService';

const router = useRouter();
const { user, userRole, userName, isAuthenticated } = useAuth();

// State - start with EMPTY data structure
const dashboardData = ref(null);
const error = ref('');
let refreshInterval = null;

// Lazy load dashboards
const AdminDashboard = shallowRef(null);
const LawyerDashboard = shallowRef(null);
const ClerkDashboard = shallowRef(null);

// Computed props with FALLBACKS - ALWAYS return something
const dashboardStats = computed(() => dashboardData.value?.stats || { total_cases: 0, active_cases: 0, total_clients: 0, pending_approvals: 0 });
const dashboardAdminStats = computed(() => dashboardData.value?.adminStats || { total_users: 0, lawyers: 0, clerks: 0, pending_documents: 0, pending_movements: 0, pending_total: 0 });
const dashboardRecentActivities = computed(() => dashboardData.value?.recentActivities || []);
const dashboardPendingDocuments = computed(() => dashboardData.value?.adminStats?.pending_documents || 0);
const dashboardPendingMovements = computed(() => dashboardData.value?.adminStats?.pending_movements || 0);
const dashboardPendingTotal = computed(() => dashboardData.value?.adminStats?.pending_total || 0);

const dashboardLawyerStats = computed(() => dashboardData.value?.lawyerStats || { assigned_cases: 0, active_cases: 0 });
const dashboardMyCases = computed(() => dashboardData.value?.myCases || []);
const dashboardPendingItems = computed(() => dashboardData.value?.pendingItems || { documents: 0, movements: 0, total: 0 });

const dashboardClerkStats = computed(() => dashboardData.value?.clerkStats || { assigned_cases: 0, total_tasks: 0, pending_tasks: 0, completed_tasks: 0 });
const dashboardMyTasks = computed(() => dashboardData.value?.myTasks || []);

// Load from cache IMMEDIATELY (synchronous)
try {
  const cached = sessionStorage.getItem('dashboard_cache');
  if (cached) {
    dashboardData.value = JSON.parse(cached).data;
    console.log('📦 Dashboard loaded from cache');
  }
} catch (e) {}

// Computed
const dashboardComponent = computed(() => {
  const role = userRole.value;
  if (role === 'admin') return AdminDashboard.value;
  if (role === 'lawyer') return LawyerDashboard.value;
  if (role === 'clerk') return ClerkDashboard.value;
  return null;
});

const getRoleMessage = computed(() => {
  const messages = {
    admin: 'Manage and oversee the entire system',
    lawyer: 'Manage your cases and documents',
    clerk: 'Handle daily tasks and records'
  };
  return messages[userRole.value] || 'Welcome';
});

// Fetch fresh data (updates in background)
const fetchDashboardData = async () => {
  if (!isAuthenticated.value) return;
  
  try {
    error.value = '';
    const data = await dashboardService.getDashboardData();
    dashboardData.value = data;
    console.log('📊 Dashboard updated with fresh data');
  } catch (err) {
    if (err.response?.status !== 401) {
      console.error('Background refresh failed:', err);
      error.value = 'Unable to load latest data';
    }
  }
};

// Load components
const loadDashboardComponent = async () => {
  const role = userRole.value;
  
  try {
    if (role === 'admin' && !AdminDashboard.value) {
      const module = await import('@/pages/Admin/AdminDashboard.vue');
      AdminDashboard.value = module.default;
    } else if (role === 'lawyer' && !LawyerDashboard.value) {
      const module = await import('@/pages/Lawyer/LawyerDashboard.vue');
      LawyerDashboard.value = module.default;
    } else if (role === 'clerk' && !ClerkDashboard.value) {
      const module = await import('@/pages/Clerk/ClerkDashboard.vue');
      ClerkDashboard.value = module.default;
    }
  } catch (err) {
    console.error('Failed to load dashboard component:', err);
  }
};

// Initialize
onMounted(async () => {
  if (!isAuthenticated.value) {
    router.replace('/');
    return;
  }
  
  // Load component (this happens in background)
  await loadDashboardComponent();
  
  // If we have cached data, update in background
  if (dashboardData.value) {
    fetchDashboardData();
  } else {
    // No cache, need to fetch
    await fetchDashboardData();
  }
  
  // Refresh every 30 seconds
  refreshInterval = setInterval(fetchDashboardData, 30000);
});

// Cleanup
onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>