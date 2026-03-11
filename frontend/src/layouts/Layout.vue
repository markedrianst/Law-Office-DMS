<template>
  <div class="app-wrapper">
    <!-- Sidebar -->
    <Sidebar 
      :class="{ 'sidebar-open': sidebarOpen }" 
      @navigate="closeSidebarOnMobile"
    />

    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Header -->
      <Header 
        :sidebar-open="sidebarOpen" 
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page Content - This changes based on route -->
      <main class="content">
        <router-view />
      </main>

      <!-- Footer -->
      <Footer />
    </div>

    <!-- Mobile Overlay -->
    <div 
      v-if="sidebarOpen && isMobile" 
      class="mobile-overlay"
      @click="sidebarOpen = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/components/Sidebar.vue'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'

const sidebarOpen = ref(false)
const isMobile = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
  if (isMobile.value) {
    sidebarOpen.value = false
  }
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebarOnMobile = () => {
  if (isMobile.value) {
    sidebarOpen.value = false
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
.app-wrapper {
  display: flex;
  min-height: 100vh;
  background: #f5f7fa;
  position: relative;
}

.sidebar {
  position: fixed;
  left: 0;
  top: 0;
  bottom: 0;
  z-index: 50;
  transition: transform 0.3s ease;
}

@media (min-width: 768px) {
  .sidebar {
    position: relative;
  }
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  width: 100%;
}

.content {
  flex: 1;
  padding: 24px;
  overflow-y: auto;
  background: #f5f7fa;
  min-height: 0;
}

@media (max-width: 768px) {
  .content {
    padding: 16px;
  }
}

@media (max-width: 480px) {
  .content {
    padding: 12px;
  }
}

@media (max-width: 767px) {
  .sidebar {
    transform: translateX(-100%);
    position: fixed;
    z-index: 100;
  }

  .sidebar.sidebar-open {
    transform: translateX(0);
  }
}

.mobile-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 90;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>