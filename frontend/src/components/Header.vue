<template>
  <header class="flex items-center justify-between px-4 md:px-6 h-16 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] border-b border-white/10 font-sans relative z-40 flex-shrink-0 gap-3">
    <h1 class="text-sm md:text-base font-semibold text-white/90 tracking-wide flex-1 text-left truncate">
      {{ pageTitle }}
    </h1>

    <div class="flex items-center gap-2 flex-shrink-0">

      <!-- ══ NOTIFICATION BELL ══════════════════════════════════════════════ -->
      <div class="relative" ref="notificationDropdownRef">
        <button
          class="relative w-9 h-9 rounded-lg border border-white/20 bg-white/10 text-white/80 hover:bg-white/20 flex items-center justify-center transition-all"
          :class="{ 'bg-white/20': isNotificationOpen }"
          @click="toggleNotification"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <span
            v-if="unreadCount > 0"
              class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center border-2 border-[#0f2f4a]"
            >
            {{ unreadCount > 9 ? '9+' : unreadCount }}
          </span>
        </button>

        <!-- Notifications Dropdown -->
        <Transition name="dropdown">
          <div
            v-if="isNotificationOpen"
            class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-xl shadow-2xl overflow-hidden z-[9999] max-sm:fixed max-sm:left-3 max-sm:right-3 max-sm:w-auto border border-slate-100"
          >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <div class="flex items-center gap-2">
                <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
                <span v-if="unreadCount > 0" class="px-2 py-0.5 text-[10px] font-bold bg-[#1a4972] text-white rounded-full">
                  {{ unreadCount }} new
                </span>
              </div>
              <button
                v-if="unreadCount > 0"
                @click="markAllAsRead"
                class="text-xs font-semibold text-[#1a4972] hover:bg-slate-200 px-2 py-1 rounded-lg transition-colors"
              >
                Mark all read
              </button>
            </div>

            <!-- List -->
            <div class="max-h-[420px] overflow-y-auto divide-y divide-slate-100">

              <!-- Empty state -->
              <div v-if="notifications.length === 0" class="p-12 flex flex-col items-center gap-3 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                  <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-500">You're all caught up!</p>
                  <p class="text-xs text-slate-400 mt-0.5">No notifications yet</p>
                </div>
              </div>

              <!-- Notification Items -->
              <div
                v-else
                v-for="item in notifications"
                :key="item.id"
                class="flex items-start gap-3 px-4 py-3.5 cursor-pointer transition-all duration-150 relative"
                :class="item.is_read
                  ? 'bg-white hover:bg-slate-50'
                  : 'bg-blue-50/70 hover:bg-blue-50 border-l-[3px] border-[#1a4972]'"
                @click="goToNotification(item)"
              >
                <!-- Unread glow strip -->
                <div
                  v-if="!item.is_read"
                  class="absolute inset-0 bg-gradient-to-r from-blue-100/40 to-transparent pointer-events-none"
                ></div>

                <!-- Icon badge -->
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 text-sm z-10"
                  :class="getIconClass(item)"
                >
                  {{ getIcon(item) }}
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0 z-10">
                  <div class="flex items-start justify-between gap-2">
                    <p class="text-xs font-bold leading-snug" :class="item.is_read ? 'text-slate-700' : 'text-slate-900'">
                      {{ item.title }}
                    </p>
                    <!-- Unread dot -->
                    <span
                      v-if="!item.is_read"
                      class="w-2 h-2 rounded-full bg-[#1a4972] flex-shrink-0 mt-1"
                    ></span>
                  </div>

                  <p class="text-xs mt-0.5 line-clamp-2" :class="item.is_read ? 'text-slate-500' : 'text-slate-600'">
                    {{ item.message }}
                  </p>

                  <p v-if="item.data?.notes" class="text-xs text-slate-400 mt-1 italic line-clamp-1">
                    📝 {{ item.data.notes }}
                  </p>

                  <p class="text-[10px] mt-1.5 font-medium" :class="item.is_read ? 'text-slate-400' : 'text-[#1a4972]/70'">
                    {{ timeAgo(item.created_at) }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Footer -->
            <div v-if="notifications.length > 0" class="px-4 py-3 border-t border-slate-100 bg-slate-50 text-center">
              <button @click="viewAll" class="text-xs font-semibold text-[#1a4972] hover:underline transition-all">
                View all notifications →
              </button>
            </div>
          </div>
        </Transition>
      </div>

      <!-- ══ USER MENU ══════════════════════════════════════════════════════ -->
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
          <svg
            class="w-3 h-3 text-white/50 transition-transform duration-200"
            :class="{ 'rotate-180': isUserMenuOpen }"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          >
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </button>

        <!-- User Dropdown -->
        <Transition name="dropdown">
          <div
            v-if="isUserMenuOpen"
            class="absolute right-0 top-full mt-2 w-56 bg-[#071626] backdrop-blur-xl border border-white/20 rounded-xl shadow-2xl overflow-hidden z-[9999] max-sm:fixed max-sm:left-3 max-sm:right-3 max-sm:w-auto"
          >
            <div class="p-4 border-b border-white/10 flex items-center gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-r from-[#1a4972] to-[#2d6db5] text-white text-sm font-bold flex items-center justify-center flex-shrink-0">
                {{ userInitials }}
              </div>
              <div class="min-w-0">
                <p class="text-sm font-bold text-white truncate">{{ userName }}</p>
                <p class="text-xs text-white/40 capitalize truncate">{{ userRole }}</p>
              </div>
            </div>

            <div class="p-1.5">
              <router-link
                to="/account-setting"
                @click="isUserMenuOpen = false"
                class="flex items-center gap-2.5 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-white/80 hover:bg-white/10 transition-colors"
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
        </Transition>
      </div>

      <!-- ══ HAMBURGER (mobile) ═════════════════════════════════════════════ -->
      <button
        v-if="showHamburger"
        class="flex flex-col gap-1 w-9 h-9 items-center justify-center bg-white/10 border border-white/20 rounded-lg hover:bg-white/20 transition-colors md:hidden"
        :class="{ 'bg-white/20': sidebarOpen }"
        @click="$emit('toggle-sidebar')"
      >
        <span class="w-5 h-0.5 bg-white/80 rounded-full transition-transform duration-300" :class="{ 'rotate-45 translate-y-1.5': sidebarOpen }"></span>
        <span class="w-5 h-0.5 bg-white/80 rounded-full transition-opacity duration-300"  :class="{ 'opacity-0': sidebarOpen }"></span>
        <span class="w-5 h-0.5 bg-white/80 rounded-full transition-transform duration-300" :class="{ '-rotate-45 -translate-y-1.5': sidebarOpen }"></span>
      </button>
    </div>
  </header>

  <!-- ══ LOGOUT CONFIRMATION MODAL ═════════════════════════════════════════ -->
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="showLogoutModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        @click.self="showLogoutModal = false"
      >
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
              class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 border border-slate-200 rounded-xl hover:bg-slate-200 transition-colors"
              @click="showLogoutModal = false"
            >
              Cancel
            </button>
            <button
              class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors flex items-center justify-center gap-2"
              @click="confirmLogout"
            >
              {{ isLoggingOut ? 'Signing out…' : 'Yes, sign out' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { useNotifications } from '@/composables/useNotifications'
import authService from '@/services/auth'

const router = useRouter()
const route  = useRoute()
const { userName, userRole, userInitials, clearSession } = useAuth()
const { notifications, unreadCount, markAsRead, markAllAsRead } = useNotifications()

// ── State ──────────────────────────────────────────────────────────────────
const isUserMenuOpen       = ref(false)
const dropdownRef          = ref(null)
const isNotificationOpen   = ref(false)
const notificationDropdownRef = ref(null)
const showLogoutModal      = ref(false)
const isLoggingOut         = ref(false)
const showHamburger        = ref(false)

// ── Props / Emits ──────────────────────────────────────────────────────────
const props = defineProps({
  sidebarOpen: { type: Boolean, default: false }
})
defineEmits(['toggle-sidebar'])

// ── Page title ─────────────────────────────────────────────────────────────
const pageTitle = computed(() => {
  const titles = {
    '/dashboard':       'Dashboard',
    '/account-setting': 'Account Settings',
    '/usermanagement':  'User Management',
    '/casemaster':      'Case Master',
    '/approvals':       'Approvals',
    '/audit-trail':     'Audit Trail',
    '/casecategories':  'Case Categories',
    '/courts':          'Courts & Offices',
    '/documents':       'Document Types',
  }
  return titles[route.path] || 'Dashboard'
})

// ── Notification helpers ───────────────────────────────────────────────────
const getIcon = (item) => {
  if (item.type?.includes('approved'))  return '✅'
  if (item.type?.includes('rejected'))  return '❌'
  if (item.type?.includes('pending'))   return '⏳'
  if (item.type?.includes('folder'))    return '📂'
  if (item.type?.includes('checklist')) return '📋'
  if (item.type?.includes('task'))      return '📝'
  if (item.type?.includes('case'))      return '📁'
  return '🔔'
}

const getIconClass = (item) => {
  if (item.type?.includes('approved'))  return 'bg-emerald-100'
  if (item.type?.includes('rejected'))  return 'bg-red-100'
  if (item.type?.includes('pending'))   return 'bg-amber-100'
  if (item.type?.includes('task'))      return 'bg-blue-100'
  return 'bg-slate-100'
}

const timeAgo = (dateStr) => {
  if (!dateStr) return ''
  const seconds = Math.floor((Date.now() - new Date(dateStr)) / 1000)
  if (seconds < 60)   return 'just now'
  const minutes = Math.floor(seconds / 60)
  if (minutes < 60)   return `${minutes}m ago`
  const hours = Math.floor(minutes / 60)
  if (hours < 24)     return `${hours}h ago`
  return `${Math.floor(hours / 24)}d ago`
}

const toggleNotification = () => {
  isNotificationOpen.value = !isNotificationOpen.value
  if (isNotificationOpen.value) isUserMenuOpen.value = false
}

const goToNotification = async (item) => {
  if (!item.is_read) await markAsRead(item.id)
  isNotificationOpen.value = false
  if (item.action_url)               router.push(item.action_url)
  else if (item.data?.case_id)       router.push(`/casemaster`)
  else if (item.type?.includes('approval')&& item.data?.approval_id==userRole.value=='lawyer') router.push('/approvals')
}

const viewAll = () => {
  isNotificationOpen.value = false
  router.push('/notifications')
}

// ── User menu ──────────────────────────────────────────────────────────────
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
  } catch {
    clearSession()
    router.replace('/')
  } finally {
    isLoggingOut.value = false
    showLogoutModal.value = false
  }
}

// ── Outside click handler ──────────────────────────────────────────────────
const handleOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isUserMenuOpen.value = false
  }
  if (notificationDropdownRef.value && !notificationDropdownRef.value.contains(e.target)) {
    isNotificationOpen.value = false
  }
}

const handleResize = () => { showHamburger.value = window.innerWidth < 768 }

onMounted(() => {
  document.addEventListener('mousedown', handleOutside)
  window.addEventListener('resize', handleResize)
  handleResize()
})
onUnmounted(() => {
  document.removeEventListener('mousedown', handleOutside)
  window.removeEventListener('resize', handleResize)
})
</script>

<style scoped>
.dropdown-enter-active { transition: all 0.15s ease; }
.dropdown-enter-from   { opacity: 0; transform: translateY(-6px) scale(0.98); }
.dropdown-leave-active { transition: all 0.1s ease; }
.dropdown-leave-to     { opacity: 0; transform: translateY(-4px) scale(0.98); }

.modal-enter-active { transition: all 0.2s ease; }
.modal-enter-from   { opacity: 0; }
.modal-leave-active { transition: all 0.15s ease; }
.modal-leave-to     { opacity: 0; }
</style>