<template>
  <aside class="sidebar" :class="{ collapsed }">

    <!-- Logo -->
    <div class="sidebar-logo">
      <div class="logo-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="3" x2="12" y2="21"/>
          <line x1="5" y1="7" x2="19" y2="7"/>
          <path d="M7 7l-3 5h6l-3-5z"/>
          <path d="M17 7l-3 5h6l-3-5z"/>
          <line x1="8" y1="21" x2="16" y2="21"/>
        </svg>
      </div>
      <transition name="fade-text">
        <div v-if="!collapsed" class="logo-text">
          <span class="logo-main">NICOLAS PINEDA</span>
          <span class="logo-sub">LAW OFFICE</span>
        </div>
      </transition>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <transition name="fade-text">
        <p v-if="!collapsed" class="nav-label">MAIN</p>
      </transition>

      <template v-for="item in visibleNav" :key="item.label || item.path">
        <router-link
          v-if="!item.isDropdown"
          :to="item.path"
          class="nav-item"
          :class="{ active: route.path === item.path }"
          @click="$emit('navigate')"
        >
          <span class="nav-icon" v-html="item.icon"></span>
          <transition name="fade-text">
            <span v-if="!collapsed" class="nav-text">{{ item.label }}</span>
          </transition>
        </router-link>

        <div v-else class="nav-dropdown">
          <div
            class="nav-item dropdown-toggle"
            :class="{ active: isDropdownActive(item) }"
            @click="toggleDropdown(item)"
          >
            <span class="nav-icon" v-html="item.icon"></span>
            <transition name="fade-text">
              <span v-if="!collapsed" class="nav-text">{{ item.label }}</span>
            </transition>
            <transition name="fade-text">
              <span v-if="!collapsed" class="dropdown-arrow" :class="{ rotated: item.expanded.value }">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </span>
            </transition>
          </div>

          <transition name="dropdown">
            <div v-if="!collapsed && item.expanded.value" class="dropdown-children">
              <router-link
                v-for="child in item.children"
                :key="child.path"
                :to="child.path"
                class="nav-item nav-item--child"
                :class="{ active: route.path === child.path }"
                @click="$emit('navigate')"
              >
                <span class="nav-icon nav-icon--child" v-html="child.icon"></span>
                <span class="nav-text">{{ child.label }}</span>
              </router-link>
            </div>
          </transition>
        </div>
      </template>
    </nav>

    <!-- Bottom user card -->
    <div class="sidebar-footer">
      <transition name="fade-text">
        <div v-if="!collapsed" class="user-card">
          <div class="uc-avatar">{{ userInitials }}</div>
          <div class="uc-info">
            <p class="uc-name">{{ userName }}</p>
            <p class="uc-role">{{ userRole }}</p>
          </div>
        </div>
      </transition>
      <div v-if="collapsed" class="uc-avatar uc-avatar--solo">{{ userInitials }}</div>

      <button v-if="!isMobile" class="collapse-btn" @click="collapsed = !collapsed">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
          :style="{ transform: collapsed ? 'rotate(180deg)' : 'rotate(0deg)', transition: 'transform .3s' }">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>
    </div>

  </aside>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'

const route = useRoute()
const collapsed = ref(false)
const isMobile = ref(false)
const { userName, userRole, userInitials } = useAuth()

defineEmits(['navigate'])

// SVG icons
const icons = {
  dashboard: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
  users: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
  logs: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
  cases: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
  tasks: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
  appointments: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
  approvals: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>`,
}

// Nav items (badges removed for now)
const allNav = [
  { path: '/dashboard', label: 'Dashboard', icon: icons.dashboard, roles: [] },
  { path: '/usermanagement', label: 'Users', icon: icons.users, roles: ['admin'] },
  { path: '/audit-trail', label: 'Activity Logs', icon: icons.logs, roles: ['admin'] },
  { path: '/casemaster', label: 'Case Master', icon: icons.cases, roles: ['admin', 'lawyer', 'clerk'] },
  { path: '/approvals', label: 'Approvals', icon: icons.approvals, roles: ['admin', 'lawyer'] },
  {
    label: 'Master Data',
    icon: icons.tasks,
    isDropdown: true,
    roles: ['admin', 'lawyer', 'clerk'],
    expanded: ref(false),
    children: [
      { path: '/casecategories', label: 'Case Categories', icon: icons.tasks },
      { path: '/courts', label: 'Courts', icon: icons.tasks },
      { path: '/documents', label: 'Documents', icon: icons.tasks },
    ],
  },
  { path: '/clerkstracker', label: 'Clerks Tracker', icon: icons.appointments, roles: ['clerk'] },
  
]

// Filter nav based on user role
const visibleNav = computed(() => {
  const role = userRole.value?.toLowerCase() ?? ''
  return allNav.filter(item => {
    if (!item.roles || item.roles.length === 0) return true
    return item.roles.some(r => r.toLowerCase() === role)
  })
})

const isDropdownActive = (item) => item.children?.some(c => route.path === c.path) ?? false
const toggleDropdown = (item) => { item.expanded.value = !item.expanded.value }

const handleResize = () => { isMobile.value = window.innerWidth < 768 }

onMounted(() => {
  window.addEventListener('resize', handleResize)
  handleResize()
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>

.sidebar {
  width: 240px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: linear-gradient(180deg, #1a4972 0%, #0f2f4a 55%, #091e31 100%);
  transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  flex-shrink: 0;
  font-family: 'Segoe UI', sans-serif;
  position: relative;
  overflow: hidden;
}

.sidebar.collapsed { width: 68px; }

@media (max-width: 767px) {
  .sidebar, .sidebar.collapsed { width: 240px; }
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 16px 18px;
  border-bottom: 1px solid rgba(255,255,255,.08);
  min-height: 68px;
}

.logo-icon {
  width: 36px; height: 36px;
  border-radius: 10px;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.18);
  display: flex; align-items: center; justify-content: center;
  color: white; flex-shrink: 0;
}

.logo-text { display: flex; flex-direction: column; gap: 1px; white-space: nowrap; overflow: hidden; }
.logo-main { font-size: 11px; font-weight: 700; color: white; letter-spacing: .06em; }
.logo-sub  { font-size: 9px;  font-weight: 500; color: rgba(255,255,255,.4); letter-spacing: .1em; }

.sidebar-nav {
  flex: 1;
  padding: 16px 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  overflow-y: auto;
}

.sidebar-nav::-webkit-scrollbar       { width: 6px; }
.sidebar-nav::-webkit-scrollbar-track { background: rgba(255,255,255,.05); }
.sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 3px; }
.sidebar-nav::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.25); }

.nav-label {
  font-size: 9px; font-weight: 700; letter-spacing: .12em;
  color: rgba(255,255,255,.3);
  padding: 0 8px; margin-bottom: 6px; white-space: nowrap; overflow: hidden;
}

.nav-item {
  display: flex; align-items: center; gap: 12px;
  padding: 10px 10px;
  border-radius: 10px;
  text-decoration: none;
  color: rgba(255,255,255,.6);
  font-size: 13px; font-weight: 500;
  transition: background .15s, color .15s;
  white-space: nowrap; overflow: hidden;
  position: relative; cursor: pointer;
}

.nav-item:hover         { background: rgba(255,255,255,.09); color: rgba(255,255,255,.9); }
.nav-item.active        { background: rgba(255,255,255,.15); color: white; }
.nav-item.active::before {
  content: ''; position: absolute; left: 0; top: 20%; bottom: 20%;
  width: 3px; border-radius: 0 3px 3px 0; background: rgba(255,255,255,.7);
}

.nav-icon      { display: flex; align-items: center; justify-content: center; flex-shrink: 0; width: 18px; }
.nav-text      { flex: 1; }

.nav-badge {
  font-size: 10px; font-weight: 700;
  padding: 1px 7px; border-radius: 99px;
  background: rgba(255,255,255,.15); color: rgba(255,255,255,.8);
}
.nav-badge--alert {
  background: #f59e0b; color: #fff;
  animation: pulse-badge 2s infinite;
}

.nav-dot-badge {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: #f59e0b;
  border: 1.5px solid rgba(15, 47, 74, 0.9);
  flex-shrink: 0;
  animation: pulse-badge 2s infinite;
}

@keyframes pulse-badge {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.65; }
}

.sidebar-footer {
  padding: 12px 10px;
  border-top: 1px solid rgba(255,255,255,.08);
  display: flex; align-items: center; gap: 10px;
}

.user-card { display: flex; align-items: center; gap: 10px; flex: 1; min-width: 0; }

.uc-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: rgba(255,255,255,.15);
  border: 1.5px solid rgba(255,255,255,.25);
  color: white; font-size: 11px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.uc-avatar--solo { margin: 0 auto 8px; }
.uc-info         { min-width: 0; }
.uc-name         { font-size: 11px; font-weight: 600; color: white; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0; }
.uc-role         { font-size: 10px; color: rgba(255,255,255,.4); text-transform: capitalize; margin: 1px 0 0; }

.collapse-btn {
  width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
  background: rgba(255,255,255,.07);
  border: 1px solid rgba(255,255,255,.1);
  color: rgba(255,255,255,.5);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: background .15s;
}
.collapse-btn:hover { background: rgba(255,255,255,.13); color: white; }

.fade-text-enter-active  { transition: opacity .2s .05s, transform .2s .05s; }
.fade-text-leave-active  { transition: opacity .12s, transform .12s; }
.fade-text-enter-from,
.fade-text-leave-to      { opacity: 0; transform: translateX(-4px); }

.nav-dropdown    { width: 100%; }
.dropdown-toggle { cursor: pointer; position: relative; }
.dropdown-arrow  { margin-left: auto; transition: transform .3s ease; display: flex; align-items: center; opacity: .6; flex-shrink: 0; }
.dropdown-arrow.rotated { transform: rotate(180deg); }

.dropdown-children {
  margin-left: 28px; margin-top: 2px; margin-bottom: 2px;
  padding-left: 4px;
  border-left: 1px dashed rgba(255,255,255,.15);
}

.nav-item--child  { padding: 8px 10px; font-size: 12px; }
.nav-icon--child  { width: 14px; opacity: .7; }

.dropdown-enter-active,
.dropdown-leave-active { transition: all .3s ease; max-height: 100px; overflow: hidden; }
.dropdown-enter-from,
.dropdown-leave-to     { opacity: 0; max-height: 0; transform: translateY(-10px); }

@media (max-width: 640px) {
  .sidebar-logo { padding: 16px 12px; }
  .sidebar-nav  { padding: 12px 8px; }
  .nav-item     { padding: 9px 8px; font-size: 12px; gap: 10px; }
  .nav-icon     { width: 16px; }
  .sidebar-footer { padding: 10px 8px; gap: 8px; }
}
</style>