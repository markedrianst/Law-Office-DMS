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
    </div>
    <!-- Role-Based Dashboard -->
    <AdminDashboard
      v-if="isAdmin && AdminDashboard"
      :stats="dashboardData?.stats || {}"
      :admin-stats="dashboardData?.adminStats || {}"
      :recent-activities="dashboardData?.recentActivities || []"
      :upcoming-hearings="dashboardData?.upcomingHearings || []"
      :hearing-stats="dashboardData?.hearingStats || {}"
    />

    <LawyerDashboard
      v-else-if="isLawyer && LawyerDashboard"
      :stats="dashboardData?.stats || {}"
      :lawyer-stats="dashboardData?.lawyerStats || {}"
      :my-cases="dashboardData?.myCases || []"
      :pending-items="dashboardData?.pendingItems || {}"
      :upcoming-hearings="dashboardData?.upcomingHearings || []"
      :hearing-stats="dashboardData?.hearingStats || {}"
    />

    <ClerkDashboard
      v-else-if="isClerk && ClerkDashboard"
      :clerk-stats="dashboardData?.clerkStats || {}"
      :my-tasks="dashboardData?.myTasks || []"
      :upcoming-hearings="dashboardData?.upcomingHearings || []"
      :hearing-stats="dashboardData?.hearingStats || {}"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, shallowRef } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import api from '@/services/api'
import Swal from 'sweetalert2'

// Import appUtils
import { 
  getUserName,
  getUserRole,
  getDashboard,
  setDashboard,
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

const manualRefresh = () => {
  fetchDashboardData()
}

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
  
  if (!dashboardData.value) {
    await fetchDashboardData()
  }

  const cleanupDashboard = listenForUpdates('dashboard-updated', handleDashboardUpdate)
  const cleanupUser = listenForUpdates('user-updated', handleUserUpdate)

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