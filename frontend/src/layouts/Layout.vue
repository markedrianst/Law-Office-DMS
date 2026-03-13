<template>
  <div class="flex h-screen overflow-hidden bg-[#f5f7fa]">

    <!-- ══ SIDEBAR ══════════════════════════════════════════════════════════
         Desktop: static in the flex row (takes up space)
         Mobile:  fixed overlay, slides in/out
    ════════════════════════════════════════════════════════════════════════ -->

    <!-- Desktop sidebar (md and above) -->
    <div class="hidden md:flex md:flex-shrink-0">
      <Sidebar @navigate="closeSidebarOnMobile" />
    </div>

    <!-- Mobile sidebar (below md) -->
    <Transition name="slide">
      <div
        v-if="sidebarOpen"
        class="fixed inset-y-0 left-0 z-50 flex md:hidden"
      >
        <Sidebar @navigate="closeSidebarOnMobile" />
      </div>
    </Transition>

    <!-- Mobile overlay backdrop -->
    <Transition name="fade">
      <div
        v-if="sidebarOpen && isMobile"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm md:hidden"
        @click="sidebarOpen = false"
      />
    </Transition>

    <!-- ══ MAIN COLUMN ═══════════════════════════════════════════════════════
         Fills remaining width, header is fixed-height, only main scrolls
    ════════════════════════════════════════════════════════════════════════ -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

      <!-- Header — never scrolls -->
      <Header
        :sidebar-open="sidebarOpen"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page content — ONLY this scrolls -->
      <main class="flex-1 min-h-0 overflow-y-auto bg-[#f5f7fa] p-6 sm:p-4 max-[480px]:p-3">
        <router-view />
      </main>

      <!-- Footer — never scrolls -->
      <Footer />

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/components/Sidebar.vue'
import Header  from '@/components/Header.vue'
import Footer  from '@/components/Footer.vue'

const sidebarOpen  = ref(false)
const isMobile     = ref(false)

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
  if (!isMobile.value) sidebarOpen.value = false // auto-close on desktop resize
}

const toggleSidebar        = () => { sidebarOpen.value = !sidebarOpen.value }
const closeSidebarOnMobile = () => { if (isMobile.value) sidebarOpen.value = false }

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})
onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style scoped>
/* Sidebar slides in from the left on mobile */
.slide-enter-active,
.slide-leave-active { transition: transform 0.3s ease; }
.slide-enter-from,
.slide-leave-to     { transform: translateX(-100%); }

/* Overlay fades */
.fade-enter-active,
.fade-leave-active  { transition: opacity 0.25s ease; }
.fade-enter-from,
.fade-leave-to      { opacity: 0; }
</style>