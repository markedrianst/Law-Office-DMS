<template>
  <div class="dashboard">
    <!-- Lawyer Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">My Cases</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ lawyerStats.assigned_cases }}</div>
        <div class="text-xs text-slate-500 mt-2">{{ lawyerStats.active_cases }} active</div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Pending Approvals</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ stats.pending_approvals }}</div>
        <div class="text-xs text-slate-500 mt-2">Awaiting your review</div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-center justify-between mb-4">
          <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <span class="text-xs font-semibold text-slate-400">Documents</span>
        </div>
        <div class="text-3xl font-bold text-slate-800">{{ stats.total_documents || 0 }}</div>
        <div class="text-xs text-slate-500 mt-2">In your cases</div>
      </div>
    </div>

    <!-- My Cases -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-700">My Active Cases</h3>
        <router-link to="/casemaster" class="text-xs text-[#1a4972] hover:text-[#0f2f4a] font-semibold">
          View All →
        </router-link>
      </div>
      
      <div v-if="casesLoading" class="py-8 text-center">
        <svg class="animate-spin w-6 h-6 text-[#1a4972] mx-auto" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
      </div>
      
      <table v-else class="w-full">
        <thead class="bg-slate-50 border-b border-slate-100">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Case Code</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Title</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Client</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Stage</th>
            <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Priority</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr v-for="item in myCases" :key="item.id" class="hover:bg-slate-50/50 transition">
            <td class="px-6 py-4 text-sm font-medium text-[#1a4972]">{{ item.case_code }}</td>
            <td class="px-6 py-4 text-sm text-slate-700">{{ item.title }}</td>
            <td class="px-6 py-4 text-sm text-slate-600">{{ item.client }}</td>
            <td class="px-6 py-4 text-sm text-slate-600">{{ item.stage }}</td>
            <td class="px-6 py-4">
              <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="priorityClass(item.priority)">
                {{ item.priority }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pending Approvals Quick View -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-700">Pending Your Approval</h3>
      </div>
      <div class="divide-y divide-slate-50">
        <div v-for="item in pendingItems" :key="item.id" class="px-6 py-4 hover:bg-slate-50/50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-800">{{ item.type === 'folder' ? 'Folder Movement' : 'Checklist Movement' }}</p>
              <p class="text-xs text-slate-500">Case: {{ item.case_code }} • {{ item.from_to }}</p>
            </div>
            <button @click="goToApprovals" 
              class="px-3 py-1.5 text-xs font-semibold text-white bg-[#1a4972] rounded-lg hover:bg-[#0f2f4a] transition">
              Review
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const props = defineProps({
  stats: Object,
  lawyerStats: Object,
  myCases: Array,
  pendingItems: Array,
  casesLoading: Boolean
});

const priorityClass = (priority) => ({
  'urgent': 'bg-red-100 text-red-700',
  'normal': 'bg-blue-100 text-blue-700',
  'low': 'bg-slate-100 text-slate-600'
}[priority] || 'bg-slate-100 text-slate-500');

const goToApprovals = () => router.push('/approvals');
</script>