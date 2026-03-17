<template>
  <div class="min-h-screen bg-slate-50">
    <!-- Silent refresh indicator (tiny, non-blocking) -->
    <div
      v-if="isRefreshing"
      class="fixed top-2 right-2 z-50 w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"
      :title="`Last updated: ${lastUpdated}`"
    ></div>

    <!-- Welcome Header -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold text-[#1a4972]">Welcome back, {{ userName }}!</h1>
      </div>
      <p class="text-sm text-slate-500 ml-4">{{ getRoleMessage }}</p>
    </div>

    <!-- Last updated -->
    <div class="text-xs text-slate-400 mb-2 ml-4">
      <span>📊 Dashboard data</span>
      <span class="mx-2">•</span>
      <span>{{ lastUpdated }}</span>
      <button 
        @click="manualRefresh" 
        class="ml-3 text-blue-600 hover:text-blue-800 text-xs font-medium"
        :disabled="isRefreshing"
      >
        {{ isRefreshing ? 'Refreshing...' : '↻ Refresh' }}
      </button>
    </div>

    <!-- Dashboard Content - Always shows, even if data is loading -->
    <div v-if="dashboardData">
      <!-- Admin Dashboard -->
      <AdminDashboard
        v-if="isAdmin && AdminDashboard"
        :stats="dashboardData?.stats || {}"
        :admin-stats="dashboardData?.adminStats || {}"
        :recent-activities="dashboardData?.recentActivities || []"
        :pending-documents="dashboardData?.adminStats?.pending_documents || 0"
        :pending-movements="dashboardData?.adminStats?.pending_movements || 0"
        :pending-total="dashboardData?.adminStats?.pending_total || 0"
      />

      <!-- Lawyer Dashboard -->
      <LawyerDashboard
        v-else-if="isLawyer && LawyerDashboard"
        :stats="dashboardData?.stats || {}"
        :lawyer-stats="dashboardData?.lawyerStats || {}"
        :my-cases="dashboardData?.myCases || []"
        :pending-items="dashboardData?.pendingItems || {}"
        :pending-total="dashboardData?.pendingItems?.total || 0"
      />

      <!-- Clerk Dashboard -->
      <ClerkDashboard
        v-else-if="isClerk && ClerkDashboard"
        :clerk-stats="dashboardData?.clerkStats || {}"
        :my-tasks="dashboardData?.myTasks || []"
        :recent-movements="dashboardData?.recentMovements || []"
      />
    </div>

    <!-- Always show dashboard - never loading state -->
    <div v-else class="dashboard-placeholder">
      <AdminDashboard
        v-if="isAdmin && AdminDashboard"
        :stats="{}"
        :admin-stats="{}"
        :recent-activities="[]"
        :pending-documents="0"
        :pending-movements="0"
        :pending-total="0"
      />
      <LawyerDashboard
        v-else-if="isLawyer && LawyerDashboard"
        :stats="{}"
        :lawyer-stats="{}"
        :my-cases="[]"
        :pending-items="{}"
        :pending-total="0"
      />
      <ClerkDashboard
        v-else-if="isClerk && ClerkDashboard"
        :clerk-stats="{}"
        :my-tasks="[]"
        :recent-movements="[]"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, shallowRef, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import api from '@/services/api'

// Import appUtils getters
import { 
  getUserName,
  getUserRole,
  getDashboard,
  setDashboard,
  getCategories,
  getCourts,
  getDocuments,
  getUsers,
  getClients,
  getNotifications,
  getUnreadCount
} from '@/utils/appUtils'

const route = useRoute()
const { isAdmin, isLawyer, isClerk } = useAuth()

// ==================== INSTANT DATA from appUtils ====================
const userName = ref(getUserName())
const userRole = ref(getUserRole())
const dashboardData = ref(getDashboard())
const lastUpdated = ref(
  getDashboard() ? new Date().toLocaleTimeString() : 'Loading...'
)
const isRefreshing = ref(false)

// Lazy loaded dashboards
const AdminDashboard = shallowRef(null)
const LawyerDashboard = shallowRef(null)
const ClerkDashboard = shallowRef(null)

// Computed properties for dashboard data
const dashboardStats = computed(() => dashboardData.value?.stats || {})
const adminStats = computed(() => dashboardData.value?.adminStats || {})
const recentActivities = computed(() => dashboardData.value?.recentActivities || [])
const lawyerStats = computed(() => dashboardData.value?.lawyerStats || {})
const myCases = computed(() => dashboardData.value?.myCases || [])
const pendingItems = computed(() => dashboardData.value?.pendingItems || {})
const clerkStats = computed(() => dashboardData.value?.clerkStats || {})
const myTasks = computed(() => dashboardData.value?.myTasks || [])
const recentMovements = computed(() => dashboardData.value?.recentMovements || [])
const pendingDocuments = computed(() => dashboardData.value?.adminStats?.pending_documents || 0)
const pendingMovements = computed(() => dashboardData.value?.adminStats?.pending_movements || 0)
const pendingTotal = computed(() => dashboardData.value?.adminStats?.pending_total || 0)

const getRoleMessage = computed(() => {
  const messages = {
    admin: 'Manage and oversee the entire system',
    lawyer: 'Manage your cases and documents',
    clerk: 'Handle daily tasks and records'
  }
  return messages[userRole.value] || 'Welcome'
})

// ==================== LOAD COMPONENTS ====================
const loadComponents = async () => {
  try {
    if (isAdmin.value && !AdminDashboard.value) {
      const module = await import('@/pages/Admin/AdminDashboard.vue')
      AdminDashboard.value = module.default
    }
    if (isLawyer.value && !LawyerDashboard.value) {
      const module = await import('@/pages/Lawyer/LawyerDashboard.vue')
      LawyerDashboard.value = module.default
    }
    if (isClerk.value && !ClerkDashboard.value) {
      const module = await import('@/pages/Clerk/ClerkDashboard.vue')
      ClerkDashboard.value = module.default
    }
  } catch (err) {
    console.error('Component load error:', err)
  }
}

// ==================== FETCH DASHBOARD DATA ====================
const fetchDashboardData = async () => {
  if (isRefreshing.value) return

  isRefreshing.value = true

  try {
    const response = await api.get('/dashboard')
    
    if (response.data) {
      dashboardData.value = response.data
      lastUpdated.value = new Date().toLocaleTimeString()
      setDashboard(response.data)
    }
  } catch (error) {
    console.error('Dashboard fetch failed:', error)
    if (error.response?.status === 401) {
      window.location.href = '/'
    }
  } finally {
    isRefreshing.value = false
  }
}

// ==================== MANUAL REFRESH ====================
const manualRefresh = () => {
  fetchDashboardData()
}

// ==================== LIFECYCLE ====================
onMounted(async () => {
  await loadComponents()
  
  // If no data, fetch it (shouldn't happen because login loads it)
  if (!dashboardData.value) {
    await fetchDashboardData()
  }

  // Auto-refresh every 30 seconds
  const interval = setInterval(() => {
    if (document.visibilityState === 'visible') {
      fetchDashboardData()
    }
  }, 30000)
  
  onUnmounted(() => clearInterval(interval))
})

// Update user info when it changes
watch(() => getUserName(), (newName) => {
  userName.value = newName
})

watch(() => getUserRole(), (newRole) => {
  userRole.value = newRole
})
</script>