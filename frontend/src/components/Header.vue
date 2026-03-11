<template>
  <header class="header-bar">
    <h1 class="page-title">{{ pageTitle }}</h1>
    <div class="header-right">
      <button class="icon-btn">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
          fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
          <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        </svg>
        <span class="bell-dot"></span>
      </button>
      <div class="user-menu" ref="dropdownRef">
        <button class="user-btn" :class="{ active: isOpen }" @click="isOpen = !isOpen">
          <div class="avatar">{{ userInitials }}</div>
          <div class="user-info">
            <span class="user-name">{{ userName }}</span>
            <span class="user-role">{{ userRole }}</span>
          </div>
          <svg class="chevron" :class="{ flipped: isOpen }"
            xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"/>
          </svg>
        </button>

        <transition name="menu">
          <div v-if="isOpen" class="dropdown">
            <div class="dropdown-header">
              <div class="dh-avatar">{{ userInitials }}</div>
              <div>
                <p class="dh-name">{{ userName }}</p>
                <p class="dh-role">{{ userRole }}</p>
              </div>
            </div>

            <div class="dropdown-body">
              <router-link to="/account" @click="isOpen = false" class="dd-item"
                :class="{ 'dd-item--active': route.path === '/account' }">
                <span class="dd-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </span>
                Account Settings
              </router-link>

              <div class="dd-divider"></div>

              <button class="dd-item dd-item--danger" @click="askLogout">
                <span class="dd-icon dd-icon--danger">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
      <button v-if="showHamburger" class="hamburger-btn" @click="$emit('toggle-sidebar')" :class="{ active: sidebarOpen }">
        <span class="hamburger-line hamburger-line-1"></span>
        <span class="hamburger-line hamburger-line-2"></span>
        <span class="hamburger-line hamburger-line-3"></span>
      </button>
    </div>
  </header>

  <!-- Logout Confirmation Modal -->
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="showLogoutModal" class="modal-overlay" @click.self="showLogoutModal = false">
        <div class="modal-card">
          <div class="modal-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </div>

          <div class="modal-body">
            <h2 class="modal-title">Sign out?</h2>
            <p class="modal-sub">You'll need to log back in to access the system.</p>
          </div>

          <div class="modal-actions">
            <button class="modal-btn modal-btn--cancel" :disabled="isLoggingOut" @click="showLogoutModal = false">
              Cancel
            </button>
            <button class="modal-btn modal-btn--confirm" :disabled="isLoggingOut" @click="confirmLogout">
              <span v-if="isLoggingOut" class="logout-spinner"></span>
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
import authService from '@/services/auth'

const router = useRouter()
const route = useRoute()
const { userName, userRole, userInitials, clearSession } = useAuth()

const isOpen = ref(false)
const dropdownRef = ref(null)
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
  }
  return titles[route.path] || 'Dashboard'
})

const handleOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false
  }
}

const handleResize = () => {
  showHamburger.value = window.innerWidth < 768
}

onMounted(() => {
  document.addEventListener('mousedown', handleOutside)
  window.addEventListener('resize', handleResize)
  handleResize()
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleOutside)
  window.removeEventListener('resize', handleResize)
})

const askLogout = () => {
  isOpen.value = false
  showLogoutModal.value = true
}

const confirmLogout = async () => {
  isLoggingOut.value = true
  try {
    await authService.logout()
    clearSession() // Use the composable's clearSession
    router.replace('/')
  } catch (err) {
    console.error('Logout error:', err)
    clearSession() // Still clear session even if API fails
    router.replace('/')
  } finally {
    isLoggingOut.value = false
    showLogoutModal.value = false
  }
}
</script>

<style scoped>

.header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  height: 64px;
  background: linear-gradient(135deg, #1a4972 0%, #0f2f4a 100%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  font-family: 'Segoe UI', sans-serif;
  position: relative;
  z-index: 40;
  flex-shrink: 0;
  gap: 12px;
}

@media (min-width: 768px) {
  .header-bar {
    padding: 0 24px;
  }
}

/* ===== PAGE TITLE ===== */
.page-title {
  font-size: 13px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  letter-spacing: 0.02em;
  margin: 0;
  flex: 1;
  text-align: left;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 480px) {
  .page-title {
    font-size: 12px;
  }
}

.header-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

/* ===== HAMBURGER (right side on mobile) ===== */
.hamburger-btn {
  display: none;
  flex-direction: column;
  gap: 5px;
  width: 36px;
  height: 36px;
  padding: 0;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.07);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.15s;
  flex-shrink: 0;
}

@media (max-width: 767px) {
  .hamburger-btn {
    display: flex;
  }
}

.hamburger-btn:hover {
  background: rgba(255, 255, 255, 0.13);
}

.hamburger-btn.active {
  background: rgba(255, 255, 255, 0.15);
}

.hamburger-line {
  width: 20px;
  height: 2px;
  background: rgba(255, 255, 255, 0.75);
  border-radius: 1px;
  transition: all 0.3s ease;
}

.hamburger-btn.active .hamburger-line-1 {
  transform: rotate(45deg) translateY(9px);
}

.hamburger-btn.active .hamburger-line-2 {
  opacity: 0;
}

.hamburger-btn.active .hamburger-line-3 {
  transform: rotate(-45deg) translateY(-9px);
}

/* ── Bell ── */
.icon-btn {
  position: relative;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.07);
  color: rgba(255, 255, 255, 0.75);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.15s;
  flex-shrink: 0;
}

.icon-btn:hover {
  background: rgba(255, 255, 255, 0.13);
}

.bell-dot {
  position: absolute;
  top: 7px;
  right: 7px;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #ef4444;
  border: 1.5px solid #0f2f4a;
}

/* ── User button ── */
.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 6px 12px 6px 6px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.16);
  background: rgba(255, 255, 255, 0.08);
  cursor: pointer;
  transition: background 0.15s;
  white-space: nowrap;
}

.user-btn:hover,
.user-btn.active {
  background: rgba(255, 255, 255, 0.15);
}

/* Hide user button text on mobile, show only avatar */
@media (max-width: 767px) {
  .user-btn {
    padding: 4px;
    border-radius: 50%;
    border: 1.5px solid rgba(255, 255, 255, 0.2);
    background: transparent;
  }
  .user-info,
  .chevron {
    display: none;
  }
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.28), rgba(255, 255, 255, 0.1));
  border: 1.5px solid rgba(255, 255, 255, 0.32);
  color: white;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-info {
  display: none;
  flex-direction: column;
  gap: 1px;
  text-align: left;
  max-width: 100px;
}

@media (min-width: 640px) {
  .user-info {
    display: flex;
  }
}

.user-name {
  font-size: 12px;
  font-weight: 600;
  color: white;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.5);
  text-transform: capitalize;
  white-space: nowrap;
}

.chevron {
  color: rgba(255, 255, 255, 0.45);
  transition: transform 0.2s;
  flex-shrink: 0;
}

.chevron.flipped {
  transform: rotate(180deg);
}

/* ── Dropdown ── */
.dropdown {
  position: absolute;
  right: 0;
  top: calc(100% + 8px);
  width: 220px;
  background: rgba(7, 22, 38, 0.97);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.07);
  z-index: 9999;
}

@media (max-width: 480px) {
  .dropdown {
    position: fixed;
    right: 12px;
    left: 12px;
    width: auto;
  }
}

.dropdown-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.dh-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: linear-gradient(135deg, #1a4972, #2d6db5);
  color: white;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dh-name {
  font-size: 12px;
  font-weight: 700;
  color: white;
  margin: 0;
}

.dh-role {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.4);
  margin: 2px 0 0;
  text-transform: capitalize;
}

.dropdown-body {
  padding: 6px;
}

.dd-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 9px 10px;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: rgba(255, 255, 255, 0.75);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  transition: background 0.15s, color 0.15s;
}

.dd-item:hover {
  background: rgba(255, 255, 255, 0.09);
  color: white;
}

.dd-item--active {
  background: rgba(255, 255, 255, 0.1) !important;
  color: white !important;
}

.dd-item--danger {
  color: rgba(255, 160, 160, 0.85);
}

.dd-item--danger:hover {
  background: rgba(239, 68, 68, 0.12) !important;
  color: #fca5a5 !important;
}

.dd-icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.07);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.dd-icon--danger {
  background: rgba(239, 68, 68, 0.12);
}

.dd-divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.07);
  margin: 4px 6px;
}

/* ── Dropdown transition ── */
.menu-enter-active,
.menu-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.menu-enter-from,
.menu-leave-to {
  opacity: 0;
  transform: translateY(-6px) scale(0.97);
}

/* ── Logout Modal ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(10, 20, 35, 0.65);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.modal-card {
  background: white;
  border-radius: 20px;
  padding: 32px 28px 24px;
  width: 100%;
  max-width: 360px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
  font-family: 'Segoe UI', sans-serif;
}

@media (max-width: 480px) {
  .modal-card {
    max-width: 90%;
    padding: 24px 16px 16px;
  }
}

.modal-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 16px;
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #dc2626;
}

.modal-body {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.modal-sub {
  font-size: 13px;
  color: #64748b;
  margin: 0;
  line-height: 1.5;
}

.modal-actions {
  display: flex;
  gap: 10px;
  width: 100%;
  margin-top: 4px;
  flex-wrap: wrap;
}

@media (max-width: 480px) {
  .modal-actions {
    flex-direction: column;
    gap: 8px;
  }
}

.modal-btn {
  flex: 1;
  min-width: 100px;
  padding: 10px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s, opacity 0.15s;
  border: none;
}

.modal-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.modal-btn--cancel {
  background: #f1f5f9;
  color: #475569;
  border: 1.5px solid #e2e8f0;
}

.modal-btn--cancel:hover:not(:disabled) {
  background: #e2e8f0;
}

.modal-btn--confirm {
  background: #dc2626;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
}

.modal-btn--confirm:hover:not(:disabled) {
  background: #b91c1c;
}

.logout-spinner {
  width: 13px;
  height: 13px;
  border-radius: 50%;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  animation: spin 0.65s linear infinite;
  flex-shrink: 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}
</style>