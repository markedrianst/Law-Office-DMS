<template>
  <header class="flex items-center justify-between px-4 md:px-6 h-16 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] border-b border-white/10 font-sans relative z-40 flex-shrink-0 gap-3">
    <h1 class="text-sm md:text-base font-semibold text-white/90 tracking-wide flex-1 text-left truncate">
      {{ pageTitle }}
    </h1>
    
    <div class="flex items-center gap-2 flex-shrink-0">
      <!-- Notification Bell with Live Badge -->
      <div class="relative" ref="notificationDropdownRef">
        <button 
          class="relative w-9 h-9 rounded-lg border border-white/20 bg-white/10 text-white/80 hover:bg-white/20 flex items-center justify-center transition-all"
          :class="{ 'bg-white/20': isNotificationOpen, 'animate-pulse': pendingCount > 0 }"
          @click="toggleNotification"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <span v-if="pendingCount > 0" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center border-2 border-[#0f2f4a]">
            {{ pendingCount > 9 ? '9+' : pendingCount }}
          </span>
        </button>

        <!-- Notifications Dropdown -->
        <transition
          enter-active-class="transition ease-out duration-150"
          enter-from-class="opacity-0 -translate-y-2 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0 -translate-y-2 scale-95"
        >
          <div v-if="isNotificationOpen" class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl overflow-hidden z-[9999] max-sm:fixed max-sm:left-3 max-sm:right-3 max-sm:w-auto">
            <!-- Header -->
            <div class="p-4 border-b border-slate-200 flex items-start justify-between">
              <div>
                <h3 class="text-sm font-bold text-slate-900">
                  {{ notificationTitle }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ pendingCount }} item(s)</p>
              </div>
              <div class="flex gap-1">
                <button 
                  v-if="pendingCount > 0" 
                  @click="markAllAsRead" 
                  class="px-2 py-1 text-xs font-semibold text-[#1a4972] hover:bg-slate-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
                  :disabled="isLoadingNotifications"
                >
                  <span v-if="isLoadingNotifications" class="w-3 h-3 border-2 border-[#1a4972] border-t-transparent rounded-full animate-spin"></span>
                  <span v-else>Mark all read</span>
                </button>
                <button 
                  @click="fetchNotifications" 
                  class="w-7 h-7 rounded-lg hover:bg-slate-100 flex items-center justify-center text-slate-600 transition-colors disabled:opacity-50"
                  :disabled="isLoadingNotifications"
                  title="Refresh"
                >
                  <svg class="w-4 h-4" :class="{ 'animate-spin': isLoadingNotifications }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- List -->
            <div class="max-h-96 overflow-y-auto">
              <div v-if="isLoadingNotifications" class="p-8 flex flex-col items-center gap-2 text-slate-500">
                <svg class="animate-spin w-5 h-5" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span class="text-xs">Loading...</span>
              </div>

              <template v-else>
                <div v-if="notifications.length === 0" class="p-12 flex flex-col items-center gap-3 text-slate-400 text-center">
                  <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                  </svg>
                  <p class="text-sm">No notifications</p>
                </div>

                <div v-else class="divide-y divide-slate-100">
                  <div 
                    v-for="item in notifications" 
                    :key="item.id" 
                    class="p-4 hover:bg-slate-50 cursor-pointer transition-colors flex items-start gap-3"
                    @click="goToNotification(item)"
                  >
                    <div 
                      class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                      :class="getIconClass(item)"
                    >
                      <svg v-if="item.source === 'folder'" class="w-4 h-4" :class="getIconColor(item)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                      </svg>
                      <svg v-else class="w-4 h-4" :class="getIconColor(item)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                      </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center flex-wrap gap-1">
                        <span class="text-sm font-semibold text-slate-800">{{ getItemTitle(item) }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded" :class="getStatusClass(item)">
                          {{ item.approval_status }}
                        </span>
                      </div>
                      
                      <p class="text-xs text-slate-600 mt-0.5">
                        <span class="font-medium">Case:</span> {{ item.case_code || `Case #${item.case_id}` }} • 
                        <span class="font-medium">From/To:</span> {{ item.from_to || '—' }}
                      </p>
                      
                      <p v-if="item.notes" class="text-xs text-slate-500 mt-1 italic">
                        📝 {{ item.notes.length > 50 ? item.notes.substring(0, 50) + '...' : item.notes }}
                      </p>
                      
                      <p class="text-[10px] text-slate-400 mt-1">{{ timeAgo(item.created_at) }}</p>
                    </div>
                    
                    <span v-if="!item.read" class="w-2 h-2 rounded-full bg-[#1a4972] flex-shrink-0 mt-2"></span>
                  </div>
                </div>
              </template>
            </div>

            <!-- Footer -->
            <div v-if="pendingCount > 0" class="p-3 border-t border-slate-200 text-center bg-slate-50">
              <router-link :to="notificationLink" @click="isNotificationOpen = false" class="text-xs font-semibold text-[#1a4972] hover:underline">
                View all →
              </router-link>
            </div>
          </div>
        </transition>
      </div>

      <!-- User Menu -->
      <div class="relative" ref="dropdownRef">
        <button 
          class="flex items-center gap-2 sm:gap-2.5 pl-1 sm:pl-1.5 pr-2 sm:pr-3 py-1 rounded-xl border border-white/20 bg-white/10 hover:bg-white/20 transition-colors whitespace-nowrap"
          :class="{ 'bg-white/20': isUserMenuOpen }"
          @click="isUserMenuOpen = !isUserMenuOpen"
        >
          <div class="w-8 h-8 rounded-full bg-gradient-to-br from-white/30 to-white/10 border-2 border-white/30 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">
            {{ userInitials }}
          </div>
          <div class="hidden sm:flex flex-col text-left max-w-[100px]">
            <span class="text-xs font-semibold text-white truncate">{{ userName }}</span>
            <span class="text-[11px] text-white/50 capitalize truncate">{{ userRole }}</span>
          </div>
          <svg class="w-3 h-3 text-white/50 transition-transform duration-200" :class="{ 'rotate-180': isUserMenuOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </button>

        <!-- User Dropdown -->
        <transition
          enter-active-class="transition ease-out duration-150"
          enter-from-class="opacity-0 -translate-y-2 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0 -translate-y-2 scale-95"
        >
          <div v-if="isUserMenuOpen" class="absolute right-0 top-full mt-2 w-56 bg-[#071626] backdrop-blur-xl border border-white/20 rounded-xl shadow-2xl overflow-hidden z-[9999] max-sm:fixed max-sm:left-3 max-sm:right-3 max-sm:w-auto">
            <!-- Header -->
            <div class="p-4 border-b border-white/10 flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-r from-[#1a4972] to-[#2d6db5] text-white text-sm font-bold flex items-center justify-center flex-shrink-0">
                {{ userInitials }}
              </div>
              <div class="min-w-0">
                <p class="text-sm font-bold text-white truncate">{{ userName }}</p>
                <p class="text-xs text-white/40 capitalize truncate">{{ userRole }}</p>
              </div>
            </div>

            <!-- Body -->
            <div class="p-1.5">
              <router-link 
                to="/account-setting" 
                @click="isUserMenuOpen = false" 
                class="flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-white/80 hover:bg-white/10 transition-colors"
                :class="{ 'bg-white/10': route.path === '/account' }"
              >
                <span class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </span>
                Account Settings
              </router-link>

              <div class="h-px bg-white/10 my-1.5 mx-2"></div>

              <button 
                @click="askLogout" 
                class="flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-red-300/80 hover:bg-red-500/10 transition-colors"
              >
                <span class="w-7 h-7 rounded-lg bg-red-500/10 flex items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                  </svg>
                </span>
                Logout
              </button>
            </div>
          </div>
        </transition>
      </div>

      <!-- Hamburger (mobile only) -->
      <button 
        v-if="showHamburger" 
        class="flex flex-col gap-1 w-9 h-9 items-center justify-center bg-white/10 border border-white/20 rounded-lg hover:bg-white/20 transition-colors md:hidden"
        :class="{ 'bg-white/20': sidebarOpen }"
        @click="$emit('toggle-sidebar')"
      >
        <span class="w-5 h-0.5 bg-white/80 rounded-full transition-transform duration-300" :class="{ 'rotate-45 translate-y-1.5': sidebarOpen }"></span>
        <span class="w-5 h-0.5 bg-white/80 rounded-full transition-opacity duration-300" :class="{ 'opacity-0': sidebarOpen }"></span>
        <span class="w-5 h-0.5 bg-white/80 rounded-full transition-transform duration-300" :class="{ '-rotate-45 -translate-y-1.5': sidebarOpen }"></span>
      </button>
    </div>
  </header>

  <!-- Logout Confirmation Modal -->
  <Teleport to="body">
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div v-if="showLogoutModal" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" @click.self="showLogoutModal = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-8 pt-6 flex flex-col items-center text-center gap-4 max-sm:max-w-[90%] max-sm:p-6">
          <div class="w-14 h-14 rounded-2xl bg-red-50 border border-red-200 flex items-center justify-center text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </div>

          <div>
            <h2 class="text-lg font-bold text-slate-900">Sign out?</h2>
            <p class="text-sm text-slate-500 mt-1">You'll need to log back in to access the system.</p>
          </div>

          <div class="flex gap-2 w-full mt-2 max-sm:flex-col">
            <button 
              class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded-xl hover:bg-slate-200 transition-colors disabled:opacity-50"
              :disabled="isLoggingOut"
              @click="showLogoutModal = false"
            >
              Cancel
            </button>
            <button 
              class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
              :disabled="isLoggingOut"
              @click="confirmLogout"
            >
              <span v-if="isLoggingOut" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
              {{ isLoggingOut ? 'Signing out…' : 'Yes, sign out' }}
            </button>
          </div>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import authService from '@/services/auth'
import approvalService from '@/services/approvalService'
import cacheService from '@/services/cacheService'

const router = useRouter()
const route = useRoute()
const { userName, userRole, userInitials, clearSession } = useAuth()

// User menu state
const isUserMenuOpen = ref(false)
const dropdownRef = ref(null)

// Notification state
const isNotificationOpen = ref(false)
const notificationDropdownRef = ref(null)
const notifications = ref([])
const pendingCount = ref(0)
const isLoadingNotifications = ref(false)
const refreshInterval = ref(null)

// Logout state
const showLogoutModal = ref(false)
const isLoggingOut = ref(false)
const showHamburger = ref(false)

const props = defineProps({
  sidebarOpen: {
    type: Boolean,
    default: false
  }
})

defineEmits(['toggle-sidebar'])

const pageTitle = computed(() => {
  const titles = {
    '/dashboard': 'Dashboard',
    '/account': 'Account Settings',
    '/usermanagement': 'User Management',
    '/casemaster': 'Case Master',
    '/approvals': 'Approvals',
    '/audit-trail': 'Audit Trail',
    '/casecategories': 'Case Categories',
    '/courts': 'Courts & Offices',
    '/documents': 'Document Types'
  }
  return titles[route.path] || 'Dashboard'
})

// ==================== ROLE-BASED NOTIFICATION SETTINGS ====================
const notificationTitle = computed(() => {
  const role = userRole.value?.toLowerCase()
  if (role === 'admin' || role === 'lawyer') {
    return 'Pending Approvals'
  } else if (role === 'clerk') {
    return 'Movement Updates'
  }
  return 'Notifications'
})

const notificationLink = computed(() => {
  const role = userRole.value?.toLowerCase()
  if (role === 'admin' || role === 'lawyer') {
    return '/approvals'
  } else if (role === 'clerk') {
    return '/clerk-tracker' // or wherever clerks view their updates
  }
  return '/dashboard'
})

// ==================== NOTIFICATION FUNCTIONS ====================
const fetchNotifications = async () => {
  isLoadingNotifications.value = true
  try {
    const role = userRole.value?.toLowerCase()
    let response
    
    if (role === 'admin' || role === 'lawyer') {
      // Admin/Lawyer: Show PENDING approvals
      response = await approvalService.getApprovals({ 
        status: 'PENDING',
        per_page: 10
      })
      
      // Mark items as unread for demo (in real app, this comes from backend)
      const items = response.data || []
      notifications.value = items.map(item => ({
        ...item,
        read: false
      }))
      pendingCount.value = response.stats?.pending || 0
      
    } else if (role === 'clerk') {
      // Clerk: Show APPROVED and REJECTED movements
      // You might need a separate API endpoint for this
      const [approved, rejected] = await Promise.all([
        approvalService.getApprovals({ 
          status: 'APPROVED',
          per_page: 5
        }),
        approvalService.getApprovals({ 
          status: 'REJECTED',
          per_page: 5
        })
      ])
      
      // Combine and sort by date
      const allItems = [
        ...(approved.data || []).map(item => ({ ...item, read: false })),
        ...(rejected.data || []).map(item => ({ ...item, read: false }))
      ].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      
      notifications.value = allItems.slice(0, 10)
      pendingCount.value = allItems.length
    }
    
    console.log('📬 Fetched notifications:', pendingCount.value)
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    isLoadingNotifications.value = false
  }
}

// Helper functions for styling based on item type
const getIconClass = (item) => {
  if (item.source === 'folder') {
    return item.type === 'OUT' ? 'bg-orange-100' : 'bg-emerald-100'
  } else {
    return item.type === 'OUT' ? 'bg-purple-100' : 'bg-indigo-100'
  }
}

const getIconColor = (item) => {
  if (item.source === 'folder') {
    return item.type === 'OUT' ? 'text-orange-600' : 'text-emerald-600'
  } else {
    return item.type === 'OUT' ? 'text-purple-600' : 'text-indigo-600'
  }
}

const getStatusClass = (item) => {
  const status = item.approval_status?.toLowerCase()
  if (status === 'pending') return 'bg-amber-100 text-amber-700'
  if (status === 'approved') return 'bg-emerald-100 text-emerald-700'
  if (status === 'rejected') return 'bg-red-100 text-red-700'
  return 'bg-slate-100 text-slate-600'
}

const getItemTitle = (item) => {
  if (item.source === 'folder') {
    return 'Folder Movement'
  } else {
    return item.task_name || 'Checklist Movement'
  }
}

const timeAgo = (dateStr) => {
  if (!dateStr) return ''
  const seconds = Math.floor((new Date() - new Date(dateStr)) / 1000)
  
  if (seconds < 5) return 'just now'
  if (seconds < 60) return `${seconds}s ago`
  
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60) return `${minutes}m ago`
  
  const hours = Math.floor(minutes / 60)
  if (hours < 24) return `${hours}h ago`
  
  const days = Math.floor(hours / 24)
  if (days < 7) return `${days}d ago`
  
  return new Date(dateStr).toLocaleDateString()
}

const toggleNotification = () => {
  isNotificationOpen.value = !isNotificationOpen.value
  if (isNotificationOpen.value) {
    fetchNotifications()
  }
}

const markAllAsRead = async () => {
  try {
    isLoadingNotifications.value = true
    
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 500))
    
    // Clear all notifications
    notifications.value = []
    pendingCount.value = 0
    
    // Close the dropdown
    isNotificationOpen.value = false
    
    console.log('✅ All notifications marked as read')
    
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  } finally {
    isLoadingNotifications.value = false
  }
}

const goToNotification = (item) => {
  // Mark as read (in real app, this would call an API)
  const index = notifications.value.findIndex(n => n.id === item.id)
  if (index !== -1) {
    notifications.value[index].read = true
  }
  
  isNotificationOpen.value = false
  
  // Navigate based on role and item type
  const role = userRole.value?.toLowerCase()
  if (role === 'admin' || role === 'lawyer') {
    router.push('/approvals')
  } else if (role === 'clerk') {
    // Clerk might go to a different page
    router.push('/clerk-tracker')
  }
}

// ==================== USER MENU FUNCTIONS ====================
const handleOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target) &&
      notificationDropdownRef.value && !notificationDropdownRef.value.contains(e.target)) {
    isUserMenuOpen.value = false
    isNotificationOpen.value = false
  }
}

const handleResize = () => {
  showHamburger.value = window.innerWidth < 768
}

const askLogout = () => {
  isUserMenuOpen.value = false
  showLogoutModal.value = true
}

const confirmLogout = async () => {
  isLoggingOut.value = true
  try {
    await authService.logout()
    clearSession()
    router.replace('/')
  } catch (err) {
    console.error('Logout error:', err)
    clearSession()
    router.replace('/')
  } finally {
    isLoggingOut.value = false
    showLogoutModal.value = false
  }
}

// ==================== LIFECYCLE ====================
onMounted(() => {
  document.addEventListener('mousedown', handleOutside)
  window.addEventListener('resize', handleResize)
  handleResize()
  
  // Initial fetch
  fetchNotifications()
  
  // Set up polling every 30 seconds
  refreshInterval.value = setInterval(fetchNotifications, 30000)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleOutside)
  window.removeEventListener('resize', handleResize)
  if (refreshInterval.value) {
    clearInterval(refreshInterval.value)
  }
})

// Watch for route changes
watch(() => route.path, () => {
  if (route.path === '/approvals' || route.path === '/casemaster') {
    fetchNotifications()
  }
})

// Watch for role changes
watch(() => userRole.value, () => {
  fetchNotifications()
})
</script>