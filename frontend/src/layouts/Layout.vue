<template>
  <div class="flex h-screen overflow-hidden bg-slate-50">
    <!-- Desktop Sidebar -->
    <div class="hidden md:block">
      <Sidebar
        :collapsed="sidebarCollapsed"
        @navigate="closeMobileSidebar"
        @toggle-collapse="toggleSidebarCollapse"
      />
    </div>

    <!-- Mobile Sidebar Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="mobileSidebarOpen"
        class="fixed inset-0 bg-black/50 z-40 md:hidden"
        @click="mobileSidebarOpen = false"
      />
    </Transition>

    <!-- Mobile Sidebar -->
    <Transition
      enter-active-class="transition-transform duration-250 ease-out"
      leave-active-class="transition-transform duration-200 ease-in"
      enter-from-class="-translate-x-full"
      leave-to-class="-translate-x-full"
    >
      <div
        v-if="mobileSidebarOpen"
        class="fixed inset-y-0 left-0 z-50 md:hidden"
      >
        <Sidebar
          :collapsed="false"
          @navigate="closeMobileSidebar"
        />
      </div>
    </Transition>

    <!-- Main Content Area -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">
      <!-- Header -->
      <Header
        :sidebar-open="mobileSidebarOpen"
        @toggle-sidebar="toggleMobileSidebar"
      />

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto bg-slate-50 p-4 md:p-6">
        <div class="w-full max-w-[1920px] mx-auto">
          <router-view />
        </div>
      </main>

      <!-- Footer -->
      <Footer />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/components/Sidebar.vue'
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'

// State
const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)
const isMobile = ref(false)

// Methods
const toggleSidebarCollapse = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleMobileSidebar = () => {
  mobileSidebarOpen.value = !mobileSidebarOpen.value
}

const closeMobileSidebar = () => {
  if (isMobile.value) {
    mobileSidebarOpen.value = false
  }
}

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
  if (!isMobile.value) {
    mobileSidebarOpen.value = false
  }
}

// Lifecycle
onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>