<template>
  <aside 
    class="flex flex-col h-full overflow-hidden transition-all duration-300 ease-in-out bg-[#1a4972] shadow-xl"
    :class="[isMobile ? 'w-full' : (collapsed ? 'w-[68px]' : 'w-60')]"
  >
    <!-- Logo Section -->
    <div class="flex items-center gap-3 px-4 py-5 border-b border-white/10 min-h-[68px]">
      <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-white/10 border border-white/20">
        <img 
          src="@/assets/images/favicon.png" 
          alt="Logo"
          class="w-15 h-13 object-contain pt-1"
        />
      </div>
      <div v-if="!collapsed || isMobile" class="flex flex-col gap-0.5">
        <span class="text-[11px] font-bold text-white">NICOLAS PINEDA</span>
        <span class="text-[9px] font-medium text-white/50">LAW OFFICE</span>
      </div>
    </div>

    <!-- MAIN MENU Label -->
    <div v-if="!collapsed && !isMobile" class="px-4 pt-4 pb-2">
      <p class="text-[9px] font-bold tracking-[0.12em] text-white/40">MAIN MENU</p>
    </div>

    <!-- Navigation — driven by getSidebarItems(userRole) -->
    <nav class="flex-1 px-3 py-3 overflow-y-auto sidebar-scroll">
      <div class="flex flex-col gap-1">

        <template v-for="item in navItems" :key="item.path || item.label">

          <!-- Regular nav link -->
          <router-link
            v-if="!item.isDropdown"
            :to="item.path"
            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white/70 text-sm font-medium hover:bg-white/10 hover:text-white transition-all"
            :class="{ 'bg-white/15 text-white': route.path === item.path }"
            @click="handleNavigation"
          >
            <span class="shrink-0 w-5" v-html="getSidebarIcon(item.icon)"></span>
            <span v-if="!collapsed || isMobile" class="flex-1">{{ item.label }}</span>
          </router-link>

          <!-- Dropdown group (Master Data) -->
          <div v-else class="w-full">
            <div
              class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white/70 text-sm font-medium hover:bg-white/10 hover:text-white transition-all cursor-pointer"
              :class="{ 'bg-white/15 text-white': isMasterDataActive }"
              @click="handleMasterDataClick"
            >
              <span class="shrink-0 w-5" v-html="getSidebarIcon(item.icon)"></span>
              <span v-if="!collapsed || isMobile" class="flex-1">{{ item.label }}</span>
              <span v-if="(!collapsed || isMobile) && !collapsed" class="ml-auto transition-transform" :class="{ 'rotate-180': masterDataOpen }">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </span>
            </div>

            <!-- Dropdown children -->
            <div
              v-if="(!collapsed || isMobile) && masterDataOpen"
              class="ml-7 mt-1 pl-3 border-l-2 border-white/15 space-y-1"
            >
              <router-link
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-white/70 text-xs font-medium hover:bg-white/10 hover:text-white transition-all"
                :class="{ 'bg-white/15 text-white': route.path === child.path }"
                @click="handleNavigation"
              >
                <span class="shrink-0 w-4" v-html="getSidebarIcon(child.icon, 12)"></span>
                <span>{{ child.label }}</span>
              </router-link>
            </div>
          </div>

        </template>

        <!-- Account Settings — mobile only, appended after config items -->
        <router-link
          v-if="isMobile"
          to="/account-setting"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-white/70 text-sm font-medium hover:bg-white/10 hover:text-white transition-all mt-2"
          :class="{ 'bg-white/15 text-white': route.path === '/account-setting' }"
          @click="handleNavigation"
        >
          <span class="shrink-0 w-5">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
          </span>
          <span class="flex-1">Account Settings</span>
        </router-link>

      </div>
    </nav>

    <!-- Bottom User Card -->
    <div class="flex items-center justify-between gap-2.5 px-3 py-3.5 border-t border-white/10 bg-white/5">
      <div v-if="!collapsed || isMobile" class="flex items-center gap-2.5 flex-1">
        <div class="w-9 h-9 rounded-full bg-white/15 border-2 border-white/30 text-white text-xs font-bold flex items-center justify-center">
          {{ userInitials }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-xs font-semibold text-white truncate">{{ userName }}</p>
          <p class="text-[10px] text-white/50 capitalize">{{ userRoleLabel }}</p>
        </div>
      </div>

      <div v-if="!isMobile && collapsed" class="w-9 h-9 rounded-full bg-white/15 border-2 border-white/30 text-white text-xs font-bold flex items-center justify-center mx-auto">
        {{ userInitials }}
      </div>

      <!-- Logout — mobile only -->
      <button
        v-if="isMobile"
        @click="handleLogout"
        class="w-8 h-8 rounded-lg bg-white/10 border border-white/20 text-white/70 hover:bg-red-500/20 hover:text-red-300 transition-all flex items-center justify-center"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/>
          <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </button>

      <!-- Collapse Button — desktop only -->
      <button
        v-if="!isMobile"
        @click="toggleCollapse"
        class="w-8 h-8 rounded-lg bg-white/10 border border-white/20 text-white/70 hover:bg-white/15 hover:text-white transition-all flex items-center justify-center"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :class="collapsed ? 'rotate-180' : ''">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Swal from 'sweetalert2'
import {
  getUserName,
  getUserRole,
  getUserInitials,
  getRoleLabel,
  clearData
} from '@/utils/appUtils'
import authService from '@/services/auth'

const props = defineProps({
  collapsed: { type: Boolean, default: false },
  isMobile:  { type: Boolean, default: false }
})

const emit = defineEmits(['navigate', 'toggle-collapse'])

const route  = useRoute()
const router = useRouter()

const userName     = ref(getUserName()  || 'User')
const userRole     = ref(getUserRole()  || 'user')
const userRoleLabel = ref(getRoleLabel(userRole.value) || 'User')
const userInitials = ref(getUserInitials() || 'U')
const masterDataOpen = ref(false)

// ── Computed: which paths count as "master data active" ──
const isMasterDataActive = computed(() =>
  ['/casecategories', '/courts', '/documents'].includes(route.path)
)

// ── Computed: nav items for the current role ──
const navItems = computed(() => getSidebarItems(userRole.value))

// ── Handlers ──
const handleMasterDataClick = () => {
  if (props.collapsed && !props.isMobile) {
    emit('toggle-collapse')
    setTimeout(() => { masterDataOpen.value = true }, 300)
  } else {
    masterDataOpen.value = !masterDataOpen.value
  }
}

const toggleCollapse  = () => emit('toggle-collapse')
const handleNavigation = () => emit('navigate')

const handleLogout = () => {
  Swal.fire({
    title: 'Sign out?',
    text: "You'll need to log back in to access the system.",
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, sign out',
    cancelButtonText: 'Cancel'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        Swal.fire({ title: 'Signing out...', allowOutsideClick: false, didOpen: () => Swal.showLoading() })
        await authService.logout()
        clearData()
        sessionStorage.removeItem('token')
        sessionStorage.removeItem('user')
        router.replace('/')
        Swal.close()
      } catch (error) {
        clearData()
        sessionStorage.removeItem('token')
        sessionStorage.removeItem('user')
        router.replace('/')
      }
    }
  })
}

const updateUserData = () => {
  userName.value      = getUserName()  || 'User'
  userRole.value      = getUserRole()  || 'user'
  userRoleLabel.value = getRoleLabel(userRole.value) || 'User'
  userInitials.value  = getUserInitials() || 'U'
}

onMounted(() => {
  updateUserData()
  window.addEventListener('storage', (e) => {
    if (e.key === 'user') updateUserData()
  })
})

// ==================== SIDEBAR NAV CONFIG ====================
// Defines routes per role. Kept here — sidebar logic belongs with the sidebar.

const getSidebarItems = (role) => {
  const masterData = {
    label: 'Master Data', icon: 'tasks', isDropdown: true,
    children: [
      { path: '/casecategories', label: 'Case Categories', icon: 'tasks' },
      { path: '/courts',         label: 'Courts & Offices', icon: 'tasks' },
      { path: '/documents',      label: 'Document Types',   icon: 'tasks' },
    ]
  }

  const items = {
    admin: [
      { path: '/dashboard',      label: 'Dashboard',    icon: 'dashboard' },
      { path: '/usermanagement', label: 'Users',         icon: 'users'     },
      { path: '/casemaster',     label: 'Case Master',   icon: 'cases'     },
      { path: '/approvals',      label: 'Approvals',     icon: 'approvals' },
      { path: '/audit-trail',    label: 'Activity Logs', icon: 'logs'      },
      { path: '/calendar',       label: 'Calendar',      icon: 'calendar'  },
      masterData
    ],
    lawyer: [
      { path: '/dashboard',  label: 'Dashboard', icon: 'dashboard' },
      { path: '/casemaster', label: 'My Cases',  icon: 'cases'     },
      { path: '/approvals',  label: 'Approvals', icon: 'approvals' },
      { path: '/calendar',   label: 'Calendar',  icon: 'calendar'  },
      masterData
    ],
    clerk: [
      { path: '/dashboard',  label: 'Dashboard',  icon: 'dashboard' },
      { path: '/casemaster', label: 'Case Master', icon: 'cases'     },
      { path: '/calendar',   label: 'Calendar',    icon: 'calendar'  },
      masterData
    ]
  }

  return items[role] || items.admin
}

// Returns an inline SVG string for v-html rendering.
// size defaults to 18 for top-level items, pass 12 for children.
const getSidebarIcon = (name, size = 18) => {
  const s = size
  const icons = {
    dashboard: `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
    users:      `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    logs:       `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
    cases:      `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
    tasks:      `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
    approvals:  `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>`,
    calendar:   `<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
  }
  return icons[name] || ''
}
</script>

<style scoped>
.sidebar-scroll::-webkit-scrollbar { width: 5px; }
.sidebar-scroll::-webkit-scrollbar-track  { background: rgba(255,255,255,0.05); border-radius: 3px; }
.sidebar-scroll::-webkit-scrollbar-thumb  { background: rgba(255,255,255,0.15); border-radius: 3px; }
</style>  