<template>
  <footer class="bg-[#1a4972] border-t border-white/10 flex-shrink-0 px-4 sm:px-5 md:px-6 py-3 shadow-sm">
    <div class="flex items-center justify-between flex-wrap gap-3">
      <p class="text-xs text-white/50 whitespace-nowrap">
        &copy; {{ currentYear }} Pineda Law Office. All rights reserved.
      </p>
      <p class="text-xs text-white/40 whitespace-nowrap">
        Document Management System
      </p>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>