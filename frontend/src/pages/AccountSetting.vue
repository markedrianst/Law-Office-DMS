<template>
  <div class="min-h-screen p-4 sm:p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-6 sm:mb-8">
      <div class="flex items-center gap-2 sm:gap-3 mb-1">
        <div class="w-1 h-6 sm:h-8 rounded-full bg-[#1a4972]"></div>
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#1a4972]">Account Settings</h1>
      </div>
      <p class="text-xs sm:text-sm ml-3 sm:ml-4 pl-2 sm:pl-3 text-slate-500">Manage your profile and security settings</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
      <!-- Left Column - Profile Card -->
          <!-- Left Column - Profile Card (Sticky on desktop, normal on mobile) -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-slate-200 overflow-hidden lg:sticky lg:top-6">
            <!-- Profile Header (No upload button) -->
            <div class="bg-[#1a4972] px-4 sm:px-6 py-6 sm:py-8 text-center">
              <div class="inline-block">
                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-white/20 border-4 border-white/60 mx-auto flex items-center justify-center">
                  <span class="text-2xl sm:text-3xl font-bold text-white">{{ userInitials }}</span>
                </div>
              </div>
              <h2 class="text-lg sm:text-xl font-bold text-white mt-3 sm:mt-4">{{ user?.full_name || 'User' }}</h2>
              <p class="text-white/80 text-xs sm:text-sm mt-1 capitalize">{{ userRole }}</p>
            </div>

            <!-- Profile Info -->
            <div class="p-4 sm:p-6 space-y-3 sm:space-y-4">
              <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-slate-400">Email</p>
                  <p class="text-sm font-medium text-slate-700 truncate">{{ user?.email || '—' }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-xs text-slate-400">Role</p>
                  <p class="text-sm font-medium text-slate-700 capitalize">{{ userRole }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-xs text-slate-400">Member Since</p>
                  <p class="text-sm font-medium text-slate-700">{{ formatDate(user?.created_at) }}</p>
                </div>
              </div>

              <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center text-purple-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="flex-1">
                  <p class="text-xs text-slate-400">Last Login</p>
                  <p class="text-sm font-medium text-slate-700">{{ formatDateTime(user?.last_login) }}</p>
                </div>
              </div>

              <div class="pt-3 sm:pt-4 border-t border-slate-200">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600">Account Status</span>
                  <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="user?.status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600'">
                    {{ user?.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>


      <!-- Right Column - Forms -->
      <div class="lg:col-span-2 space-y-4 sm:space-y-6">
        <!-- Profile Information Form -->
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
          <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Profile Information
            </h3>
          </div>
          
          <div class="p-4 sm:p-6">
            <form @submit.prevent="updateProfile" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">First Name <span class="text-red-500">*</span></label>
                  <input 
                    v-model="profileForm.first_name" 
                    type="text" 
                    placeholder="Enter first name"
                    @input="validateField('first_name')"
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200"
                    :class="{ 'border-red-400 focus:ring-red-200': profileErrors.first_name }"
                  />
                  <p v-if="profileErrors.first_name" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ profileErrors.first_name }}
                  </p>
                </div>

                <div>
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Last Name <span class="text-red-500">*</span></label>
                  <input 
                    v-model="profileForm.last_name" 
                    type="text" 
                    placeholder="Enter last name"
                    @input="validateField('last_name')"
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200"
                    :class="{ 'border-red-400 focus:ring-red-200': profileErrors.last_name }"
                  />
                  <p v-if="profileErrors.last_name" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ profileErrors.last_name }}
                  </p>
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Email Address <span class="text-red-500">*</span></label>
                  <input 
                    v-model="profileForm.email" 
                    type="email" 
                    placeholder="Enter email address"
                    @input="validateField('email')"
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200"
                    :class="{ 'border-red-400 focus:ring-red-200': profileErrors.email }"
                  />
                  <p v-if="profileErrors.email" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ profileErrors.email }}
                  </p>
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Address</label>
                  <input 
                    v-model="profileForm.address" 
                    type="text" 
                    placeholder="Enter address"
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200"
                  />
                </div>

                <div class="sm:col-span-2">
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Contact Number</label>
                  <input 
                    v-model="displayPhoneNumber" 
                    type="tel" 
                    placeholder="Enter Philippine phone number (e.g., 0909-565-4444)"
                    @input="handlePhoneInput"
                    maxlength="13"
                    class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200"
                    :class="{ 'border-red-400 focus:ring-red-200': profileErrors.contact_no }"
                  />
                  <p v-if="profileErrors.contact_no" class="text-xs text-red-500 mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ profileErrors.contact_no }}
                  </p>
                  <p class="text-xs text-slate-400 mt-1">Format: 09XX-XXX-XXXX or 639XX-XXX-XXXX</p>
                </div>
              </div>

              <div class="flex justify-end pt-4">
                <button 
                  type="submit"
                  :disabled="profileLoading"
                  class="px-5 sm:px-6 py-2 sm:py-2.5 bg-[#1a4972] hover:bg-[#0f2f4a] text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="profileLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                  {{ profileLoading ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white rounded-xl sm:rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
          <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 bg-slate-50">
            <h3 class="text-sm font-bold text-slate-700 flex items-center gap-2">
              <svg class="w-4 h-4 text-[#1a4972]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6-4h12a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"/>
              </svg>
              Change Password
            </h3>
          </div>
          
          <div class="p-4 sm:p-6">
            <form @submit.prevent="changePassword" class="space-y-4">
              <div class="grid grid-cols-1 gap-4">
                <div>
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Current Password <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.current_password" 
                      :type="showCurrentPassword ? 'text' : 'password'"
                      placeholder="Enter current password"
                      @input="passwordErrors.current_password = ''"
                      class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200 pr-10"
                      :class="{ 'border-red-400 focus:ring-red-200': passwordErrors.current_password }"
                    />
                    <button 
                      type="button"
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                      <svg v-if="!showCurrentPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314" />
                      </svg>
                    </button>
                  </div>
                  <p v-if="passwordErrors.current_password" class="text-xs text-red-500 mt-1">{{ passwordErrors.current_password }}</p>
                </div>

                <div>
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">New Password <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.new_password" 
                      :type="showNewPassword ? 'text' : 'password'"
                      placeholder="Enter new password (min. 8 characters)"
                      @input="validatePassword"
                      class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200 pr-10"
                      :class="{ 'border-red-400 focus:ring-red-200': passwordErrors.new_password }"
                    />
                    <button 
                      type="button"
                      @click="showNewPassword = !showNewPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                      <svg v-if="!showNewPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314" />
                      </svg>
                    </button>
                  </div>
                  <p v-if="passwordErrors.new_password" class="text-xs text-red-500 mt-1">{{ passwordErrors.new_password }}</p>
                </div>

                <div>
                  <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.new_password_confirmation" 
                      :type="showConfirmPassword ? 'text' : 'password'"
                      placeholder="Confirm new password"
                      @input="validatePasswordConfirmation"
                      class="w-full px-3 sm:px-4 py-2 sm:py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#1a4972]/20 focus:border-[#1a4972] transition-all duration-200 pr-10"
                      :class="{ 'border-red-400 focus:ring-red-200': passwordErrors.new_password_confirmation }"
                    />
                    <button 
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                    >
                      <svg v-if="!showConfirmPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                      <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314" />
                      </svg>
                    </button>
                  </div>
                  <p v-if="passwordErrors.new_password_confirmation" class="text-xs text-red-500 mt-1">{{ passwordErrors.new_password_confirmation }}</p>
                </div>
              </div>

              <div class="flex justify-end pt-4">
                <button 
                  type="submit"
                  :disabled="passwordLoading"
                  class="px-5 sm:px-6 py-2 sm:py-2.5 bg-[#1a4972] hover:bg-[#0f2f4a] text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all duration-200 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg v-if="passwordLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6-4h12a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"/>
                  </svg>
                  {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useAuth } from '@/composables/useAuth';
import accountService from '@/services/accountService';
import Swal from 'sweetalert2';
import { setUser } from '@/utils/appUtils';
const { user, refreshUser, userRole, userInitials } = useAuth();

// ========== PROFILE FORM ==========
const profileLoading = ref(false);
const profileForm = reactive({
  first_name: '',
  last_name: '',
  email: '',
  address: '',
  contact_no: '' // Stores the raw phone number (no formatting)
});

// Display phone number with formatting
const displayPhoneNumber = ref('');

const profileErrors = reactive({
  first_name: '',
  last_name: '',
  email: '',
  contact: ''
});

// ========== PASSWORD FORM ==========
const passwordLoading = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const passwordForm = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

const passwordErrors = reactive({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
});

// ========== PHONE NUMBER FORMATTING ==========
const formatPhoneNumber = (value) => {
  if (!value) return '';
  
  // Remove all non-digit characters
  let cleaned = value.replace(/\D/g, '');
  
  // Limit to 12 digits (Philippine numbers max)
  if (cleaned.length > 12) {
    cleaned = cleaned.slice(0, 12);
  }
  
  // Format based on length
  if (cleaned.length >= 4 && cleaned.length <= 7) {
    // Format: 09XX-XXX
    return cleaned.replace(/(\d{4})(\d{1,3})/, '$1-$2');
  } else if (cleaned.length >= 8 && cleaned.length <= 11) {
    // Format: 09XX-XXX-XXXX
    return cleaned.replace(/(\d{4})(\d{3})(\d{1,4})/, '$1-$2-$3');
  } else if (cleaned.length === 12) {
    // Format: 639XX-XXX-XXXX
    return cleaned.replace(/(\d{3})(\d{3})(\d{4})/, '$1-$2-$3');
  }
  
  return cleaned;
};

const handlePhoneInput = (event) => {
  let value = event.target.value;
  
  // Remove all non-digit characters for storage
  const rawValue = value.replace(/\D/g, '');
  profileForm.contact_no = rawValue;
  
  // Format for display
  displayPhoneNumber.value = formatPhoneNumber(rawValue);
  
  // Validate
  validatePhoneNumber();
};

const validatePhoneNumber = () => {
  const phone = profileForm.contact_no;
  
  if (phone) {
    // Check if starts with 09 or 639
    const isValid = /^(09\d{9})$|^(639\d{9})$/.test(phone);
    
    if (!isValid) {
      profileErrors.contact_no = 'Please enter a valid Philippine phone number (e.g., 09123456789 or 639123456789)';
    } else if (phone.length !== 11 && phone.length !== 12) {
      profileErrors.contact_no = 'Phone number must be 11 or 12 digits';
    } else {
      profileErrors.contact_no = '';
    }
  } else {
    profileErrors.contact_no = '';
  }
};

// ========== VALIDATION FUNCTIONS ==========
const validateField = (field) => {
  switch(field) {
    case 'first_name':
      if (!profileForm.first_name.trim()) {
        profileErrors.first_name = 'First name is required';
      } else if (profileForm.first_name.length < 2) {
        profileErrors.first_name = 'First name must be at least 2 characters';
      } else if (!/^[a-zA-Z\s]+$/.test(profileForm.first_name)) {
        profileErrors.first_name = 'First name can only contain letters';
      } else {
        profileErrors.first_name = '';
      }
      break;
      
    case 'last_name':
      if (!profileForm.last_name.trim()) {
        profileErrors.last_name = 'Last name is required';
      } else if (profileForm.last_name.length < 2) {
        profileErrors.last_name = 'Last name must be at least 2 characters';
      } else if (!/^[a-zA-Z\s]+$/.test(profileForm.last_name)) {
        profileErrors.last_name = 'Last name can only contain letters';
      } else {
        profileErrors.last_name = '';
      }
      break;
      
    case 'email':
      const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
      if (!profileForm.email.trim()) {
        profileErrors.email = 'Email is required';
      } else if (!emailRegex.test(profileForm.email)) {
        profileErrors.email = 'Please enter a valid email address';
      } else {
        profileErrors.email = '';
      }
      break;
  }
};

const validatePassword = () => {
  if (passwordForm.new_password && passwordForm.new_password.length < 8) {
    passwordErrors.new_password = 'Password must be at least 8 characters';
  } else {
    passwordErrors.new_password = '';
  }
  
  if (passwordForm.new_password_confirmation) {
    validatePasswordConfirmation();
  }
};

const validatePasswordConfirmation = () => {
  if (passwordForm.new_password_confirmation && passwordForm.new_password !== passwordForm.new_password_confirmation) {
    passwordErrors.new_password_confirmation = 'Passwords do not match';
  } else {
    passwordErrors.new_password_confirmation = '';
  }
};

// ========== GET PROFILE ==========
const getProfile = async () => {
  profileLoading.value = true;
  try {
    const response = await accountService.getProfile();
    if (response.success) {
      const userData = response.data;
      const nameParts = userData.full_name?.split(' ') || [];
      profileForm.first_name = nameParts[0] || '';
      profileForm.last_name = nameParts.slice(1).join(' ') || '';
      profileForm.email = userData.email || '';
      profileForm.address = userData.address || '';
      profileForm.contact_no = userData.contact_number || '';
      
      // Format phone for display
      displayPhoneNumber.value = formatPhoneNumber(profileForm.contact_no);
      
      // Validate initial data
      validateField('first_name');
      validateField('last_name');
      validateField('email');
      if (profileForm.contact) validatePhoneNumber();
    }
  } catch (error) {
    console.error('Error fetching profile:', error);
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: 'Failed to load profile information',
      confirmButtonColor: '#1a4972'
    });
  } finally {
    profileLoading.value = false;
  }
};

// ========== INITIALIZE ==========
const initForm = () => {
  getProfile();
};

onMounted(() => {
  initForm();
});

// ========== FORMAT HELPERS ==========
const formatDate = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const formatDateTime = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// ========== UPDATE PROFILE ==========
const updateProfile = async () => {
  // Run all validations before submit
  validateField('first_name');
  validateField('last_name');
  validateField('email');
  if (profileForm.contact_no) validatePhoneNumber();
  
  // Check if there are any validation errors
  if (profileErrors.first_name || profileErrors.last_name || profileErrors.email || profileErrors.contact_no) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please fix the errors in the form before submitting',
      confirmButtonColor: '#dc2626'
    });
    return;
  }
  
  profileLoading.value = true;
  
  try {
    const payload = {
      firstName: profileForm.first_name,
      lastName: profileForm.last_name,
      email: profileForm.email,
      address: profileForm.address || null,
      contact_no: profileForm.contact_no || null
    };
    
    await accountService.updateProfile(payload);
    setUser({
      ...user.value,
      full_name: `${profileForm.first_name} ${profileForm.last_name}`,
      email: profileForm.email,
      address: profileForm.address || null,
      contact_number: profileForm.contact_no || null
    });
    // Refresh user data
    await refreshUser();
    
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Profile updated successfully',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    // Display server-side errors
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      if (errors.firstName) profileErrors.first_name = errors.firstName[0];
      if (errors.lastName) profileErrors.last_name = errors.lastName[0];
      if (errors.email) profileErrors.email = errors.email[0];
      if (errors.contact_no) profileErrors.contact_no = errors.contact_no[0];
      
      // Show error message
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'Failed to update profile',
        confirmButtonColor: '#dc2626'
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: 'Failed to update profile',
        confirmButtonColor: '#dc2626'
      });
    }
  } finally {
    profileLoading.value = false;
  }
};

// ========== CHANGE PASSWORD ==========
const changePassword = async () => {
  // Clear previous errors
  passwordErrors.current_password = '';
  passwordErrors.new_password = '';
  passwordErrors.new_password_confirmation = '';
  
  // Validate current password is not empty
  if (!passwordForm.current_password) {
    passwordErrors.current_password = 'Current password is required';
  }
  
  // Validate new password
  if (!passwordForm.new_password) {
    passwordErrors.new_password = 'New password is required';
  } else if (passwordForm.new_password.length < 8) {
    passwordErrors.new_password = 'Password must be at least 8 characters';
  }
  
  // Validate confirmation
  if (!passwordForm.new_password_confirmation) {
    passwordErrors.new_password_confirmation = 'Please confirm your password';
  } else if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    passwordErrors.new_password_confirmation = 'Passwords do not match';
  }
  
  // Check if there are any errors
  if (passwordErrors.current_password || passwordErrors.new_password || passwordErrors.new_password_confirmation) {
    Swal.fire({
      icon: 'error',
      title: 'Validation Error',
      text: 'Please fix the errors in the password form',
      confirmButtonColor: '#dc2626'
    });
    return;
  }
  
  passwordLoading.value = true;
  
  try {
    const payload = {
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation
    };
    
    await accountService.changePassword(payload);
    
    // Clear form
    passwordForm.current_password = '';
    passwordForm.new_password = '';
    passwordForm.new_password_confirmation = '';
    
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: 'Password changed successfully',
      timer: 2000,
      showConfirmButton: false
    });
    
  } catch (error) {
    // Display server-side errors
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      if (errors.current_password) passwordErrors.current_password = errors.current_password[0];
      if (errors.new_password) passwordErrors.new_password = errors.new_password[0];
      if (errors.new_password_confirmation) passwordErrors.new_password_confirmation = errors.new_password_confirmation[0];
    }
    
    // Show error message from server
    Swal.fire({
      icon: 'error',
      title: 'Error!',
      text: error.response?.data?.message || 'Failed to change password',
      confirmButtonColor: '#dc2626'
    });
  } finally {
    passwordLoading.value = false;
  }
};
</script>