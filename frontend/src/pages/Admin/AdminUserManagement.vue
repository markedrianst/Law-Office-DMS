<!-- User Management - Responsive (card on mobile, table on desktop) -->
<template>
  <div class="min-h-screen p-4 sm:p-6 bg-slate-50 font-sans">

    <!-- ── Header ── -->
    <div class="mb-6">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">User Management</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage lawyers and clerks</p>
    </div>

    <!-- ── Toolbar ── -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-3 mb-4">
      <!-- Row 1: Search + Role Filter -->
      <div class="flex items-center gap-2 mb-2">
        <div class="relative group flex-1">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400 group-focus-within:text-[#1a4972] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <input v-model="searchQuery" @input="debouncedSearch" type="text"
            placeholder="Search by name or email..."
            class="w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:bg-white transition-all placeholder-slate-400" />
        </div>
        <select v-model="roleFilter" @change="handleFilterChange"
          class="shrink-0 py-2.5 px-3 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100 transition-all w-[112px]">
          <option value="">All Roles</option>
          <option v-for="role in availableRoles" :key="role.id" :value="role.name">{{ role.name }}</option>
        </select>
      </div>

      <!-- Row 2: Refresh + Add New User (always side-by-side) -->
      <div class="flex items-center gap-2">
        <!-- Refresh -->
        <button @click="manualRefresh" :disabled="isRefreshing"
          class="flex-1 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center gap-1.5 transition-all hover:shadow-md active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed bg-white text-[#1a4972] border border-[#1a4972]/30 hover:bg-[#1a4972]/5 whitespace-nowrap">
          <svg v-if="isRefreshing" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          {{ isRefreshing ? 'Refreshing...' : 'Refresh' }}
        </button>

        <!-- Add User -->
        <button @click="openAddUserModal" :disabled="isAdding"
          class="flex-1 text-white py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center gap-1.5 transition-all hover:shadow-lg active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30 whitespace-nowrap">
          <svg v-if="!isAdding" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ isAdding ? 'Adding...' : 'Add New User' }}
        </button>
      </div>
    </div>

    <!-- ── Content Area ── -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

      <!-- Loading -->
      <div v-if="isLoading" class="flex justify-center items-center py-16">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#1a4972]"></div>
        <span class="ml-3 text-sm text-slate-500">Loading users...</span>
      </div>

      <template v-else>

        <!-- ═══════════════════════════════════════
             MOBILE  —  Card list  (hidden sm+)
        ═══════════════════════════════════════ -->
        <div class="block sm:hidden">

          <!-- Empty state -->
          <div v-if="paginatedUsers.length === 0" class="flex flex-col items-center py-14 px-6 text-center">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 bg-[#1a4972]/10">
              <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </div>
            <p class="text-sm font-semibold text-slate-700 mb-1">No users found</p>
            <p class="text-xs text-slate-400">Try adjusting your search or add a new user</p>
          </div>

          <!-- Cards -->
          <div v-else class="divide-y divide-slate-100">
            <div
              v-for="(user, index) in paginatedUsers" :key="user.id"
              class="p-4 transition-colors hover:bg-slate-50/60"
              :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.04}s both` }">

              <!-- Top row: avatar + name + badges -->
              <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-center gap-3 min-w-0">
                  <!-- Initials avatar -->
                  <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#1a4972] to-[#2d6db5] flex items-center justify-center text-white text-sm font-bold shrink-0 shadow-sm">
                    {{ (user?.name || '?').charAt(0).toUpperCase() }}
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ user?.name || '—' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ user?.email || '—' }}</p>
                  </div>
                </div>
                <!-- Role + Status -->
                <div class="flex flex-col items-end gap-1.5 shrink-0">
                  <span class="px-2 py-0.5 text-xs font-semibold rounded-lg"
                    :class="{
                      'bg-[#1a4972]/10 text-[#1a4972]': user?.role === 'Lawyer',
                      'bg-emerald-50 text-emerald-700': user?.role === 'Clerk'
                    }">
                    {{ user?.role || '—' }}
                  </span>
                  <div class="flex items-center gap-1">
                    <div class="w-1.5 h-1.5 rounded-full"
                      :class="user?.status === 'Active' ? 'bg-emerald-500' : 'bg-red-500'"></div>
                    <span class="text-xs font-medium"
                      :class="user?.status === 'Active' ? 'text-emerald-700' : 'text-red-700'">
                      {{ user?.status || '—' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Dates row -->
              <div class="grid grid-cols-2 gap-2 mb-3">
                <div class="bg-slate-50 rounded-lg px-3 py-2">
                  <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Created</p>
                  <p class="text-xs text-slate-600 font-medium leading-snug">{{ formatDate(user?.created_at) }}</p>
                </div>
                <div class="bg-slate-50 rounded-lg px-3 py-2">
                  <p class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Last Login</p>
                  <p class="text-xs text-slate-600 font-medium leading-snug">{{ formatLastLogin(user?.last_login) }}</p>
                </div>
              </div>

              <!-- Action buttons -->
              <div class="flex gap-2">
                <button @click="editUser(user)" :disabled="isEditingUser === user.id"
                  class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-sm font-semibold transition-all active:scale-95 disabled:opacity-50 text-[#1a4972] bg-[#1a4972]/8 hover:bg-[#1a4972]/15 border border-[#1a4972]/20">
                  <svg v-if="isEditingUser !== user.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ isEditingUser === user.id ? 'Editing...' : 'Edit' }}
                </button>
                <button @click="confirmDeleteUser(user)" :disabled="isDeletingUser === user.id"
                  class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-xl text-sm font-semibold transition-all active:scale-95 disabled:opacity-50 text-red-600 bg-red-50 hover:bg-red-100 border border-red-100">
                  <svg v-if="isDeletingUser !== user.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ isDeletingUser === user.id ? 'Deleting...' : 'Delete' }}
                </button>
              </div>

            </div>
          </div>
        </div>

        <!-- ═══════════════════════════════════════
             DESKTOP  —  Table  (hidden below sm)
        ═══════════════════════════════════════ -->
        <div class="hidden sm:block overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr class="border-b border-slate-100 bg-[#1a4972]/5">
                <th v-for="col in columns" :key="col.field" scope="col"
                  class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 whitespace-nowrap"
                  :class="col.sortable ? 'cursor-pointer hover:text-[#1a4972] select-none group' : ''"
                  @click="col.sortable ? sortBy(col.field) : null">
                  <div class="flex items-center gap-1.5">
                    {{ col.label }}
                    <svg v-if="col.sortable && sortField === col.field"
                      class="w-3.5 h-3.5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path :d="sortDirection === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <svg v-else-if="col.sortable"
                      class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                  </div>
                </th>
              </tr>
            </thead>

            <tbody class="divide-y divide-slate-50" v-if="paginatedUsers.length > 0">
              <tr v-for="(user, index) in paginatedUsers" :key="user.id"
                class="transition-all duration-300 hover:bg-blue-50/30 group"
                :style="{ animation: `fadeIn 0.3s ease-out ${index * 0.03}s both` }">
                <td class="px-5 py-4">
                  <p class="text-sm font-semibold text-slate-800">{{ user?.name || '—' }}</p>
                  <p class="text-xs text-slate-400">{{ user?.email || '—' }}</p>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <span class="px-2.5 py-1 text-xs font-semibold rounded-lg inline-block"
                    :class="{
                      'bg-[#1a4972]/10 text-[#1a4972]': user?.role === 'Lawyer',
                      'bg-emerald-50 text-emerald-700': user?.role === 'Clerk'
                    }">{{ user?.role || '—' }}</span>
                </td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-1.5">
                    <div class="w-1.5 h-1.5 rounded-full"
                      :class="user?.status === 'Active' ? 'bg-emerald-500' : 'bg-red-500'"></div>
                    <span class="text-xs font-medium"
                      :class="user?.status === 'Active' ? 'text-emerald-700' : 'text-red-700'">
                      {{ user?.status || '—' }}
                    </span>
                  </div>
                </td>
                <td class="px-5 py-4 text-sm text-slate-400 whitespace-nowrap">{{ formatDate(user?.created_at) }}</td>
                <td class="px-5 py-4 text-sm text-slate-400 whitespace-nowrap">{{ formatLastLogin(user?.last_login) }}</td>
                <td class="px-5 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-2 opacity-80 group-hover:opacity-100 transition-opacity">
                    <button @click="editUser(user)" :disabled="isEditingUser === user.id"
                      class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold transition-all hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 text-[#1a4972] hover:bg-[#1a4972]/10">
                      <svg v-if="isEditingUser !== user.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                      </svg>
                      {{ isEditingUser === user.id ? 'Editing...' : 'Edit' }}
                    </button>
                    <button @click="confirmDeleteUser(user)" :disabled="isDeletingUser === user.id"
                      class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-600 text-sm font-semibold transition-all hover:bg-red-50 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100">
                      <svg v-if="isDeletingUser !== user.id" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      <svg v-else class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                      </svg>
                      {{ isDeletingUser === user.id ? 'Deleting...' : 'Delete' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Empty state desktop -->
            <tbody v-else>
              <tr>
                <td :colspan="columns.length" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-3 bg-[#1a4972]/10">
                      <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                      </svg>
                    </div>
                    <p class="text-sm font-semibold text-slate-700 mb-1">No users found</p>
                    <p class="text-xs text-slate-400">Try adjusting your search or add a new user</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- ── Pagination (shared by both views) ── -->
        <div v-if="pagination.total > 0"
          class="flex flex-col sm:flex-row items-center justify-between gap-2 px-4 sm:px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
          <p class="text-xs text-slate-500 order-2 sm:order-1">
            Showing <span class="font-semibold text-slate-700">{{ pagination.from }}</span>–<span class="font-semibold text-slate-700">{{ pagination.to }}</span>
            of <span class="font-semibold text-slate-700">{{ pagination.total }}</span> users
          </p>
          <div class="flex items-center gap-1 order-1 sm:order-2">
            <button @click="previousPage" :disabled="pagination.current_page === 1"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
              :class="pagination.current_page === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200 active:scale-95'">
              ← Prev
            </button>
            <button v-for="page in displayedPages" :key="page" @click="goToPage(page)"
              class="w-7 h-7 rounded-lg text-xs font-medium transition-all hover:scale-110 active:scale-95"
              :class="pagination.current_page === page
                ? 'bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white shadow-md shadow-[#1a4972]/30'
                : 'text-slate-600 hover:bg-slate-200'">
              {{ page }}
            </button>
            <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
              :class="pagination.current_page === pagination.last_page ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200 active:scale-95'">
              Next →
            </button>
          </div>
        </div>

      </template>
    </div>


    <!-- ═══════════════════════════════════════════
         ADD / EDIT MODAL
    ═══════════════════════════════════════════ -->
    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" @click.self="closeModal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

        <div class="relative bg-white w-full sm:rounded-2xl shadow-2xl sm:max-w-2xl max-h-[95dvh] sm:max-h-[90vh] flex flex-col overflow-hidden rounded-t-2xl">

          <!-- Modal Header -->
          <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100 flex-shrink-0 bg-gradient-to-r from-[#1a4972]/5 to-transparent">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-[#1a4972]/10">
                <svg class="w-5 h-5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-base font-bold text-slate-800">{{ isEditing ? 'Edit User' : 'Add New User' }}</h2>
                <p class="text-xs text-slate-500">{{ isEditing ? 'Update user information' : 'Fill in the details to create a new account' }}</p>
              </div>
            </div>
            <button @click="closeModal" :disabled="formLoading"
              class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all hover:scale-110 active:scale-95 disabled:opacity-50">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="px-5 sm:px-6 py-5 overflow-y-auto space-y-5">

            <!-- ── Personal Info ── -->
            <div>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-1 h-4 rounded-full bg-[#1a4972]"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#1a4972]">Personal Info</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                  <input v-model="form.firstName" @input="clearFieldError('firstName')" type="text" :disabled="formLoading"
                    placeholder="Enter first name"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50"
                    :class="{ 'border-red-400 bg-red-50/30': errors.firstName }" />
                  <p v-if="errors.firstName" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.firstName }}
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                  <input v-model="form.lastName" @input="clearFieldError('lastName')" type="text" :disabled="formLoading"
                    placeholder="Enter last name"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50"
                    :class="{ 'border-red-400 bg-red-50/30': errors.lastName }" />
                  <p v-if="errors.lastName" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.lastName }}
                  </p>
                </div>
              </div>
            </div>

            <!-- ── Contact Details ── -->
            <div>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-1 h-4 rounded-full bg-[#1a4972]"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#1a4972]">Contact Details</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <!-- Email -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                  <input v-model="form.email" @input="clearFieldError('email')" type="email" :disabled="formLoading"
                    placeholder="name@example.com"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50"
                    :class="{ 'border-red-400 bg-red-50/30': errors.email }" />
                  <p v-if="errors.email" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.email }}
                  </p>
                </div>

                <!-- Contact Number — plain, no flag/prefix -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Contact Number <span class="text-slate-400 font-normal text-xs">(Optional)</span>
                  </label>
                  <div class="relative">
                    <input
                      v-model="form.contact"
                      @input="handleContactInput"
                      @blur="validateContactNo"
                      type="tel"
                      :disabled="formLoading"
                      placeholder="09XX-XXX-XXXX"
                      maxlength="13"
                      class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50"
                      :class="{
                        'border-red-400 bg-red-50/30': errors.contact,
                        'border-emerald-400': contactValid && form.contact
                      }" />
                    <div v-if="contactValid && form.contact" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                      </svg>
                    </div>
                  </div>
                  <p v-if="!errors.contact" class="text-xs text-slate-400 mt-1">Format: 09XX-XXX-XXXX</p>
                  <p v-if="errors.contact" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.contact }}
                  </p>
                </div>

                <!-- Address — full width -->
                <div class="sm:col-span-2">
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Complete Address <span class="text-slate-400 font-normal text-xs">(Optional)</span>
                  </label>
                  <input v-model="form.address" @input="clearFieldError('address')" type="text" :disabled="formLoading"
                    placeholder="House No., Street, Barangay, City, Province"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50"
                    :class="{ 'border-red-400 bg-red-50/30': errors.address }" />
                  <p v-if="errors.address" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.address }}
                  </p>
                </div>

              </div>
            </div>

            <!-- ── Account Settings ── -->
            <div>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-1 h-4 rounded-full bg-[#1a4972]"></div>
                <p class="text-xs font-bold uppercase tracking-widest text-[#1a4972]">Account Settings</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                <!-- Role -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                  <select v-model="form.role" @change="clearFieldError('role')" :disabled="formLoading"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 text-slate-600 cursor-pointer disabled:opacity-50"
                    :class="{ 'border-red-400 bg-red-50/30': errors.role }">
                    <option value="" disabled>Select role</option>
                    <option v-for="role in availableRoles" :key="role.id" :value="role.name">{{ role.name }}</option>
                  </select>
                  <p v-if="errors.role" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.role }}
                  </p>
                </div>

                <!-- Status -->
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                  <div class="flex items-center gap-4 h-[42px]">
                    <label v-for="status in ['Active', 'Inactive']" :key="status"
                      class="flex items-center gap-2 cursor-pointer group hover:scale-105 transition-all">
                      <div class="relative">
                        <input type="radio" v-model="form.status" :value="status" :disabled="formLoading" class="sr-only"/>
                        <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                          :class="form.status === status
                            ? 'border-[#1a4972] bg-[#1a4972] shadow-[0_0_0_3px_rgba(26,73,114,0.2)]'
                            : 'border-slate-300'">
                          <div v-if="form.status === status" class="w-2 h-2 bg-white rounded-full"></div>
                        </div>
                      </div>
                      <span class="text-sm font-medium text-slate-700 group-hover:text-[#1a4972] transition-colors">{{ status }}</span>
                    </label>
                  </div>
                  <p v-if="errors.status" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.status }}
                  </p>
                </div>

                <!-- Password — full width -->
                <div class="sm:col-span-2">
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                  <div class="flex flex-col sm:flex-row gap-2">
                    <div class="relative flex-1">
                      <input v-model="form.password" @input="clearFieldError('password')"
                        :type="showPassword ? 'text' : 'password'"
                        :placeholder="isEditing && !resetPassword ? '•••••••• (unchanged)' : 'Enter new password'"
                        class="w-full px-4 py-2.5 pr-10 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50"
                        :class="{ 'border-red-400 bg-red-50/30': errors.password }"
                        :disabled="formLoading || (isEditing && !resetPassword)" />
                      <button v-if="!isEditing || resetPassword" type="button" @click="showPassword = !showPassword" :disabled="formLoading"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-all hover:scale-110 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path :d="showPassword
                            ? 'M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112'
                            : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'"/>
                        </svg>
                      </button>
                    </div>
                    <button v-if="isEditing" @click="toggleResetPassword" type="button" :disabled="formLoading"
                      class="flex items-center justify-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-xl transition-all hover:scale-105 active:scale-95 disabled:opacity-50 whitespace-nowrap"
                      :class="resetPassword ? 'bg-slate-100 text-slate-700 border border-slate-200' : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100'">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                      </svg>
                      {{ resetPassword ? 'Cancel Reset' : 'Reset Password' }}
                    </button>
                  </div>
                  <p v-if="errors.password" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    {{ errors.password }}
                  </p>
                </div>

              </div>
            </div>

          </div><!-- end modal body -->

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 px-5 sm:px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex-shrink-0">
            <button @click="closeModal" :disabled="formLoading"
              class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 active:scale-95 transition-all hover:shadow-md disabled:opacity-50">
              Cancel
            </button>
            <button @click="submitForm" :disabled="formLoading"
              class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl active:scale-95 transition-all hover:shadow-lg hover:scale-105 disabled:opacity-50 disabled:hover:scale-100 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30 min-w-[120px] flex items-center justify-center gap-2">
              <svg v-if="formLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              {{ formLoading ? (isEditing ? 'Saving...' : 'Adding...') : (isEditing ? 'Save Changes' : 'Add User') }}
            </button>
          </div>

        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onUnmounted, watch } from 'vue';
import { debounce } from 'lodash';
import userService from '@/services/userServices';
import Swal from 'sweetalert2';

import {
  getUsers,
  addUser,
  updateUserInStore,
  removeUserFromStore,
  listenForUpdates
} from '@/utils/appUtils';

// ==================== COLUMNS ====================
const columns = [
  { label: 'Name',       field: 'name',       sortable: true  },
  { label: 'Role',       field: 'role',       sortable: true  },
  { label: 'Status',     field: 'status',     sortable: true  },
  { label: 'Created At', field: 'created_at', sortable: true  },
  { label: 'Last Login', field: 'last_login', sortable: true  },
  { label: 'Actions',    field: 'actions',    sortable: false },
];

// ==================== STATE ====================
const initialUsers = getUsers();

const users = ref(initialUsers || []);
const isLoading = ref(false);
const isRefreshing = ref(false);
const initialLoadDone = ref(false);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: users.value.length,
  from: 1,
  to: users.value.length
});

const availableRoles = ref([]);
const searchQuery = ref('');
const roleFilter = ref('');
const sortField = ref('created_at');
const sortDirection = ref('desc');
const currentPage = ref(1);
const itemsPerPage = ref(10);

const isAdding = ref(false);
const isEditingUser = ref(null);
const isDeletingUser = ref(null);
const formLoading = ref(false);

const showModal = ref(false);
const isEditing = ref(false);
const showPassword = ref(false);
const editingUserId = ref(null);
const resetPassword = ref(false);

const contactValid = ref(false);

const form = reactive({
  firstName: '', lastName: '',
  address: '', contact: '',
  email: '', role: '', password: '', status: 'Active',
});

const errors = reactive({
  firstName: '', lastName: '',
  address: '', contact: '',
  email: '', role: '', password: '', status: ''
});

// ==================== COMPUTED ====================
const displayedPages = computed(() => {
  const pages = [];
  const max = 5;
  const total = pagination.value.last_page || 1;
  const current = pagination.value.current_page || 1;
  if (total <= max) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    let s = Math.max(1, current - 2);
    let e = Math.min(total, s + max - 1);
    if (e - s + 1 < max) s = Math.max(1, e - max + 1);
    for (let i = s; i <= e; i++) pages.push(i);
  }
  return pages;
});

const filteredUsers = computed(() => {
  let filtered = users.value;
  if (roleFilter.value) filtered = filtered.filter(u => u.role === roleFilter.value);
  if (searchQuery.value) {
    const search = searchQuery.value.toLowerCase();
    filtered = filtered.filter(u =>
      u.name?.toLowerCase().includes(search) ||
      u.email?.toLowerCase().includes(search)
    );
  }
  filtered = [...filtered].sort((a, b) => {
    let aVal = a[sortField.value];
    let bVal = b[sortField.value];
    if (sortField.value === 'created_at' || sortField.value === 'last_login') {
      aVal = aVal ? new Date(aVal) : 0;
      bVal = bVal ? new Date(bVal) : 0;
    }
    if (aVal < bVal) return sortDirection.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortDirection.value === 'asc' ? 1 : -1;
    return 0;
  });
  return filtered;
});

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredUsers.value.slice(start, start + itemsPerPage.value);
});

watch(filteredUsers, (newVal) => {
  pagination.value.total = newVal.length;
  pagination.value.last_page = Math.ceil(newVal.length / itemsPerPage.value);
  pagination.value.from = (currentPage.value - 1) * itemsPerPage.value + 1;
  pagination.value.to = Math.min(currentPage.value * itemsPerPage.value, newVal.length);
  if (currentPage.value > pagination.value.last_page) currentPage.value = 1;
}, { immediate: true });

// ==================== FETCH ROLES ====================
const fetchRoles = async () => {
  try {
    const response = await userService.getRoles();
    availableRoles.value = response.data || [];
  } catch (error) {
    console.error('Failed to fetch roles:', error);
    availableRoles.value = [
      { id: 1, name: 'Lawyer' },
      { id: 2, name: 'Clerk' }
    ];
  }
};

// ==================== LOAD USERS ====================
const loadUsers = async () => {
  isLoading.value = true;
  isRefreshing.value = true;
  try {
    const response = await userService.getUsers({ per_page: 100 });
    if (response.data && response.data.data) users.value = response.data.data;
  } catch (error) {
    console.error('Failed to load users:', error);
    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load users. Please try again.', timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
  } finally {
    isLoading.value = false;
    isRefreshing.value = false;
    initialLoadDone.value = true;
  }
};

// ==================== INITIALIZE ====================
const initialize = async () => {
  await fetchRoles();
  if (users.value.length === 0 && !initialLoadDone.value) {
    await loadUsers();
    manualRefresh();
  } else {
    isLoading.value = false;
  }
};

// ==================== MANUAL REFRESH ====================
const manualRefresh = async () => {
  isRefreshing.value = true;
  await loadUsers();
};

// ==================== LISTEN FOR UPDATES ====================
const handleUsersUpdated = (event) => { users.value = event.detail; };
let cleanup = null;

onMounted(async () => {
  await initialize();
  cleanup = listenForUpdates('users-updated', handleUsersUpdated);
});

onUnmounted(() => {
  if (cleanup) cleanup();
  debouncedSearch.cancel();
});

// ==================== FILTER HANDLERS ====================
const debouncedSearch = debounce(() => { currentPage.value = 1; }, 500);
const handleFilterChange = () => { currentPage.value = 1; };
const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
};
const previousPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < pagination.value.last_page) currentPage.value++; };
const goToPage = (page) => { currentPage.value = page; };

// ==================== UTILITIES ====================
const formatDate = (d) => d
  ? new Date(d).toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true })
  : 'N/A';

const formatLastLogin = (d) => d
  ? new Date(d).toLocaleString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true })
  : 'Never';

// ==================== CONTACT NUMBER VALIDATION ====================
const PH_PHONE_REGEX = /^09\d{2}-\d{3}-\d{4}$|^09\d{9}$/;

const validateContactNo = () => {
  if (!form.contact) {
    errors.contact = '';
    contactValid.value = false;
    return true;
  }
  if (!PH_PHONE_REGEX.test(form.contact)) {
    errors.contact = 'Enter a valid PH number (e.g. 09XX-XXX-XXXX)';
    contactValid.value = false;
    return false;
  }
  errors.contact = '';
  contactValid.value = true;
  return true;
};

const handleContactInput = () => {
  clearFieldError('contact');
  contactValid.value = false;
  let digits = form.contact.replace(/\D/g, '');
  if (digits.length > 11) digits = digits.slice(0, 11);
  if (digits.length <= 4) {
    form.contact = digits;
  } else if (digits.length <= 7) {
    form.contact = `${digits.slice(0, 4)}-${digits.slice(4)}`;
  } else {
    form.contact = `${digits.slice(0, 4)}-${digits.slice(4, 7)}-${digits.slice(7)}`;
  }
  if (digits.length === 11) validateContactNo();
};

// ==================== MODAL ====================
const resetForm = () => {
  Object.assign(form, { firstName: '', lastName: '', address: '', contact: '', email: '', role: '', password: '', status: 'Active' });
  Object.keys(errors).forEach(k => errors[k] = '');
  editingUserId.value = null;
  resetPassword.value = false;
  showPassword.value = false;
  contactValid.value = false;
};

const clearErrors = () => { Object.keys(errors).forEach(k => errors[k] = ''); };
const clearFieldError = (field) => { if (errors[field]) errors[field] = ''; };

const openAddUserModal = () => {
  resetForm();
  isEditing.value = false;
  form.password = 'temporary123';
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; resetForm(); };

// ==================== FIXED: EDIT USER ====================
const editUser = (user) => {
  console.log('Editing user:', user);
  resetForm();
  isEditing.value = true;
  editingUserId.value = user.id;

  const nameParts = user.name?.split(' ') || [];
  form.firstName = nameParts[0] || '';
  form.lastName = nameParts.length > 2 ? nameParts[nameParts.length - 1] : (nameParts.slice(1).join(' ') || '');

  form.email = user.email || '';
  form.role = user.role || '';
  form.status = user.status || 'Active';
  form.address = user.address || '';
  form.contact = user.contact_no || user.contact || user.contact_number || '';
  form.password = '';

  if (form.contact) validateContactNo();

  showModal.value = true;
};

const toggleResetPassword = () => {
  resetPassword.value = !resetPassword.value;
  form.password = resetPassword.value ? 'temppass1' : '';
  if (!resetPassword.value) errors.password = '';
};

// ==================== FIXED: SUBMIT FORM ====================
const submitForm = async () => {
  if (!validateContactNo()) return;

  formLoading.value = true;
  clearErrors();

  const payload = {
    firstName: form.firstName,
    lastName: form.lastName,
    email: form.email,
    role: form.role,
    status: form.status,
    address: form.address || null,
    contact_no: form.contact?.replace(/\D/g, '') || null,
  };

  if (form.password) payload.password = form.password;

  try {
    if (isEditing.value) {
      await userService.updateUser(editingUserId.value, payload);
      await loadUsers();
      Swal.fire({ icon: 'success', title: 'Success!', text: 'User updated successfully', timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
    } else {
      await userService.createUser(payload);
      await loadUsers();
      Swal.fire({ icon: 'success', title: 'Success!', text: 'User created successfully', timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
    }
    closeModal();
  } catch (error) {
    console.error('Form submission error:', error);
    if (error.errors) {
      const fieldMapping = {
        'firstName': 'firstName', 'lastName': 'lastName',
        'email': 'email', 'role': 'role', 'password': 'password',
        'address': 'address', 'contact_no': 'contact', 'contact': 'contact', 'status': 'status'
      };
      Object.keys(error.errors).forEach(key => {
        const fieldName = fieldMapping[key] || key;
        if (fieldName in errors) errors[fieldName] = error.errors[key];
      });
      Swal.fire({ icon: 'error', title: 'Validation Error', text: 'Please check the form for errors', timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
    } else {
      Swal.fire({ icon: 'error', title: 'Error!', text: error.message || 'An error occurred. Please try again.', timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
    }
  } finally {
    formLoading.value = false;
    isAdding.value = false;
    isEditingUser.value = null;
  }
};

// ==================== DELETE USER ====================
const confirmDeleteUser = async (user) => {
  const result = await Swal.fire({
    title: 'Delete User?',
    text: `Are you sure you want to delete ${user.name}?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, delete',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    isDeletingUser.value = user.id;
    try {
      await userService.deleteUser(user.id);
      Swal.fire({ icon: 'success', title: 'Deleted!', text: 'User deleted successfully', timer: 1500, showConfirmButton: false, position: 'top-end', toast: true });
      const response = await userService.getUsers({ per_page: 100 });
      if (response.data && response.data.data) users.value = response.data.data;
    } catch (error) {
      let errorMessage = 'Failed to delete user';
      if (error.status === 403) errorMessage = 'You do not have permission to delete this user';
      else if (error.status === 404) errorMessage = 'User not found';
      else if (error.message) errorMessage = error.message;
      Swal.fire({ icon: 'error', title: 'Error!', text: errorMessage, timer: 2000, showConfirmButton: false, position: 'top-end', toast: true });
    } finally {
      isDeletingUser.value = null;
    }
  }
};
</script>

<style scoped>
/* Modal: slide up on mobile, scale in on desktop */
.modal-enter-active, .modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
@media (min-width: 640px) {
  .modal-enter-from, .modal-leave-to { transform: scale(0.95); }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(8px); }
  to   { opacity: 1; transform: translateY(0); }
}
</style>