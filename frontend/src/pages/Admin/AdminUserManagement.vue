<template>
  <div class="min-h-screen p-6 bg-slate-50 font-sans">
    <!-- Header Section -->
    <div class="mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">User Management</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage lawyers and clerks</p>
    </div>

    <!-- Search and Add Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <input v-model="searchQuery" @input="debouncedSearch" type="text"
            placeholder="Search by name or email..."
            class="w-full pl-10 pr-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] focus:bg-white transition-all" />
        </div>
        
        <!-- Role Filter -->
        <select v-model="roleFilter" @change="handleFilterChange"
          class="px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#1a4972] text-slate-600 cursor-pointer hover:bg-slate-100">
          <option value="">All Roles</option>
          <option v-for="role in availableRoles" :key="role.id" :value="role.name">
            {{ role.name }}
          </option>
        </select>

        <button @click="openAddUserModal" :disabled="isAdding"
          class="text-white px-5 py-2.5 rounded-xl text-sm font-semibold inline-flex items-center justify-center transition-all whitespace-nowrap hover:shadow-lg active:scale-95 disabled:opacity-50 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md">
          <svg v-if="!isAdding" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <svg v-else class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
          </svg>
          {{ isAdding ? 'Adding...' : 'Add New User' }}
        </button>
      </div>
    </div>

    <!-- Users Table - ALWAYS RENDERED, just updates data -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-slate-100 bg-[#1a4972]/5">
            <th v-for="col in columns" :key="col.field" scope="col"
              class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500"
              :class="col.sortable ? 'cursor-pointer hover:text-[#1a4972] select-none' : ''"
              @click="col.sortable ? sortBy(col.field) : null">
              <div class="flex items-center gap-1.5">
                {{ col.label }}
                <svg v-if="col.sortable && sortField === col.field" class="w-3.5 h-3.5 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path :d="sortDirection === 'desc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'" stroke-width="2.5"/>
                </svg>
                <svg v-else-if="col.sortable" class="w-3.5 h-3.5 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
              </div>
            </th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-50">
          <tr v-for="(user, index) in users" :key="user.id" 
            class="hover:bg-blue-50/30 transition-colors">
            <td class="px-5 py-4">
              <p class="text-sm font-semibold text-slate-800">{{ user.name || '—' }}</p>
              <p class="text-xs text-slate-400">{{ user.email || '—' }}</p>
            </td>
            <td class="px-5 py-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-lg inline-block"
                :class="{
                  'bg-[#1a4972]/10 text-[#1a4972]': user.role === 'Lawyer',
                  'bg-emerald-50 text-emerald-700': user.role === 'Clerk'
                }">
                {{ user.role || '—' }}
              </span>
            </td>
            <td class="px-5 py-4">
              <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full" 
                  :class="{
                    'bg-emerald-500': user.status === 'Active',
                    'bg-red-500': user.status !== 'Active'
                  }"></div>
                <span class="text-xs font-medium" :class="user.status === 'Active' ? 'text-emerald-700' : 'text-red-700'">
                  {{ user.status }}
                </span>
              </div>
            </td>
            <td class="px-5 py-4 text-sm text-slate-400">{{ formatDate(user.created_at) }}</td>
            <td class="px-5 py-4 text-sm text-slate-400">{{ formatLastLogin(user.last_login) }}</td>
            <td class="px-5 py-4">
              <div class="flex items-center gap-2">
                <button @click="editUser(user)" :disabled="isEditingUser === user.id"
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-[#1a4972] hover:bg-[#1a4972]/10 transition-all disabled:opacity-50">
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
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-600 text-sm font-semibold hover:bg-red-50 transition-all disabled:opacity-50">
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

          <!-- Empty state -->
          <tr v-if="users.length === 0">
            <td :colspan="columns.length" class="px-6 py-16 text-center">
              <div class="flex flex-col items-center">
                <div class="w-14 h-14 rounded-2xl bg-[#1a4972]/10 flex items-center justify-center mb-3">
                  <svg class="w-7 h-7 text-[#1a4972] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                </div>
                <p class="text-sm font-semibold text-slate-700 mb-1">No users found</p>
                <p class="text-xs text-slate-400">Click "Add New User" to create one</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.total > 0" class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 bg-slate-50/50">
        <p class="text-xs text-slate-500">
          Showing <span class="font-semibold">{{ pagination.from }}</span> to
          <span class="font-semibold">{{ pagination.to }}</span> of
          <span class="font-semibold">{{ pagination.total }}</span> users
        </p>
        <div class="flex items-center gap-1">
          <button @click="previousPage" :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === 1 ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'">
            ← Prev
          </button>
          <button v-for="page in displayedPages" :key="page" @click="goToPage(page)"
            class="w-7 h-7 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === page ? 'bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white' : 'text-slate-600 hover:bg-slate-200'">
            {{ page }}
          </button>
          <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all"
            :class="pagination.current_page === pagination.last_page ? 'text-slate-300 cursor-not-allowed' : 'text-slate-600 hover:bg-slate-200'">
            Next →
          </button>
        </div>
      </div>
    </div>

    <!-- ADD / EDIT MODAL (keep your existing modal - it's perfect) -->
    <Transition name="modal">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" @click.self="closeModal">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-[1000px] max-h-[90vh] flex flex-col overflow-hidden">

          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 flex-shrink-0">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-[#1a4972]/10">
                <svg class="w-6 h-6 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-bold text-slate-800">{{ isEditing ? 'Edit User' : 'Add New User' }}</h2>
                <p class="text-sm text-slate-500">{{ isEditing ? 'Update user information' : 'Fill in the details to create a new account' }}</p>
              </div>
            </div>
            <button @click="closeModal" :disabled="formLoading" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-all duration-200 hover:scale-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="px-8 py-6 space-y-6 overflow-y-auto">
            <!-- Name fields -->
            <div class="grid grid-cols-3 gap-4">
              <div v-for="(field, index) in ['firstName', 'middleName', 'lastName']" :key="field">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  {{ ['First Name', 'Middle Name', 'Last Name'][index] }}
                  <span v-if="field !== 'middleName'" class="text-red-500">*</span>
                  <span v-else class="text-slate-400 font-normal text-xs ml-1">(Optional)</span>
                </label>
                <input v-model="form[field]" type="text" :disabled="formLoading"
                  :placeholder="'Enter ' + ['first name','middle name','last name'][index]"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all duration-200 hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="{ 'border-red-400': errors[field] }" />
                <p v-if="errors[field]" class="text-sm text-red-500 mt-1">{{ errors[field] }}</p>
              </div>
            </div>

            <!-- Address + Contact (Preserved for future integration) -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  Complete Address <span class="text-slate-400 font-normal text-xs ml-1">(Optional)</span>
                </label>
                <input v-model="form.address" type="text" :disabled="formLoading" placeholder="Enter complete address"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all duration-200 hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50 disabled:cursor-not-allowed" />
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  Contact Number <span class="text-slate-400 font-normal text-xs ml-1">(Optional)</span>
                </label>
                <input v-model="form.contact" type="text" :disabled="formLoading" placeholder="09XX XXX XXXX"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all duration-200 hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50 disabled:cursor-not-allowed" />
              </div>
            </div>

            <!-- Email, Role, Password -->
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                <input v-model="form.email" type="email" :disabled="formLoading" placeholder="Enter email address"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all duration-200 hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="{ 'border-red-400': errors.email }" />
                <p v-if="errors.email" class="text-sm text-red-500 mt-1">{{ errors.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role <span class="text-red-500">*</span></label>
                <select v-model="form.role" :disabled="formLoading"
                  class="w-full px-4 py-3 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all duration-200 hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 text-slate-600 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="{ 'border-red-400': errors.role }">
                  <option value="" disabled>Select role</option>
                  <option v-for="role in availableRoles" :key="role.id" :value="role.name">
                    {{ role.name }}
                  </option>
                </select>
                <p v-if="errors.role" class="text-sm text-red-500 mt-1">{{ errors.role }}</p>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Password <span class="text-red-500">*</span></label>
                <div class="relative">
                  <input v-model="form.password"
                    :type="showPassword ? 'text' : 'password'"
                    :placeholder="isEditing && !resetPassword ? '•••••••• (unchanged)' : 'Enter new password'"
                    class="w-full px-4 py-3 pr-10 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none transition-all duration-200 hover:border-[#1a4972] focus:border-[#1a4972] focus:ring-2 focus:ring-[#1a4972]/10 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="{ 'border-red-400': errors.password }"
                    :disabled="formLoading || (isEditing && !resetPassword)" />
                  <button v-if="!isEditing || resetPassword" type="button" @click="showPassword = !showPassword" :disabled="formLoading"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-all duration-200 hover:scale-110 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path :d="showPassword
                        ? 'M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112'
                        : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'"/>
                    </svg>
                  </button>
                </div>
                <div class="flex gap-2 mt-2">
                  <button v-if="isEditing" @click="toggleResetPassword" type="button" :disabled="formLoading"
                    class="flex items-center gap-1 px-3 py-1 text-sm font-semibold rounded-lg transition-all duration-200 hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="resetPassword ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-700 hover:bg-amber-200'">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    {{ resetPassword ? 'Cancel Reset' : 'Reset Password' }}
                  </button>
                </div>
                <p v-if="errors.password" class="text-sm text-red-500 mt-1">{{ errors.password }}</p>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Status <span class="text-red-500">*</span></label>
              <div class="flex items-center gap-4">
                <label v-for="status in ['Active', 'Inactive']" :key="status" 
                  class="flex items-center gap-2 cursor-pointer group transition-all duration-200 hover:scale-105">
                  <div class="relative">
                    <input type="radio" v-model="form.status" :value="status" :disabled="formLoading" class="sr-only"/>
                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                      :class="form.status === status 
                        ? 'border-[#1a4972] bg-[#1a4972] shadow-[0_0_0_3px_rgba(26,73,114,0.2)]' 
                        : 'border-slate-300'">
                      <div v-if="form.status === status" class="w-2 h-2 bg-white rounded-full"></div>
                    </div>
                  </div>
                  <span class="text-sm text-slate-700 font-medium group-hover:text-[#1a4972] transition-colors duration-200">{{ status }}</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex-shrink-0">
            <button @click="closeModal" :disabled="formLoading"
              class="px-6 py-3 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 active:scale-95 transition-all duration-200 hover:shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
              Cancel
            </button>
            <button @click="submitForm" :disabled="formLoading"
              class="px-6 py-3 text-sm font-semibold text-white rounded-xl active:scale-95 transition-all duration-200 hover:shadow-lg hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] shadow-md shadow-[#1a4972]/30 min-w-[120px] flex items-center justify-center gap-2">
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
import { ref, computed, reactive, onMounted, onUnmounted, onActivated, watch } from 'vue';
import { debounce } from 'lodash';
import userService from '@/services/userServices';
import Swal from 'sweetalert2';

// ==================== COLUMNS ====================
const columns = [
  { label: 'Name', field: 'name', sortable: true },
  { label: 'Role', field: 'role', sortable: true },
  { label: 'Status', field: 'status', sortable: true },
  { label: 'Created At', field: 'created_at', sortable: true },
  { label: 'Last Login', field: 'last_login', sortable: true },
  { label: 'Actions', field: 'actions', sortable: false },
];

// ==================== STATE ====================
const users = ref([]);
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0
});

const availableRoles = ref([]);
const searchQuery = ref('');
const roleFilter = ref('');
const sortField = ref('created_at');
const sortDirection = ref('desc');
const currentPage = ref(1);
const itemsPerPage = ref(10);

// Loading states
const isAdding = ref(false);
const isEditingUser = ref(null);
const isDeletingUser = ref(null);
const formLoading = ref(false);
const isInitialLoad = ref(true);

const showModal = ref(false);
const isEditing = ref(false);
const showPassword = ref(false);
const editingUserId = ref(null);
const resetPassword = ref(false);
const isComponentActive = ref(true);

// Cache key for this view
const CACHE_KEY = 'user_management_cache';

// Load from cache immediately
const loadFromCache = () => {
  try {
    const cached = sessionStorage.getItem(CACHE_KEY);
    if (cached) {
      const { data, meta, timestamp } = JSON.parse(cached);
      // Use cache if less than 30 seconds old
      if (Date.now() - timestamp < 30000) {
        users.value = data;
        pagination.value = meta;
        console.log('📦 Loaded users from cache');
      }
    }
  } catch (e) {
    console.warn('Failed to load from cache:', e);
  }
};

// Save to cache
const saveToCache = () => {
  try {
    sessionStorage.setItem(CACHE_KEY, JSON.stringify({
      data: users.value,
      meta: pagination.value,
      timestamp: Date.now()
    }));
  } catch (e) {
    console.warn('Failed to save to cache:', e);
  }
};

// Load from cache immediately
loadFromCache();

const form = reactive({
  firstName: '', middleName: '', lastName: '',
  email: '', role: '', password: '', status: 'Active',
});

const errors = reactive({
  firstName: '', lastName: '',
  email: '', role: '', password: '',
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
const loadUsers = async (forceRefresh = false) => {
  if (!isComponentActive.value) return;
  
  try {
    const params = {
      search: searchQuery.value || undefined,
      role: roleFilter.value || undefined,
      sort_by: sortField.value,
      sort_direction: sortDirection.value,
      page: currentPage.value,
      per_page: itemsPerPage.value,
    };
    
    const response = await userService.getUsers(params);
    
    if (isComponentActive.value) {
      users.value = response.data || [];
      pagination.value = response.meta || {
        current_page: currentPage.value,
        last_page: 1,
        per_page: itemsPerPage.value,
        total: users.value.length,
        from: 1,
        to: users.value.length
      };
      
      // Save to cache after successful load
      saveToCache();
      isInitialLoad.value = false;
    }
    
  } catch (error) {
    console.error('Failed to load users:', error);
    if (isComponentActive.value) {
      users.value = [];
    }
  }
};

// ==================== FILTER / SORT / PAGINATE ====================
const debouncedSearch = debounce(() => { 
  currentPage.value = 1; 
  loadUsers(true); 
}, 500);

const handleFilterChange = () => { 
  currentPage.value = 1; 
  loadUsers(true); 
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortField.value = field;
    sortDirection.value = 'asc';
  }
  loadUsers(true);
};

const previousPage = () => { 
  if (currentPage.value > 1) { 
    currentPage.value--; 
    loadUsers(true); 
  } 
};

const nextPage = () => { 
  if (currentPage.value < pagination.value.last_page) { 
    currentPage.value++; 
    loadUsers(true); 
  } 
};

const goToPage = (page) => { 
  currentPage.value = page; 
  loadUsers(true); 
};

// ==================== UTILITIES ====================
const formatDate = (d) => d 
  ? new Date(d).toLocaleString('en-US', { 
      year: 'numeric', 
      month: '2-digit', 
      day: '2-digit', 
      hour: '2-digit', 
      minute: '2-digit', 
      hour12: true 
    }) 
  : 'N/A';

const formatLastLogin = (d) => d 
  ? new Date(d).toLocaleString('en-US', { 
      year: 'numeric', 
      month: '2-digit', 
      day: '2-digit', 
      hour: '2-digit', 
      minute: '2-digit', 
      hour12: true 
    }) 
  : 'Never';

// ==================== MODAL ====================
const resetForm = () => {
  Object.assign(form, { 
    firstName: '', middleName: '', lastName: '', 
    email: '', role: '', password: '', status: 'Active' 
  });
  Object.keys(errors).forEach(k => errors[k] = '');
  editingUserId.value = null; 
  resetPassword.value = false; 
  showPassword.value = false;
};

const openAddUserModal = () => {
  resetForm();
  isEditing.value = false;
  form.password = 'temporary123';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  resetForm();
};

// ==================== EDIT USER ====================
const editUser = (user) => {
  resetForm();
  isEditing.value = true;
  editingUserId.value = user.id;
  
  const nameParts = user.name?.split(' ') || [];
  form.firstName = nameParts[0] || '';
  form.lastName = nameParts.slice(1).join(' ') || '';
  
  form.email = user.email || '';
  form.role = user.role || '';
  form.status = user.status || 'Active';
  form.password = '';
  
  showModal.value = true;
};

const toggleResetPassword = () => {
  resetPassword.value = !resetPassword.value;
  form.password = resetPassword.value ? 'temppass1' : '';
  if (!resetPassword.value) errors.password = '';
};

// ==================== SUBMIT FORM ====================
const submitForm = async () => {
  formLoading.value = true;
  clearErrors();
  
  const fullName = [form.firstName, form.middleName, form.lastName]
    .filter(part => part?.trim())
    .join(' ')
    .trim();
  
  const payload = {
    firstName: form.firstName,
    middleName: form.middleName || null,
    lastName: form.lastName,
    email: form.email,
    role: form.role,
    status: form.status,
    password: form.password,
  };
  
  if (isEditing.value && !resetPassword.value) {
    delete payload.password;
  }
  
  try {
    if (isEditing.value) {
      // Optimistic update
      const index = users.value.findIndex(u => u.id === editingUserId.value);
      if (index !== -1) {
        users.value[index] = {
          ...users.value[index],
          name: fullName,
          email: form.email,
          role: form.role,
          status: form.status,
        };
        saveToCache();
      }
      
      // Background API call
      userService.updateUser(editingUserId.value, payload)
        .then(() => {
          Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: 'User updated successfully',
            timer: 1500,
            showConfirmButton: false,
            position: 'top-end',
            toast: true
          });
          loadUsers(true);
        })
        .catch(error => {
          loadUsers(true);
          handleSubmitError(error);
        });
      
    } else {
      const response = await userService.createUser(payload);
      
      if (response.data) {
        users.value.unshift({
          ...response.data,
          name: fullName
        });
        saveToCache();
      }
      
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'User created successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
      
      loadUsers(true);
    }
    
    closeModal();
    
  } catch (error) {
    handleSubmitError(error);
  } finally {
    formLoading.value = false;
    isAdding.value = false;
    isEditingUser.value = null;
  }
};

const clearErrors = () => {
  Object.keys(errors).forEach(k => errors[k] = '');
};

const handleSubmitError = (error) => {
  if (error.errors) {
    const fieldMap = {
      'firstName': 'firstName',
      'lastName': 'lastName',
      'email': 'email',
      'role': 'role',
      'password': 'password',
    };
    
    Object.keys(error.errors).forEach(key => {
      const field = fieldMap[key] || key;
      if (field in errors) {
        errors[field] = error.errors[key][0] || error.errors[key];
      }
    });
  }
  
  Swal.fire({
    icon: 'error',
    title: 'Error!',
    text: error.message || 'An error occurred',
    confirmButtonColor: '#dc2626',
    timer: 2000,
    showConfirmButton: false
  });
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
      // Optimistic delete
      users.value = users.value.filter(u => u.id !== user.id);
      saveToCache();
      
      await userService.deleteUser(user.id);
      
      await Swal.fire({
        icon: 'success',
        title: 'Deleted!',
        text: 'User deleted successfully',
        timer: 1500,
        showConfirmButton: false,
        position: 'top-end',
        toast: true
      });
      
      await loadUsers(true);
      
    } catch (error) {
      await loadUsers(true);
      await Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.message || 'Failed to delete user',
        confirmButtonColor: '#dc2626'
      });
    } finally {
      isDeletingUser.value = null;
    }
  }
};

// Handle page visibility refresh
const onVisibilityChange = () => {
  if (document.visibilityState === 'visible' && isComponentActive.value) {
    loadUsers(true);
  }
};

// ==================== LIFECYCLE ====================
onMounted(async () => {
  isComponentActive.value = true;
  
  await fetchRoles();
  
  // If we have cached data, show it immediately then refresh in background
  if (users.value.length === 0) {
    await loadUsers();
  } else {
    // Refresh in background
    setTimeout(() => {
      if (isComponentActive.value) {
        loadUsers(true);
      }
    }, 100);
  }
  
  // Listen for page visibility
  document.addEventListener('visibilitychange', onVisibilityChange);
});

// Refresh when navigating back to this page
onActivated(() => {
  if (isComponentActive.value) {
    loadUsers(true);
  }
});

watch(currentPage, () => {
  loadUsers(true);
});

onUnmounted(() => {
  isComponentActive.value = false;
  debouncedSearch.cancel();
  document.removeEventListener('visibilitychange', onVisibilityChange);
});
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { 
  transition: all 0.25s ease; 
}
.modal-enter-from, .modal-leave-to { 
  opacity: 0; 
  transform: scale(0.95);
}
</style>