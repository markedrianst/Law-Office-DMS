<template>
  <div class="min-h-screen bg-slate-50 p-4 md:p-6">
    <!-- Silent refresh indicator -->
    <div
      v-if="isRefreshing"
      class="fixed top-2 right-2 z-50 w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"
      :title="`Last updated: ${lastUpdated}`"
    ></div>

    <!-- Welcome Header with Date -->
    <div class="mb-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-[#1a4972]">
              Welcome back, {{ userName }}!
            </h1>
          </div>
          <p class="text-sm ml-4 pl-3 text-slate-500">{{ getRoleMessage }}</p>
        </div>
        <div class="ml-4 pl-3 flex items-center gap-3 text-sm bg-white rounded-lg shadow-sm px-4 py-2 border border-slate-100">
          <svg class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span class="font-medium text-slate-700">{{ currentDate }}</span>
        </div>
      </div>
    </div>

    <!-- Last updated & Refresh -->
    <div class="flex items-center justify-between mb-6 ml-4">
      <div class="text-xs text-slate-400 flex items-center gap-2">
        <span>📊 Dashboard data</span>
        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
        <span>{{ lastUpdated }}</span>
      </div>
      <button 
        @click="manualRefresh" 
        class="text-xs text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 bg-white px-3 py-1.5 rounded-lg shadow-sm border border-slate-200"
        :disabled="isRefreshing"
      >
        <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': isRefreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        {{ isRefreshing ? 'Refreshing...' : 'Refresh' }}
      </button>
    </div>

    <!-- Role-Based Dashboard -->
    <AdminDashboard
      v-if="isAdmin && AdminDashboard"
      :stats="dashboardData?.stats || {}"
      :admin-stats="dashboardData?.adminStats || {}"
      :recent-activities="dashboardData?.recentActivities || []"
      :pending-documents="dashboardData?.adminStats?.pending_documents || 0"
      :pending-movements="dashboardData?.adminStats?.pending_movements || 0"
      :pending-total="dashboardData?.adminStats?.pending_total || 0"
      :system-info="systemInfo"
      :today-schedules="todaySchedules"
      :upcoming-schedules="upcomingSchedules"
      :recent-users="recentUsers"
      :storage-stats="storageStats"
    />

    <LawyerDashboard
      v-else-if="isLawyer && LawyerDashboard"
      :stats="dashboardData?.stats || {}"
      :lawyer-stats="dashboardData?.lawyerStats || {}"
      :my-cases="dashboardData?.myCases || []"
      :pending-items="dashboardData?.pendingItems || []"
      :pending-documents="dashboardData?.pendingItems?.documents || 0"
      :pending-movements="dashboardData?.pendingItems?.movements || 0"
      :pending-total="dashboardData?.pendingItems?.total || 0"
      :today-schedules="todaySchedules"
      :upcoming-schedules="upcomingSchedules"
      :recent-documents="recentDocuments"
    />

    <ClerkDashboard
      v-else-if="isClerk && ClerkDashboard"
      :clerk-stats="dashboardData?.clerkStats || {}"
      :my-tasks="dashboardData?.myTasks || []"
      :recent-movements="dashboardData?.recentMovements || []"
      :today-schedules="todaySchedules"
      :upcoming-schedules="upcomingSchedules"
      :pending-tasks="pendingTasks"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, shallowRef, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import api from '@/services/api'
import hearingService from '@/services/hearingService'
import Swal from 'sweetalert2'

// Import appUtils
import { 
  getUserName,
  getUserRole,
  getDashboard,
  setDashboard,
  getUsers,
  getCases,
  getClients,
  listenForUpdates
} from '@/utils/appUtils'

const router = useRouter()
const { isAdmin, isLawyer, isClerk } = useAuth()

// ==================== STATE ====================
const userName = ref(getUserName())
const userRole = ref(getUserRole())
const dashboardData = ref(getDashboard())
const lastUpdated = ref(
  getDashboard() ? new Date().toLocaleTimeString() : 'Loading...'
)
const isRefreshing = ref(false)

// Additional data
const systemInfo = ref({
  version: '1.0.0',
  environment: import.meta.env.MODE || 'production',
  lastBackup: null,
  totalStorage: '--',
  usedStorage: '--',
  uptime: '--'
})

const todaySchedules = ref([])
const upcomingSchedules = ref([])
const recentUsers = ref([])
const recentDocuments = ref([])
const pendingTasks = ref(0)
const storageStats = ref({
  total: 0,
  used: 0,
  free: 0,
  percentage: 0
})

// Lazy loaded dashboards
const AdminDashboard = shallowRef(null)
const LawyerDashboard = shallowRef(null)
const ClerkDashboard = shallowRef(null)

// ==================== COMPUTED ====================
const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const getRoleMessage = computed(() => {
  const messages = {
    admin: 'Manage and oversee the entire system with full access',
    lawyer: 'Manage your cases, approve documents, and track movements',
    clerk: 'Handle daily tasks, track folder movements, and manage records'
  }
  return messages[userRole.value] || 'Welcome to your dashboard'
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

// ==================== FETCH ADDITIONAL DATA ====================
const fetchAdditionalData = async () => {
  try {
    // Fetch today's schedules/hearings
    const hearingsResponse = await hearingService.getHearings({ 
      date: new Date().toISOString().split('T')[0],
      per_page: 10
    })
    todaySchedules.value = hearingsResponse.data || []

    // Fetch upcoming schedules
    const upcomingResponse = await hearingService.getHearings({ 
      upcoming: true,
      per_page: 5
    })
    upcomingSchedules.value = upcomingResponse.data || []

    // Get recent users
    const users = getUsers() || []
    recentUsers.value = users
      .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      .slice(0, 5)

    // Get recent documents (placeholder - implement actual service)
    recentDocuments.value = [
      { id: 1, type: 'Affidavit', case: '2026-0002', status: 'pending' },
      { id: 2, type: 'Order', case: '2026-0001', status: 'approved' },
      { id: 3, type: 'Motion', case: '2026-0003', status: 'pending' }
    ]

    // Calculate pending tasks for clerk
    if (isClerk.value) {
      const tasks = dashboardData.value?.myTasks || []
      pendingTasks.value = tasks.filter(t => t.status !== 'done').length
    }

    // System info (mock data - replace with actual API calls)
    systemInfo.value = {
      version: '1.0.0',
      environment: import.meta.env.MODE || 'production',
      lastBackup: new Date().toLocaleDateString(),
      totalStorage: '100 GB',
      usedStorage: '45 GB',
      freeStorage: '55 GB',
      uptime: '15 days',
      databaseSize: '2.3 GB',
      totalRecords: {
        cases: getCases()?.length || 0,
        users: getUsers()?.length || 0,
        clients: getClients()?.length || 0
      }
    }

    // Storage stats
    storageStats.value = {
      total: 100,
      used: 45,
      free: 55,
      percentage: 45
    }

  } catch (error) {
    console.error('Failed to fetch additional data:', error)
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
      await fetchAdditionalData()
    }
  } catch (error) {
    console.error('Dashboard fetch failed:', error)
    if (error.response?.status === 401) {
      router.push('/')
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Failed to load dashboard data',
        timer: 2000,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      })
    }
  } finally {
    isRefreshing.value = false
  }
}

// ==================== MANUAL REFRESH ====================
const manualRefresh = () => {
  fetchDashboardData()
}

// ==================== UPDATE HANDLERS ====================
const handleDashboardUpdate = (event) => {
  if (event.detail) {
    dashboardData.value = event.detail
    lastUpdated.value = new Date().toLocaleTimeString()
  }
}

const handleUserUpdate = () => {
  userName.value = getUserName()
  userRole.value = getUserRole()
}

// ==================== LIFECYCLE ====================
onMounted(async () => {
  await loadComponents()
  
  // If no data, fetch it
  if (!dashboardData.value) {
    await fetchDashboardData()
  } else {
    await fetchAdditionalData()
  }

  // Listen for updates
  const cleanupDashboard = listenForUpdates('dashboard-updated', handleDashboardUpdate)
  const cleanupUser = listenForUpdates('user-updated', handleUserUpdate)

  // Auto-refresh every 30 seconds
  const interval = setInterval(() => {
    if (document.visibilityState === 'visible') {
      fetchDashboardData()
    }
  }, 30000)
  
  onUnmounted(() => {
    clearInterval(interval)
    if (cleanupDashboard) cleanupDashboard()
    if (cleanupUser) cleanupUser()
  })
})
</script>