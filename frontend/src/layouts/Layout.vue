<!-- src/layouts/Layout.vue - Fully responsive -->
<template>
  <div class="flex h-screen overflow-hidden bg-slate-100">
    <!-- Desktop sidebar (md and above) -->
    <div class="hidden md:flex md:flex-shrink-0">
      <Sidebar @navigate="closeSidebarOnMobile" />
    </div>

    <!-- Mobile sidebar -->
    <Transition
      enter-active-class="transition-transform duration-250 ease-out"
      leave-active-class="transition-transform duration-200 ease-in"
      enter-from-class="-translate-x-full"
      leave-to-class="-translate-x-full"
    >
      <div
        v-if="sidebarOpen"
        class="fixed inset-y-0 left-0 z-50 flex md:hidden"
      >
        <Sidebar @navigate="closeSidebarOnMobile" />
      </div>
    </Transition>

    <!-- Mobile backdrop -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      leave-active-class="transition-opacity duration-150"
      enter-from-class="opacity-0"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen && isMobile"
        class="fixed inset-0 z-40 bg-black/60 md:hidden"
        @click="sidebarOpen = false"
      />
    </Transition>

    <!-- Main Column -->
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden bg-slate-100">
      <Header
        :sidebar-open="sidebarOpen"
        @toggle-sidebar="toggleSidebar"
      />

      <!-- Page content - responsive padding -->
      <main 
        class="flex-1 min-h-0 overflow-y-auto bg-slate-100
               px-2 py-2
               xs:px-3 xs:py-3
               sm:px-4 sm:py-4
               md:px-5 md:py-5
               lg:px-6 lg:py-6"
      >
        <div class="w-full mx-auto max-w-[1920px]">
          <router-view />
        </div>
      </main>

      <Footer />
    </div>
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
  if (!isMobile.value) sidebarOpen.value = false
}

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const closeSidebarOnMobile = () => {
  if (isMobile.value) sidebarOpen.value = false
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
})
</script>

<style>
/* Add xs breakpoint for very small devices */
@media (min-width: 480px) {
  .xs\:px-3 {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }
  .xs\:py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }
}
</style>