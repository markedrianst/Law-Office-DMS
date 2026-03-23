<template>
  <div class="min-h-screen p-4 sm:p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Courts & Offices</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage courts, prosecutor offices, and agencies</p>
    </div>

    <!-- Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-3 mb-4 space-y-2">
      <!-- Row 1: Search -->
      <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
          <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <input v-model="searchQuery" @input="debouncedSearch" type="text"
          placeholder="Search courts..."
          class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:bg-white transition-all"/>
      </div>
      <!-- Row 2: Filters + Buttons -->
      <div class="flex flex-wrap items-center gap-2">
        <select v-model="typeFilter" @change="handleFilterChange"
          class="flex-1 min-w-[110px] px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
          <option value="">All Types</option>
          <option v-for="type in courtTypes" :key="type" :value="type">{{ type }}</option>
        </select>
        <select v-model="statusFilter" @change="handleFilterChange"
          class="flex-1 min-w-[110px] px-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer">
          <option value="">All Status</option>
          <option value="true">Active Only</option>
          <option value="false">Inactive Only</option>
        </select>
        <button @click="manualRefresh" :disabled="isRefreshing"
          class="shrink-0 px-3 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-1.5 transition-all whitespace-nowrap hover:shadow-md active:scale-95 disabled:opacity-50 bg-white text-[#1a4972] border border-[#1a4972]/30 hover:bg-[#1a4972]/5">
          <svg v-if="isRefreshing" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          {{ isRefreshing ? 'Refreshing...' : 'Refresh' }}
        </button>
        <button @click="openCreateModal" :disabled="isAdding"
          class="shrink-0 text-white px-4 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center gap-1.5 transition-all whitespace-nowrap hover:shadow-lg active:scale-95 disabled:opacity-50 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30">
          <svg v-if="!isAdding" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ isAdding ? 'Adding...' : 'Add Court' }}
        </button>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="bg-white rounded-2xl shadow-sm border border-slate-100 py-16 flex flex-col items-center">
      <div class="w-12 h-12 rounded-full border-4 border-blue-200 border-t-[#1a4972] animate-spin mb-4"></div>
      <p class="text-sm text-slate-500">Loading courts...</p>
    </div>

    <div v-else class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

      <!-- ══ MOBILE: Cards ══ -->
      <div class="block sm:hidden">
        <div v-if="courts.length === 0" class="flex flex-col items-center py-14 px-6">
          <div class="w-14 h-14 rounded-2xl bg-[#1a4972]/10 flex items-center justify-center mb-3">
            <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
          <p class="text-sm font-semibold text-slate-700 mb-1">No courts found</p>
          <p class="text-xs text-slate-400">Click "Add Court" to create one</p>
        </div>
        <div v-else class="p-3 space-y-2.5">
          <div v-for="item in paginatedCourts" :key="item.id"
            class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <!-- Name + type badge + status -->
            <div class="flex items-start justify-between gap-3 mb-2">
              <div class="min-w-0">
                <p class="text-sm font-bold text-slate-800 leading-snug">{{ item.name }}</p>
                <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold rounded-full" :class="typeBadgeClass(item.type)">{{ item.type }}</span>
              </div>
              <span class="shrink-0 px-2.5 py-1 text-xs font-semibold rounded-lg"
                :class="item.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            <!-- Details tiles -->
            <div class="grid grid-cols-2 gap-2 mb-3">
              <div v-if="item.address" class="bg-slate-50 rounded-xl p-2.5 col-span-2">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Address</p>
                <p class="text-xs text-slate-700">{{ item.address }}</p>
              </div>
              <div v-if="item.contact_info" class="bg-slate-50 rounded-xl p-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Contact</p>
                <p class="text-xs text-slate-700">{{ item.contact_info }}</p>
              </div>
              <div class="bg-slate-50 rounded-xl p-2.5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Sort / Created</p>
                <p class="text-xs text-slate-700">{{ item.sort_order }} · {{ formatDateHelper(item.created_at) }}</p>
              </div>
            </div>
            <!-- Actions -->
            <div class="flex gap-2">
              <button @click="editItem(item)" :disabled="editingId === item.id"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold text-[#1a4972] bg-[#1a4972]/8 hover:bg-[#1a4972]/15 border border-[#1a4972]/20 transition-all active:scale-95 disabled:opacity-50">
                <svg v-if="editingId !== item.id" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <svg v-else class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Edit
              </button>
              <button @click="toggleStatus(item)" :disabled="togglingId === item.id"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold transition-all active:scale-95 disabled:opacity-50"
                :class="item.is_active ? 'text-amber-600 bg-amber-50 border border-amber-100' : 'text-emerald-600 bg-emerald-50 border border-emerald-100'">
                <svg v-if="togglingId !== item.id" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="item.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg v-else class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ item.is_active ? 'Deactivate' : 'Activate' }}
              </button>
              <button @click="confirmDelete(item)" :disabled="deletingId === item.id"
                class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 transition-all active:scale-95 disabled:opacity-50">
                <svg v-if="deletingId !== item.id" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <svg v-else class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ══ DESKTOP: Table ══ -->
      <div class="hidden sm:block overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b border-slate-100 bg-[#1a4972]/5">
              <th v-for="col in columns" :key="col.field" scope="col"
                class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
                :class="col.sortable ? 'cursor-pointer hover:text-[#1a4972] select-none group' : ''"
                @click="col.sortable ? sortBy(col.field) : null">
                <div class="flex items-center gap-1.5">
                  {{ col.label }}
                  <svg v-if="col.sortable && sortField === col.field" class="w-3.5 h-3.5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :d="sortDirection === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" stroke-width="2.5"/>
                  </svg>
                  <svg v-else-if="col.sortable" class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                  </svg>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr v-for="(item, index) in paginatedCourts" :key="item.id"
              class="transition-all duration-300 hover:bg-blue-50/30 group"
              :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.03}s both` }">
              <td class="px-5 py-4">
                <div>
                  <p class="text-sm font-semibold text-slate-800">{{ item.name }}</p>
                  <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded-full" :class="typeBadgeClass(item.type)">{{ item.type }}</span>
                </div>
              </td>
              <td class="px-5 py-4 text-sm text-slate-600">{{ item.address || '—' }}</td>
              <td class="px-5 py-4 text-sm text-slate-600">{{ item.contact_info || '—' }}</td>
              <td class="px-5 py-4 text-sm text-slate-600">{{ item.sort_order }}</td>
              <td class="px-5 py-4">
                <span class="px-2.5 py-1 text-xs font-semibold rounded-lg"
                  :class="item.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                  {{ item.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-5 py-4 text-sm text-slate-400">{{ formatDateHelper(item.created_at) }}</td>
              <td class="px-5 py-4">
                <div class="flex items-center gap-2">
                  <button @click="editItem(item)" :disabled="editingId === item.id"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-[#1a4972] hover:bg-[#1a4972]/10 transition-all disabled:opacity-50">
                    <svg v-if="editingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ editingId === item.id ? 'Editing...' : 'Edit' }}
                  </button>
                  <button @click="toggleStatus(item)" :disabled="togglingId === item.id"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold"
                    :class="item.is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50'">
                    <svg v-if="togglingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="item.is_active" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ togglingId === item.id ? '...' : (item.is_active ? 'Deactivate' : 'Activate') }}
                  </button>
                  <button @click="confirmDelete(item)" :disabled="deletingId === item.id"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-600 text-sm font-semibold hover:bg-red-50 transition-all disabled:opacity-50">
                    <svg v-if="deletingId !== item.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    {{ deletingId === item.id ? 'Deleting...' : 'Delete' }}
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="courts.length === 0">
              <td :colspan="columns.length" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center">
                  <div class="w-14 h-14 rounded-2xl bg-[#1a4972]/10 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                  </div>
                  <p class="text-sm font-semibold text-slate-700 mb-1">No courts found</p>
                  <p class="text-xs text-slate-400">Click "Add Court" to create one</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="flex flex-col sm:flex-row items-center justify-between gap-2 px-4 sm:px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
        <p class="text-xs text-slate-500 order-2 sm:order-1">
          Showing <span class="font-semibold text-slate-700">{{ pagination.from }}</span> to
          <span class="font-semibold text-slate-700">{{ pagination.to }}</span> of
          <span class="font-semibold text-slate-700">{{ pagination.total }}</span> courts
        </p>
        <div class="flex items-center gap-1 order-1 sm:order-2">
          <button @click="previousPage" :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'">← Prev</button>
          <button v-for="page in displayedPages" :key="page" @click="goToPage(page)"
            class="w-7 h-7 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === page ? 'bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white' : 'text-slate-600 hover:bg-slate-200'">{{ page }}</button>
          <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === pagination.last_page ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'">Next →</button>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeModal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-2xl overflow-hidden max-h-[90dvh] flex flex-col">
          <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100 flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-[#1a4972]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <div>
                <h2 class="text-base font-bold text-slate-800">{{ isEditing ? 'Edit Court' : 'Add Court' }}</h2>
                <p class="text-xs text-slate-500">{{ isEditing ? 'Update court details' : 'Create a new court or office' }}</p>
              </div>
            </div>
            <button @click="closeModal" :disabled="formLoading" class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="px-5 sm:px-6 py-5 space-y-4 overflow-y-auto">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Court/Office Name <span class="text-red-500">*</span></label>
              <input v-model="form.name" type="text" placeholder="e.g. RTC Branch 16, Urdaneta City"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                :class="{ 'border-red-400': errors.name }"/>
              <p v-if="errors.name" class="text-sm text-red-500 mt-1">{{ errors.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Type</label>
              <select v-model="form.type" class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] text-slate-600">
                <option v-for="type in courtTypes" :key="type" :value="type">{{ type }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address <span class="text-slate-400 font-normal text-xs">(Optional)</span></label>
              <input v-model="form.address" type="text" placeholder="Enter full address"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"/>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contact Info <span class="text-slate-400 font-normal text-xs">(Optional)</span></label>
              <input v-model="form.contact_info" type="text" placeholder="Phone, email, etc."
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"/>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sort Order</label>
              <input v-model.number="form.sort_order" type="number" min="0" placeholder="0"
                class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"/>
              <p class="text-xs text-slate-400 mt-1">Lower numbers appear first</p>
            </div>
            <div class="flex items-center gap-2">
              <input type="checkbox" v-model="form.is_active" id="isActive" class="w-4 h-4 rounded border-slate-300 text-[#1a4972] focus:ring-[#1a4972]"/>
              <label for="isActive" class="text-sm font-medium text-slate-700">Active</label>
            </div>
          </div>
          <div class="flex items-center justify-end gap-3 px-5 sm:px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex-shrink-0">
            <button @click="closeModal" :disabled="formLoading"
              class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
            <button @click="submitForm" :disabled="formLoading"
              class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl flex items-center gap-2 min-w-[110px] justify-center bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30 active:scale-95">
              <svg v-if="formLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ formLoading ? (isEditing ? 'Saving...' : 'Adding...') : (isEditing ? 'Save Changes' : 'Add Court') }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue';
import { debounce } from 'lodash';
import { courtService } from '@/services/masterData';
import Swal from 'sweetalert2';

import { 
  getCourts,
  setCourts,
  addCourt,
  updateCourtInStore,
  removeCourtFromStore,
  listenForUpdates,
  formatDate
} from '@/utils/appUtils';

const columns = [
  { label: 'Court/Office', field: 'name', sortable: true },
  { label: 'Address', field: 'address', sortable: false },
  { label: 'Contact', field: 'contact_info', sortable: false },
  { label: 'Sort Order', field: 'sort_order', sortable: true },
  { label: 'Status', field: 'is_active', sortable: true },
  { label: 'Created', field: 'created_at', sortable: true },
  { label: 'Actions', field: 'actions', sortable: false },
];

const initialCourts = getCourts();
const courts = ref(initialCourts || []);
const courtTypes = ref(['Court', 'Prosecutor', 'Agency', 'Others']);
const isLoading = ref(!initialCourts || initialCourts.length === 0);
const isRefreshing = ref(false);

const pagination = ref({
  current_page: 1, last_page: 1, per_page: 15,
  total: courts.value.length, from: 1, to: courts.value.length
});

const searchQuery = ref('');
const typeFilter = ref('');
const statusFilter = ref('');
const sortField = ref('sort_order');
const sortDirection = ref('asc');
const currentPage = ref(1);

const isAdding = ref(false);
const editingId = ref(null);
const togglingId = ref(null);
const deletingId = ref(null);
const formLoading = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const editingItemId = ref(null);

const form = reactive({
  name: '', type: 'Court', address: '', contact_info: '', sort_order: 0, is_active: true
});
const errors = reactive({ name: '', type: '', address: '', contact_info: '' });

const getNextSortOrder = () => {
  const normalItems = courts.value.filter(c => c.sort_order < 9000);
  if (normalItems.length === 0) return 1;
  return Math.max(...normalItems.map(c => c.sort_order)) + 1;
};

const displayedPages = computed(() => {
  const pages = []; const max = 5;
  const total = pagination.value.last_page || 1;
  const current = pagination.value.current_page || 1;
  if (total <= max) { for (let i = 1; i <= total; i++) pages.push(i); }
  else {
    let s = Math.max(1, current - 2); let e = Math.min(total, s + max - 1);
    if (e - s + 1 < max) s = Math.max(1, e - max + 1);
    for (let i = s; i <= e; i++) pages.push(i);
  }
  return pages;
});

const filteredCourts = computed(() => {
  let filtered = courts.value;
  if (typeFilter.value) filtered = filtered.filter(c => c.type === typeFilter.value);
  if (statusFilter.value) {
    const isActive = statusFilter.value === 'true';
    filtered = filtered.filter(c => c.is_active === isActive);
  }
  if (searchQuery.value) {
    const search = searchQuery.value.toLowerCase();
    filtered = filtered.filter(c => c.name?.toLowerCase().includes(search) || c.address?.toLowerCase().includes(search) || c.contact_info?.toLowerCase().includes(search));
  }
  filtered = [...filtered].sort((a, b) => {
    let aVal = a[sortField.value]; let bVal = b[sortField.value];
    if (sortField.value === 'created_at') { aVal = aVal ? new Date(aVal) : 0; bVal = bVal ? new Date(bVal) : 0; }
    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
  return filtered;
});

const paginatedCourts = computed(() => {
  const start = (currentPage.value - 1) * pagination.value.per_page;
  return filteredCourts.value.slice(start, start + pagination.value.per_page);
});

watch(filteredCourts, (newVal) => {
  pagination.value.total = newVal.length;
  pagination.value.last_page = Math.ceil(newVal.length / pagination.value.per_page);
  pagination.value.from = (currentPage.value - 1) * pagination.value.per_page + 1;
  pagination.value.to = Math.min(currentPage.value * pagination.value.per_page, newVal.length);
  if (currentPage.value > pagination.value.last_page) currentPage.value = 1;
}, { immediate: true });

const typeBadgeClass = (type) => {
  const classes = {
    'Court': 'bg-blue-50 text-blue-700 border border-blue-200',
    'Prosecutor': 'bg-amber-50 text-amber-700 border border-amber-200',
    'Agency': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'Others': 'bg-slate-50 text-slate-600 border border-slate-200'
  };
  return classes[type] || 'bg-slate-50 text-slate-600';
};

const fetchCourts = async (showLoading = false) => {
  try {
    const params = { search: searchQuery.value || undefined, type: typeFilter.value || undefined, is_active: statusFilter.value || undefined, sort_by: sortField.value, sort_direction: sortDirection.value, page: currentPage.value, per_page: pagination.value.per_page };
    await courtService.getCourts(params);
  } catch (error) {
    console.error('Failed to load courts:', error);
    Swal.fire({ icon: 'error', title: 'Error', text: error.message || 'Failed to load courts', timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
  } finally {
    if (showLoading) isLoading.value = false;
    isRefreshing.value = false;
  }
};

const loadCourtTypes = async () => {
  try {
    const response = await courtService.getCourtTypes();
    courtTypes.value = response.data || ['Court', 'Prosecutor', 'Agency', 'Others'];
  } catch (error) { console.error('Failed to load court types:', error); }
};

const initialize = async () => {
  await loadCourtTypes();
  if (courts.value.length === 0) { manualRefresh(); }
  else { isLoading.value = false; fetchCourts(false); }
};

const debouncedSearch = debounce(() => { currentPage.value = 1; }, 500);
const handleFilterChange = () => { currentPage.value = 1; };
const sortBy = (field) => {
  if (sortField.value === field) { sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'; }
  else { sortField.value = field; sortDirection.value = 'asc'; }
};
const previousPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < pagination.value.last_page) currentPage.value++; };
const goToPage = (page) => { currentPage.value = page; };

const manualRefresh = async () => {
  isRefreshing.value = true;
  await fetchCourts(true);
  isRefreshing.value = false;
  Swal.fire({ icon: 'success', title: 'Refreshed!', text: 'Courts list refreshed', timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
};

const resetForm = () => {
  form.name = ''; form.type = 'Court'; form.address = ''; form.contact_info = ''; form.sort_order = 0; form.is_active = true;
  errors.name = ''; errors.type = ''; errors.address = ''; errors.contact_info = '';
};
const clearErrors = () => { errors.name = ''; errors.type = ''; errors.address = ''; errors.contact_info = ''; };

const openCreateModal = async () => {
  resetForm(); isEditing.value = false; editingItemId.value = null;
  form.sort_order = getNextSortOrder();
  showModal.value = true;
};

const editItem = (item) => {
  resetForm(); isEditing.value = true; editingItemId.value = item.id;
  form.name = item.name; form.type = item.type; form.address = item.address || '';
  form.contact_info = item.contact_info || ''; form.sort_order = item.sort_order; form.is_active = item.is_active;
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; resetForm(); };

const submitForm = async () => {
  formLoading.value = true; clearErrors();
  try {
    const payload = { name: form.name, type: form.type, address: form.address || null, contact_info: form.contact_info || null, sort_order: form.sort_order, is_active: form.is_active };
    if (isEditing.value) {
      editingId.value = editingItemId.value;
      await courtService.updateCourt(editingItemId.value, payload);
      await fetchCourts(true);
      Swal.fire({ icon: 'success', title: 'Success!', text: 'Court updated successfully', timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
    } else {
      isAdding.value = true;
      await courtService.createCourt(payload);
      await fetchCourts(true);
      Swal.fire({ icon: 'success', title: 'Success!', text: 'Court created successfully', timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
    }
    closeModal();
  } catch (error) {
    if (error.errors) {
      if (error.errors.name) errors.name = error.errors.name[0];
      if (error.errors.type) errors.type = error.errors.type[0];
    }
    Swal.fire({ icon: 'error', title: 'Error!', text: error.message || 'An error occurred', timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
  } finally {
    formLoading.value = false; isAdding.value = false; editingId.value = null;
  }
};

const toggleStatus = async (item) => {
  togglingId.value = item.id;
  try {
    await courtService.toggleCourt(item.id);
    await fetchCourts(true);
    Swal.fire({ icon: 'success', title: 'Success!', text: `Court ${!item.is_active ? 'activated' : 'deactivated'} successfully`, timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
  } catch (error) {
    Swal.fire({ icon: 'error', title: 'Error!', text: error.message || 'Failed to toggle status', timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
  } finally { togglingId.value = null; }
};

const confirmDelete = async (item) => {
  const result = await Swal.fire({ title: 'Delete Court?', text: `Are you sure you want to delete "${item.name}"?`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc2626', cancelButtonColor: '#64748b', confirmButtonText: 'Yes, delete', cancelButtonText: 'Cancel' });
  if (result.isConfirmed) {
    deletingId.value = item.id;
    try {
      await courtService.deleteCourt(item.id);
      await fetchCourts(true);
      Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Court deleted successfully', timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
    } catch (error) {
      Swal.fire({ icon: 'error', title: 'Error!', text: error.message || 'Failed to delete court', timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
    } finally { deletingId.value = null; }
  }
};

const handleCourtsUpdated = (event) => {
  courts.value = event.detail;
  if (courts.value.length > 0) isLoading.value = false;
};

let cleanup = null;

onMounted(async () => {
  await initialize();
  cleanup = listenForUpdates('courts-updated', handleCourtsUpdated);
});

onUnmounted(() => {
  if (cleanup) cleanup();
  debouncedSearch.cancel();
});

const formatDateHelper = (date) => formatDate(date);
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
@media (max-width: 639px) {
  .modal-enter-from, .modal-leave-to { transform: translateY(20px); }
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>