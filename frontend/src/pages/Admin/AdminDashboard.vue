<template>
  <div class="dashboard">
    <!-- Admin Overview Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Total Cases -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Total Cases</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ displayStats.total_cases }}</div>
        <div class="flex items-center gap-2 mt-2">
          <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
            {{ displayStats.active_cases }} Active
          </span>
        </div>
      </div>

      <!-- Total Users -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Total Users</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ displayAdminStats.total_users }}</div>
        <div class="flex items-center gap-2 mt-2">
          <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
            {{ displayAdminStats.lawyers }} Lawyers
          </span>
          <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full">
            {{ displayAdminStats.clerks }} Clerks
          </span>
        </div>
      </div>

      <!-- Pending Approvals (Combined) -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Pending Approvals</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ displayPendingTotal }}</div>
        <div class="flex items-center gap-2 mt-2">
          <span class="text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-full">
            {{ displayPendingDocuments }} Documents
          </span>
          <span class="text-xs text-blue-600 bg-blue-50 px-2 py-1 rounded-full">
            {{ displayPendingMovements }} Movements
          </span>
        </div>
      </div>

      <!-- Total Clients -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Total Clients</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ displayStats.total_clients }}</div>
        <div class="text-xs text-slate-500 mt-2">Active clients</div>
      </div>
    </div>

    <!-- User Activity Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">System Activity (Last 7 Days)</h3>
        <div class="h-64 flex items-center justify-center bg-slate-50 rounded-xl">
          <p class="text-slate-400">Activity chart coming soon</p>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-semibold text-slate-700 mb-4">User Logins Today</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">Active Users Today</span>
            <span class="text-lg font-bold text-[#1a4972]">{{ displayAdminStats.active_today || 0 }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">Logins Today</span>
            <span class="text-lg font-bold text-[#1a4972]">{{ displayAdminStats.logins_today || 0 }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-sm text-slate-600">Activities (7 days)</span>
            <span class="text-lg font-bold text-[#1a4972]">{{ displayAdminStats.activities_last_7_days || 0 }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-700">Recent System Activities</h3>
      </div>
      <div class="divide-y divide-slate-50">
        <div v-for="activity in displayRecentActivities" :key="activity.id" class="px-6 py-4 hover:bg-slate-50/50">
          <div class="flex items-start gap-3">
            <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
            <div class="flex-1">
              <p class="text-sm text-slate-700">
                <span class="font-semibold">{{ activity.user }}</span>
                {{ activity.action }}
              </p>
              <p class="text-xs text-slate-400 mt-1">{{ formatDate(activity.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  stats: Object,
  adminStats: Object,
  recentActivities: Array,
  pendingDocuments: [Number, String],
  pendingMovements: [Number, String],
  pendingTotal: [Number, String]
});

// Create reactive local copies that update when props change
const displayStats = ref({ ...props.stats });
const displayAdminStats = ref({ ...props.adminStats });
const displayRecentActivities = ref([...props.recentActivities || []]);
const displayPendingDocuments = ref(props.pendingDocuments || 0);
const displayPendingMovements = ref(props.pendingMovements || 0);
const displayPendingTotal = ref(props.pendingTotal || 0);

// Watch for prop changes and update local refs
watch(() => props.stats, (newVal) => {
  displayStats.value = { ...newVal };
}, { deep: true, immediate: true });

watch(() => props.adminStats, (newVal) => {
  displayAdminStats.value = { ...newVal };
}, { deep: true, immediate: true });

watch(() => props.recentActivities, (newVal) => {
  displayRecentActivities.value = [...(newVal || [])];
}, { deep: true, immediate: true });

watch(() => props.pendingDocuments, (newVal) => {
  displayPendingDocuments.value = newVal || 0;
}, { immediate: true });

watch(() => props.pendingMovements, (newVal) => {
  displayPendingMovements.value = newVal || 0;
}, { immediate: true });

watch(() => props.pendingTotal, (newVal) => {
  displayPendingTotal.value = newVal || 0;
}, { immediate: true });

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>