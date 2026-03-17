<template>
  <aside 
    class="shrink-0 min-h-screen flex flex-col relative overflow-hidden transition-all duration-300 ease-in-out"
    :class="collapsed ? 'w-[68px]' : 'w-60'"
    style="background: linear-gradient(180deg, #1a4972 0%, #0f2f4a 55%, #091e31 100%);">
    
    <!-- Logo -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-white/8 min-h-[68px]">
      <div class="w-9 h-9 rounded-[10px] bg-white/12 border border-white/18 flex items-center justify-center text-white shrink-0" v-html="getIcon('dashboard')"></div>
      <Transition
        enter-active-class="transition-all duration-200 delay-50"
        leave-active-class="transition-all duration-150"
        enter-from-class="opacity-0 -translate-x-1"
        leave-to-class="opacity-0 -translate-x-1">
        <div v-if="!collapsed" class="flex flex-col gap-0.5 whitespace-nowrap overflow-hidden">
          <span class="text-[11px] font-bold text-white tracking-wider">NICOLAS PINEDA</span>
          <span class="text-[9px] font-medium text-white/40 tracking-widest">LAW OFFICE</span>
        </div>
      </Transition>
    </div>

    <!-- Sync indicator -->
    <div v-if="collapsed && isRefreshing" class="absolute top-20 left-1/2 -translate-x-1/2">
      <div class="w-1 h-1 bg-blue-400 rounded-full animate-ping"></div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 flex flex-col gap-0.5 overflow-y-auto sidebar-scroll">
      <Transition
        enter-active-class="transition-all duration-200 delay-50"
        leave-active-class="transition-all duration-150"
        enter-from-class="opacity-0 -translate-x-1"
        leave-to-class="opacity-0 -translate-x-1">
        <p v-if="!collapsed" class="text-[9px] font-bold tracking-[0.12em] text-white/30 px-2 mb-1.5 whitespace-nowrap overflow-hidden">
          MAIN
        </p>
      </Transition>

      <template v-for="item in navItems" :key="item.label || item.path">
        <!-- Regular link -->
        <router-link
          v-if="!item.isDropdown"
          :to="item.path"
          class="flex items-center gap-3 px-2.5 py-2.5 rounded-[10px] text-white/60 text-[13px] font-medium transition-all duration-150 whitespace-nowrap overflow-hidden relative cursor-pointer hover:bg-white/9 hover:text-white/90"
          :class="{ 
            'bg-white/15 text-white before:absolute before:left-0 before:top-[20%] before:bottom-[20%] before:w-0.5 before:rounded-r before:bg-white/70': isActive(item.path)
          }"
          @click="handleNavigation">
          <span class="flex items-center justify-center shrink-0 w-[18px]" v-html="getIcon(item.icon)"></span>
          <Transition
            enter-active-class="transition-all duration-200 delay-50"
            leave-active-class="transition-all duration-150"
            enter-from-class="opacity-0 -translate-x-1"
            leave-to-class="opacity-0 -translate-x-1">
            <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
          </Transition>
          
          <!-- Badge -->
          <Transition
            enter-active-class="transition-all duration-200 delay-50"
            leave-active-class="transition-all duration-150"
            enter-from-class="opacity-0 -translate-x-1"
            leave-to-class="opacity-0 -translate-x-1">
            <span 
              v-if="!collapsed && item.badge && getBadgeCount(item.badge) > 0" 
              class="text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
              :class="getBadgeCount(item.badge) > 0 ? 'bg-amber-500 text-white animate-pulse-badge' : 'bg-white/15 text-white/80'">
              {{ getBadgeCount(item.badge) }}
            </span>
          </Transition>
          <span 
            v-if="collapsed && item.badge && getBadgeCount(item.badge) > 0" 
            class="w-2 h-2 rounded-full bg-amber-500 border-[1.5px] shrink-0 animate-pulse"
            :style="{ borderColor: 'rgba(15, 47, 74, 0.9)' }">
          </span>
        </router-link>

        <!-- Dropdown -->
        <div v-else class="w-full">
          <div
            class="flex items-center gap-3 px-2.5 py-2.5 rounded-[10px] text-white/60 text-[13px] font-medium transition-all duration-150 whitespace-nowrap overflow-hidden relative cursor-pointer hover:bg-white/9 hover:text-white/90"
            :class="{ 
              'bg-white/15 text-white before:absolute before:left-0 before:top-[20%] before:bottom-[20%] before:w-0.5 before:rounded-r before:bg-white/70': isDropdownActive(item)
            }"
            @click="toggleDropdown(item)">
            <span class="flex items-center justify-center shrink-0 w-[18px]" v-html="getIcon(item.icon)"></span>
            <Transition
              enter-active-class="transition-all duration-200 delay-50"
              leave-active-class="transition-all duration-150"
              enter-from-class="opacity-0 -translate-x-1"
              leave-to-class="opacity-0 -translate-x-1">
              <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
            </Transition>
            <Transition
              enter-active-class="transition-all duration-200 delay-50"
              leave-active-class="transition-all duration-150"
              enter-from-class="opacity-0 -translate-x-1"
              leave-to-class="opacity-0 -translate-x-1">
              <span 
                v-if="!collapsed" 
                class="ml-auto flex items-center opacity-60 shrink-0 transition-transform duration-300 ease-in-out"
                :class="{ 'rotate-180': expandedItems.has(item.label) }">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </span>
            </Transition>
          </div>

          <Transition
            enter-active-class="transition-all duration-300 ease-in-out"
            leave-active-class="transition-all duration-300 ease-in-out"
            enter-from-class="opacity-0 max-h-0 -translate-y-2"
            leave-to-class="opacity-0 max-h-0 -translate-y-2"
            enter-to-class="opacity-100 max-h-[500px]"
            leave-from-class="opacity-100 max-h-[500px]">
            <div v-if="!collapsed && expandedItems.has(item.label)" class="ml-7 mt-0.5 mb-0.5 pl-1 border-l border-dashed border-white/15 overflow-hidden">
              <router-link
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                class="flex items-center gap-3 px-2.5 py-2 rounded-[10px] text-white/60 text-xs font-medium transition-all duration-150 whitespace-nowrap overflow-hidden relative cursor-pointer hover:bg-white/9 hover:text-white/90"
                :class="{ 
                  'bg-white/15 text-white before:absolute before:left-0 before:top-[20%] before:bottom-[20%] before:w-0.5 before:rounded-r before:bg-white/70': route.path === child.path
                }"
                @click="handleNavigation">
                <span class="flex items-center justify-center shrink-0 w-[14px] opacity-70" v-html="getIcon(child.icon)"></span>
                <span class="flex-1">{{ child.label }}</span>
              </router-link>
            </div>
          </Transition>
        </div>
      </template>
    </nav>

    <!-- Bottom user card -->
    <div class="flex items-center gap-2.5 px-2.5 py-3 border-t border-white/8">
      <Transition
        enter-active-class="transition-all duration-200 delay-50"
        leave-active-class="transition-all duration-150"
        enter-from-class="opacity-0 -translate-x-1"
        leave-to-class="opacity-0 -translate-x-1">
        <div v-if="!collapsed" class="flex items-center gap-2.5 flex-1 min-w-0">
          <div class="w-[30px] h-[30px] rounded-full bg-white/15 border-[1.5px] border-white/25 text-white text-[11px] font-bold flex items-center justify-center shrink-0">
            {{ userInitials }}
          </div>
          <div class="min-w-0">
            <p class="text-[11px] font-semibold text-white whitespace-nowrap overflow-hidden text-ellipsis m-0">{{ userName }}</p>
            <p class="text-[10px] text-white/40 capitalize mt-0.5 m-0">{{ userRoleLabel }}</p>
          </div>
        </div>
      </Transition>
      
      <div v-if="collapsed" class="w-[30px] h-[30px] rounded-full bg-white/15 border-[1.5px] border-white/25 text-white text-[11px] font-bold flex items-center justify-center shrink-0 mx-auto mb-2">
        {{ userInitials }}
      </div>

      <button 
        v-if="!isMobile" 
        @click="$emit('toggle-collapse')"
        class="w-7 h-7 rounded-lg shrink-0 bg-white/7 border border-white/10 text-white/50 cursor-pointer flex items-center justify-center transition-all duration-150 hover:bg-white/13 hover:text-white">
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          width="14" 
          height="14" 
          viewBox="0 0 24 24"
          fill="none" 
          stroke="currentColor" 
          stroke-width="2.5"
          class="transition-transform duration-300"
          :class="collapsed ? 'rotate-180' : 'rotate-0'">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>
    </div>
  </aside>
</template>
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

// Import appUtils
import { 
  getUserName,
  getUserRole,
  getUserInitials,
  getRoleLabel,
  getSidebarItems,
  getIcon,
  getUnreadCount,
  listenForUpdates
} from '@/utils/appUtils'

const props = defineProps({
  collapsed: Boolean
})

const emit = defineEmits(['navigate', 'toggle-collapse'])

const route = useRoute()
const router = useRouter()

// ==================== STATE ====================
const userName = ref(getUserName() || 'User')
const userRole = ref(getUserRole() || 'user')
const userRoleLabel = ref(getRoleLabel(userRole.value) || 'User')
const userInitials = ref(getUserInitials() || 'U')
const unreadCount = ref(getUnreadCount() || 0)

// UI State
const isMobile = ref(false)
const isRefreshing = ref(false)
const expandedItems = ref(new Set())

// Badge counts
const badgeCounts = ref({
  pending: 0,
  notifications: unreadCount.value
})

// ==================== COMPUTED ====================
const navItems = computed(() => {
  try {
    return getSidebarItems(userRole.value) || []
  } catch (error) {
    console.error('Error getting sidebar items:', error)
    return []
  }
})

// ==================== UPDATE FUNCTIONS ====================
const updateUserData = () => {
  console.log('🔄 Sidebar updating user data')
  userName.value = getUserName() || 'User'
  userRole.value = getUserRole() || 'user'
  userRoleLabel.value = getRoleLabel(userRole.value) || 'User'
  userInitials.value = getUserInitials() || 'U'
}

const updateNotifications = () => {
  unreadCount.value = getUnreadCount() || 0
  badgeCounts.value.notifications = unreadCount.value
}

// ==================== NAVIGATION HELPERS ====================
const isActive = (path) => {
  if (path === '/dashboard') {
    return route.path === path
  }
  return route.path.startsWith(path)
}

const isDropdownActive = (item) => 
  item.children?.some(c => route.path === c.path) ?? false

const toggleDropdown = (item) => {
  if (expandedItems.value.has(item.label)) {
    expandedItems.value.delete(item.label)
  } else {
    expandedItems.value.add(item.label)
  }
}

const handleNavigation = () => {
  emit('navigate')
  if (isMobile.value) {
    emit('toggle-collapse')
  }
}

const getBadgeCount = (type) => {
  if (type === 'notifications') {
    return badgeCounts.value.notifications
  }
  return badgeCounts.value[type] || 0
}

// ==================== FETCH BADGE COUNTS ====================
const fetchBadges = async () => {
  try {
    badgeCounts.value.notifications = getUnreadCount()
    // You can add API calls here for pending counts
    badgeCounts.value.pending = Math.floor(Math.random() * 5)
  } catch (error) {
    console.error('Failed to fetch badges:', error)
  }
}

// ==================== UI HELPERS ====================
const handleResize = () => { 
  isMobile.value = window.innerWidth < 768 
}

// ==================== LIFECYCLE ====================
let cleanupUser = null
let cleanupNotifications = null

onMounted(() => {
  console.log('📌 Sidebar mounted')
  
  // Initial updates
  updateUserData()
  updateNotifications()
  fetchBadges()
  handleResize()
  
  // Listen for updates from appUtils
  cleanupUser = listenForUpdates('user-updated', updateUserData)
  cleanupNotifications = listenForUpdates('notifications-updated', updateNotifications)
  
  // Storage events for multi-tab
  const handleStorageChange = (e) => {
    if (e.key === 'user') {
      updateUserData()
    } else if (e.key === 'notifications') {
      updateNotifications()
    }
  }
  
  // Event listeners
  window.addEventListener('resize', handleResize)
  window.addEventListener('storage', handleStorageChange)
  
  // Auto-refresh badges every 30 seconds
  const interval = setInterval(fetchBadges, 30000)
  
  // Cleanup on unmount
  onUnmounted(() => {
    console.log('📌 Sidebar unmounting')
    if (cleanupUser) cleanupUser()
    if (cleanupNotifications) cleanupNotifications()
    window.removeEventListener('resize', handleResize)
    window.removeEventListener('storage', handleStorageChange)
    clearInterval(interval)
  })
})
</script>
<style scoped>
/* Custom scrollbar for sidebar navigation */
.sidebar-scroll::-webkit-scrollbar {
  width: 6px;
}

.sidebar-scroll::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

.sidebar-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 3px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.25);
}

/* Custom badge pulse animation */
@keyframes pulse-badge {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.65; }
}

.animate-pulse-badge {
  animation: pulse-badge 2s infinite;
}
</style>