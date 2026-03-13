// In useNotifications.js
const refresh = async () => {
  await loadNotifications()
}

return {
  notifications,
  unreadCount,<template>
  <div class="min-h-screen p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Notifications</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Stay updated with your latest activities</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-4">
      <div class="flex flex-wrap items-center gap-3">
        <button
          @click="filter = 'all'"
          class="px-4 py-2 text-sm font-semibold rounded-xl transition-all"
          :class="filter === 'all' ? 'bg-[#1a4972] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          All
        </button>
        <button
          @click="filter = 'unread'"
          class="px-4 py-2 text-sm font-semibold rounded-xl transition-all"
          :class="filter === 'unread' ? 'bg-[#1a4972] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          Unread
          <span v-if="unreadCount" class="ml-1.5 px-1.5 py-0.5 text-xs rounded-full bg-white/20">
            {{ unreadCount }}
          </span>
        </button>
        <button
          @click="filter = 'read'"
          class="px-4 py-2 text-sm font-semibold rounded-xl transition-all"
          :class="filter === 'read' ? 'bg-[#1a4972] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
        >
          Read
        </button>

        <div class="flex-1"></div>

        <button
          v-if="unreadCount > 0"
          @click="handleMarkAllRead"
          class="px-4 py-2 text-sm font-semibold text-[#1a4972] bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          Mark All as Read
        </button>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <!-- Loading State -->
      <div v-if="isLoading" class="py-16 flex justify-center">
        <svg class="animate-spin w-8 h-8 text-[#1a4972]" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredNotifications.length === 0" class="py-16 flex flex-col items-center text-center">
        <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
          <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-700 mb-1">No notifications</h3>
        <p class="text-sm text-slate-400">You're all caught up!</p>
      </div>

      <!-- Notifications -->
      <div v-else class="divide-y divide-slate-100">
        <div
          v-for="item in filteredNotifications"
          :key="item.id"
          class="flex items-start gap-4 px-6 py-4 hover:bg-slate-50 cursor-pointer transition-all"
          :class="{ 'bg-blue-50/30': !item.is_read }"
          @click="goToNotification(item)"
        >
          <!-- Icon -->
          <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" :class="getIconClass(item)">
            {{ getIcon(item) }}
          </div>

          <!-- Content -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-sm font-bold" :class="item.is_read ? 'text-slate-700' : 'text-slate-900'">
                  {{ item.title }}
                </p>
                <p class="text-sm mt-0.5 text-slate-600">{{ item.message }}</p>
                <p v-if="item.data?.notes" class="text-xs text-slate-500 mt-1 italic">
                  📝 {{ item.data.notes }}
                </p>
                <div class="flex items-center gap-3 mt-2">
                  <span class="text-xs text-slate-400">{{ formatDateTime(item.created_at) }}</span>
                  <span
                    class="px-2 py-0.5 text-[10px] font-bold rounded-full"
                    :class="statusClass(item.type)"
                  >
                    {{ formatType(item.type) }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <span v-if="!item.is_read" class="w-2 h-2 rounded-full bg-[#1a4972]"></span>
                <button
                  v-if="!item.is_read"
                  @click.stop="markAsRead(item.id)"
                  class="px-3 py-1 text-xs font-semibold text-[#1a4972] hover:bg-blue-100 rounded-lg transition-colors"
                >
                  Mark Read
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        <p class="text-xs text-slate-500">
          Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} notifications
        </p>
        <div class="flex items-center gap-1">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-30 disabled:cursor-not-allowed transition"
          >
            ←
          </button>
          <span class="px-3 py-1 text-sm font-semibold text-[#1a4972]">{{ currentPage }} / {{ totalPages }}</span>
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-600 hover:bg-slate-200 disabled:opacity-30 disabled:cursor-not-allowed transition"
          >
            →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useNotifications } from '@/composables/useNotifications'

const router = useRouter()
const { notifications, unreadCount, markAsRead, markAllAsRead } = useNotifications()

// State
const filter = ref('all')
const currentPage = ref(1)
const perPage = ref(10)
const isLoading = ref(false)

// Computed
const filteredNotifications = computed(() => {
  let filtered = [...notifications.value]

  if (filter.value === 'unread') {
    filtered = filtered.filter(n => !n.is_read)
  } else if (filter.value === 'read') {
    filtered = filtered.filter(n => n.is_read)
  }

  // Pagination
  const start = (currentPage.value - 1) * perPage.value
  return filtered.slice(start, start + perPage.value)
})

const totalPages = computed(() => {
  let total = notifications.value.length
  if (filter.value === 'unread') {
    total = notifications.value.filter(n => !n.is_read).length
  } else if (filter.value === 'read') {
    total = notifications.value.filter(n => n.is_read).length
  }
  return Math.max(1, Math.ceil(total / perPage.value))
})

const pagination = computed(() => {
  const start = (currentPage.value - 1) * perPage.value + 1
  const end = Math.min(start + perPage.value - 1, notifications.value.length)
  return {
    from: start,
    to: end,
    total: notifications.value.length
  }
})

// Methods
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

const statusClass = (type) => {
  if (type?.includes('approved')) return 'bg-emerald-50 text-emerald-700'
  if (type?.includes('rejected')) return 'bg-red-50 text-red-700'
  if (type?.includes('pending'))  return 'bg-amber-50 text-amber-700'
  return 'bg-slate-50 text-slate-600'
}

const formatType = (type) => {
  if (!type) return 'Notification'
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}

const formatDateTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const handleMarkAllRead = async () => {
  await markAllAsRead()
}

const goToNotification = async (item) => {
  if (!item.is_read) {
    await markAsRead(item.id)
  }

  if (item.action_url) {
    router.push(item.action_url)
  } else if (item.data?.case_id) {
    router.push(`/casemaster`)
  } else if (item.type?.includes('approval')) {
    router.push('/approvals')
  }
}
</script>
  markAsRead,
  markAllAsRead,
  refresh  // Add this
}