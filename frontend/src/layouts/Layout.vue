<template>
  <div class="flex h-screen overflow-hidden bg-slate-50">
    <!-- Desktop Sidebar -->
    <div class="hidden md:block">
      <Sidebar
        :collapsed="sidebarCollapsed"
        :is-mobile="false"
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
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-40 md:hidden"
        @click="closeMobileSidebar"
      />
    </Transition>

    <!-- Mobile Sidebar - Slides Down from Top -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-250 ease-in"
      enter-from-class="-translate-y-full opacity-0"
      leave-to-class="-translate-y-full opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-from-class="translate-y-0 opacity-100"
    >
      <div
        v-if="mobileSidebarOpen"
        class="fixed top-0 left-0 right-0 z-50 md:hidden shadow-2xl"
      >
        <Sidebar
          :collapsed="false"
          :is-mobile="true"
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

      <!-- Page Content with Smooth Transitions -->
      <main class="flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-5 md:p-6 lg:p-8">
        <div class="w-full max-w-[1920px] mx-auto">
          <router-view v-slot="{ Component }">
            <transition
              mode="out-in"
              name="page"
            >
              <component :is="Component" />
            </transition>
          </router-view>
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

const sidebarCollapsed = ref(false)
const mobileSidebarOpen = ref(false)
const isMobile = ref(false)

const toggleSidebarCollapse = () => {
  sidebarCollapsed.value = !sidebarCollapsed.value
}

const toggleMobileSidebar = () => {
  mobileSidebarOpen.value = !mobileSidebarOpen.value
  if (mobileSidebarOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

const closeMobileSidebar = () => {
  mobileSidebarOpen.value = false
  document.body.style.overflow = ''
}

const checkMobile = () => {
  isMobile.value = window.innerWidth < 768
  if (!isMobile.value) {
    closeMobileSidebar()
  }
}

onMounted(() => {
  checkMobile()
  window.addEventListener('resize', checkMobile)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkMobile)
  document.body.style.overflow = ''
})
</script>

<style scoped>
/* Page Transition Animations */
.page-enter-active,
.page-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>