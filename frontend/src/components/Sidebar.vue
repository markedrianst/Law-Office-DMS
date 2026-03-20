<template>
  <aside 
    class="shrink-0 min-h-screen flex flex-col relative overflow-hidden transition-all duration-300 ease-in-out bg-[#1a4972] shadow-xl"
    :class="collapsed ? 'w-[68px]' : 'w-60'"
  >
    
    <!-- Logo Section -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-white/10 min-h-[68px]">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
                  bg-white/10 backdrop-blur-md border border-white/20
                  shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 bg-white/20"></div>
        <img 
          src="@/assets/images/lawofficelogo.png" 
          alt="Logo"
          class="w-6 h-6 object-contain relative z-10"
        />
      </div>

      <Transition
        enter-active-class="transition-all duration-200 delay-75"
        leave-active-class="transition-all duration-150"
        enter-from-class="opacity-0 -translate-x-2"
        leave-to-class="opacity-0 -translate-x-2"
      >
        <div v-if="!collapsed" class="flex flex-col gap-0.5 whitespace-nowrap overflow-hidden">
          <span class="text-[11px] font-bold text-white tracking-wider leading-tight">NICOLAS PINEDA</span>
          <span class="text-[9px] font-medium text-white/50 tracking-widest leading-tight">LAW OFFICE</span>
        </div>
      </Transition>
    </div>

    <!-- Loading Indicator (collapsed) -->
    <div v-if="collapsed && isRefreshing" class="absolute top-20 left-1/2 -translate-x-1/2">
      <div class="w-1.5 h-1.5 bg-blue-400 rounded-full animate-ping"></div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-3 sm:p-4 flex flex-col gap-1 overflow-y-auto sidebar-scroll">
      <Transition
        enter-active-class="transition-all duration-200 delay-75"
        leave-active-class="transition-all duration-150"
        enter-from-class="opacity-0 -translate-x-2"
        leave-to-class="opacity-0 -translate-x-2"
      >
        <p v-if="!collapsed" class="text-[9px] font-bold tracking-[0.12em] text-white/40 px-2.5 mb-2 whitespace-nowrap overflow-hidden">
          MAIN MENU
        </p>
      </Transition>

      <template v-for="item in navItems" :key="item.label || item.path">
        <!-- Regular Link -->
        <router-link
          v-if="!item.isDropdown"
          :to="item.path"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white/70 text-sm font-medium transition-all duration-200 whitespace-nowrap overflow-hidden relative cursor-pointer hover:bg-white/10 hover:text-white group"
          :class="{ 
            'bg-white/15 text-white shadow-sm': isActive(item.path)
          }"
          @click="handleNavigation"
        >
          <!-- Active Indicator -->
          <span
            v-if="isActive(item.path)"
            class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 rounded-r bg-white shadow-sm"
          ></span>

          <span class="flex items-center justify-center shrink-0 w-5 transition-transform duration-200 group-hover:scale-110" v-html="getIcon(item.icon)"></span>
          
          <Transition
            enter-active-class="transition-all duration-200 delay-75"
            leave-active-class="transition-all duration-150"
            enter-from-class="opacity-0 -translate-x-2"
            leave-to-class="opacity-0 -translate-x-2"
          >
            <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
          </Transition>
          
          <!-- Badge -->
          <Transition
            enter-active-class="transition-all duration-200 delay-75"
            leave-active-class="transition-all duration-150"
            enter-from-class="opacity-0 scale-75"
            leave-to-class="opacity-0 scale-75"
          >
            <span 
              v-if="!collapsed && item.badge && getBadgeCount(item.badge) > 0" 
              class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0 bg-amber-500 text-white shadow-sm"
            >
              {{ getBadgeCount(item.badge) }}
            </span>
          </Transition>

          <!-- Collapsed Badge Dot -->
          <span 
            v-if="collapsed && item.badge && getBadgeCount(item.badge) > 0" 
            class="absolute -top-1 -right-1 w-2 h-2 rounded-full bg-amber-500 border-2 border-[#1a4972] shrink-0 shadow-sm"
          ></span>
        </router-link>

        <!-- Dropdown -->
        <div v-else class="w-full">
          <div
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white/70 text-sm font-medium transition-all duration-200 whitespace-nowrap overflow-hidden relative cursor-pointer hover:bg-white/10 hover:text-white group"
            :class="{ 
              'bg-white/15 text-white shadow-sm': isDropdownActive(item)
            }"
            @click="toggleDropdown(item)"
          >
            <!-- Active Indicator -->
            <span
              v-if="isDropdownActive(item)"
              class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 rounded-r bg-white shadow-sm"
            ></span>

            <span class="flex items-center justify-center shrink-0 w-5 transition-transform duration-200 group-hover:scale-110" v-html="getIcon(item.icon)"></span>
            
            <Transition
              enter-active-class="transition-all duration-200 delay-75"
              leave-active-class="transition-all duration-150"
              enter-from-class="opacity-0 -translate-x-2"
              leave-to-class="opacity-0 -translate-x-2"
            >
              <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
            </Transition>

            <Transition
              enter-active-class="transition-all duration-200 delay-75"
              leave-active-class="transition-all duration-150"
              enter-from-class="opacity-0 -translate-x-2"
              leave-to-class="opacity-0 -translate-x-2"
            >
              <span 
                v-if="!collapsed" 
                class="ml-auto flex items-center shrink-0 transition-transform duration-300 ease-out"
                :class="{ 'rotate-180': expandedItems.has(item.label) }"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </span>
            </Transition>
          </div>

          <!-- Dropdown Children -->
          <Transition
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-250 ease-in"
            enter-from-class="opacity-0 max-h-0"
            leave-to-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-[500px]"
            leave-from-class="opacity-100 max-h-[500px]"
          >
            <div v-if="!collapsed && expandedItems.has(item.label)" class="ml-7 mt-1 mb-1 pl-3 border-l-2 border-white/15 space-y-0.5 overflow-hidden">
              <router-link
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-white/70 text-xs font-medium transition-all duration-200 whitespace-nowrap overflow-hidden relative cursor-pointer hover:bg-white/10 hover:text-white"
                :class="{ 
                  'bg-white/15 text-white shadow-sm': route.path === child.path
                }"
                @click="handleNavigation"
              >
                <span class="flex items-center justify-center shrink-0 w-4 opacity-80" v-html="getIcon(child.icon)"></span>
                <span class="flex-1">{{ child.label }}</span>
              </router-link>
            </div>
          </Transition>
        </div>
      </template>
    </nav>

    <!-- Bottom User Card -->
    <div class="flex items-center gap-2.5 px-3 py-3.5 border-t border-white/10 bg-white/5">
      <Transition
        enter-active-class="transition-all duration-200 delay-75"
        leave-active-class="transition-all duration-150"
        enter-from-class="opacity-0 -translate-x-2"
        leave-to-class="opacity-0 -translate-x-2"
      >
        <div v-if="!collapsed" class="flex items-center gap-2.5 flex-1 min-w-0">
          <div class="w-9 h-9 rounded-full bg-white/15 border-2 border-white/30 text-white text-xs font-bold flex items-center justify-center shrink-0 shadow-sm">
            {{ userInitials }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold text-white whitespace-nowrap overflow-hidden text-ellipsis leading-tight">{{ userName }}</p>
            <p class="text-[10px] text-white/50 capitalize mt-0.5 leading-tight">{{ userRoleLabel }}</p>
          </div>
        </div>
      </Transition>
      
      <!-- Collapsed User Avatar -->
      <div v-if="collapsed" class="w-9 h-9 rounded-full bg-white/15 border-2 border-white/30 text-white text-xs font-bold flex items-center justify-center shrink-0 mx-auto shadow-sm">
        {{ userInitials }}
      </div>

      <!-- Collapse Toggle Button (Desktop Only) -->
      <button 
        v-if="!isMobile" 
        @click="$emit('toggle-collapse')"
        class="w-8 h-8 rounded-lg shrink-0 bg-white/10 border border-white/20 text-white/70 cursor-pointer flex items-center justify-center transition-all duration-200 hover:bg-white/15 hover:text-white active:scale-95"
        aria-label="Toggle sidebar"
      >
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          width="14" 
          height="14" 
          viewBox="0 0 24 24"
          fill="none" 
          stroke="currentColor" 
          stroke-width="2.5"
          stroke-linecap="round"
          stroke-linejoin="round"
          class="transition-transform duration-300"
          :class="collapsed ? 'rotate-180' : 'rotate-0'"
        >
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
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

// State
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

// Computed
const navItems = computed(() => {
  try {
    return getSidebarItems(userRole.value) || []
  } catch (error) {
    console.error('Error getting sidebar items:', error)
    return []
  }
})

// Update Functions
const updateUserData = () => {
  userName.value = getUserName() || 'User'
  userRole.value = getUserRole() || 'user'
  userRoleLabel.value = getRoleLabel(userRole.value) || 'User'
  userInitials.value = getUserInitials() || 'U'
}

const updateNotifications = () => {
  unreadCount.value = getUnreadCount() || 0
  badgeCounts.value.notifications = unreadCount.value
}

// Navigation Helpers
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

// Fetch Badge Counts
const fetchBadges = async () => {
  try {
    badgeCounts.value.notifications = getUnreadCount()
    // Add API calls here for pending counts if needed
  } catch (error) {
    console.error('Failed to fetch badges:', error)
  }
}

const handleResize = () => { 
  isMobile.value = window.innerWidth < 768 
}

// Lifecycle
let cleanupUser = null
let cleanupNotifications = null

onMounted(() => {
  updateUserData()
  updateNotifications()
  fetchBadges()
  handleResize()
  
  cleanupUser = listenForUpdates('user-updated', updateUserData)
  cleanupNotifications = listenForUpdates('notifications-updated', updateNotifications)

  const handleStorageChange = (e) => {
    if (e.key === 'user') {
      updateUserData()
    } else if (e.key === 'notifications') {
      updateNotifications()
    }
  }
  
  window.addEventListener('resize', handleResize)
  window.addEventListener('storage', handleStorageChange)
 
  const interval = setInterval(fetchBadges, 30000)
  
  onUnmounted(() => {
    if (cleanupUser) cleanupUser()
    if (cleanupNotifications) cleanupNotifications()
    window.removeEventListener('resize', handleResize)
    window.removeEventListener('storage', handleStorageChange)
    clearInterval(interval)
  })
})
</script>

<style scoped>
/* Custom scrollbar */
.sidebar-scroll::-webkit-scrollbar {
  width: 5px;
}

.sidebar-scroll::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 3px;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 3px;
  transition: background 0.2s;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.25);
}
</style>