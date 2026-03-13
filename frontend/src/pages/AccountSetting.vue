<template>
  <div class="min-h-screen p-6 bg-slate-50">
    <!-- Header -->
    <div class="mb-7">
      <div class="flex items-center gap-3 mb-1">
        <div class="w-1 h-8 rounded-full bg-gradient-to-b from-[#1a4972] to-[#2d6db5]"></div>
        <h1 class="text-2xl font-bold tracking-tight text-[#1a4972]">Account Settings</h1>
      </div>
      <p class="text-sm ml-4 pl-3 text-slate-500">Manage your profile and security settings</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Profile Card -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-6">
          <!-- Profile Header -->
          <div class="bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] px-6 py-8 text-center">
            <div class="relative inline-block">
              <div class="w-24 h-24 rounded-full bg-white/20 border-4 border-white/50 mx-auto flex items-center justify-center">
                <span class="text-3xl font-bold text-white">{{ userInitials }}</span>
              </div>
              <button class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-emerald-500 hover:bg-emerald-600 text-white flex items-center justify-center shadow-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </button>
            </div>
            <h2 class="text-xl font-bold text-white mt-4">{{ user?.full_name || 'User' }}</h2>
            <p class="text-white/80 text-sm mt-1 capitalize">{{ userRole }}</p>
          </div>

          <!-- Profile Info -->
          <div class="p-6 space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-xs text-slate-400">Email</p>
                <p class="text-sm font-medium text-slate-700">{{ user?.email || '—' }}</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
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

            <div class="flex items-center gap-3">
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

            <div class="flex items-center gap-3">
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

            <div class="pt-4 border-t border-slate-100">
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600">Account Status</span>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="user?.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'">
                  {{ user?.status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Forms -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Profile Information Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700">Profile Information</h3>
          </div>
          
          <div class="p-6">
            <form @submit.prevent="updateProfile" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">First Name</label>
                  <input 
                    v-model="profileForm.first_name" 
                    type="text" 
                    placeholder="Enter first name"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                    :class="{ 'border-red-400': profileErrors.first_name }"
                  />
                  <p v-if="profileErrors.first_name" class="text-xs text-red-500 mt-1">{{ profileErrors.first_name }}</p>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Last Name</label>
                  <input 
                    v-model="profileForm.last_name" 
                    type="text" 
                    placeholder="Enter last name"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                    :class="{ 'border-red-400': profileErrors.last_name }"
                  />
                  <p v-if="profileErrors.last_name" class="text-xs text-red-500 mt-1">{{ profileErrors.last_name }}</p>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address</label>
                  <input 
                    v-model="profileForm.email" 
                    type="email" 
                    placeholder="Enter email address"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                    :class="{ 'border-red-400': profileErrors.email }"
                  />
                  <p v-if="profileErrors.email" class="text-xs text-red-500 mt-1">{{ profileErrors.email }}</p>
                </div>

                <div class="md:col-span-2">
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Address</label>
                  <input 
                    v-model="profileForm.address" 
                    type="text" 
                    placeholder="Enter address"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Contact Number</label>
                  <input 
                    v-model="profileForm.contact" 
                    type="text" 
                    placeholder="Enter contact number"
                    class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all"
                  />
                </div>
              </div>

              <div class="flex justify-end pt-4">
                <button 
                  type="submit"
                  :disabled="profileLoading"
                  class="px-6 py-2.5 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
                >
                  <svg v-if="profileLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ profileLoading ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Change Password Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700">Change Password</h3>
          </div>
          
          <div class="p-6">
            <form @submit.prevent="changePassword" class="space-y-4">
              <div class="grid grid-cols-1 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.current_password" 
                      :type="showCurrentPassword ? 'text' : 'password'"
                      placeholder="Enter current password"
                      class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all pr-10"
                      :class="{ 'border-red-400': passwordErrors.current_password }"
                    />
                    <button 
                      type="button"
                      @click="showCurrentPassword = !showCurrentPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
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
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.new_password" 
                      :type="showNewPassword ? 'text' : 'password'"
                      placeholder="Enter new password"
                      class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all pr-10"
                      :class="{ 'border-red-400': passwordErrors.new_password }"
                    />
                    <button 
                      type="button"
                      @click="showNewPassword = !showNewPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
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
                  <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                  <div class="relative">
                    <input 
                      v-model="passwordForm.new_password_confirmation" 
                      :type="showConfirmPassword ? 'text' : 'password'"
                      placeholder="Confirm new password"
                      class="w-full px-4 py-2.5 text-sm border border-slate-200 rounded-xl bg-slate-50 focus:bg-white focus:outline-none focus:border-[#1a4972] transition-all pr-10"
                      :class="{ 'border-red-400': passwordErrors.new_password_confirmation }"
                    />
                    <button 
                      type="button"
                      @click="showConfirmPassword = !showConfirmPassword"
                      class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
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
                  class="px-6 py-2.5 bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white text-sm font-semibold rounded-xl hover:shadow-lg transition-all flex items-center gap-2 disabled:opacity-50"
                >
                  <svg v-if="passwordLoading" class="animate-spin w-4 h-4" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ passwordLoading ? 'Changing...' : 'Change Password' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Session Management -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-bold text-slate-700">Active Sessions</h3>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-semibold text-slate-800">Current Session</p>
                    <p class="text-xs text-slate-500">This device • {{ formatDateTime(new Date()) }}</p>
                  </div>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 rounded-full">
                  Active Now
                </span>
              </div>

              <button 
                @click="logoutAllDevices"
                class="w-full px-4 py-2.5 text-sm font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 rounded-xl transition-colors flex items-center justify-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout All Other Devices
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div 
      v-if="toast.show" 
      class="fixed bottom-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
      :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'"
    >
      <div class="w-5 h-5 rounded-full flex items-center justify-center" :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'">
        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path v-if="toast.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </div>
      {{ toast.message }}
    </div>
  </div>
</template>
<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useAuth } from '@/composables/useAuth';
import accountService from '@/services/accountService';
import Swal from 'sweetalert2';

const { user, refreshUser, userRole, userInitials } = useAuth();

// ========== TOAST STATE ==========
const toast = reactive({
  show: false,
  message: '',
  type: 'success'
});

// ========== PROFILE FORM ==========
const profileLoading = ref(false);
const profileForm = reactive({
  first_name: '',
  last_name: '',
  email: '',
  address: '',
  contact: ''
});

const profileErrors = reactive({
  first_name: '',
  last_name: '',
  email: ''
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

// ========== INITIALIZE FORM WITH USER DATA ==========
const initForm = () => {
  if (user.value) {
    const nameParts = user.value.full_name?.split(' ') || [];
    profileForm.first_name = nameParts[0] || '';
    profileForm.last_name = nameParts.slice(1).join(' ') || '';
    profileForm.email = user.value.email || '';
    profileForm.address = user.value.address || '';
    profileForm.contact = user.value.contact_number || '';
  }
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

// ========== TOAST HELPER ==========
const showToast = (message, type = 'success') => {
  toast.show = true;
  toast.message = message;
  toast.type = type;
  
  setTimeout(() => {
    toast.show = false;
  }, 3000);
};

// ========== UPDATE PROFILE ==========
const updateProfile = async () => {
  profileLoading.value = true;
  
  // Clear errors
  profileErrors.first_name = '';
  profileErrors.last_name = '';
  profileErrors.email = '';
  
  try {
    const payload = {
      firstName: profileForm.first_name,
      lastName: profileForm.last_name,
      email: profileForm.email,
      address: profileForm.address || null,
      contact: profileForm.contact || null
    };
    
    // ✅ FIXED: Using correct accountService method
    await accountService.updateProfile(payload);
    
    // Refresh user data
    await refreshUser();
    
    showToast('Profile updated successfully', 'success');
    
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      if (errors.firstName) profileErrors.first_name = errors.firstName[0];
      if (errors.lastName) profileErrors.last_name = errors.lastName[0];
      if (errors.email) profileErrors.email = errors.email[0];
    }
    showToast(error.response?.data?.message || 'Failed to update profile', 'error');
  } finally {
    profileLoading.value = false;
  }
};

// ========== CHANGE PASSWORD ==========
const changePassword = async () => {
  passwordLoading.value = true;
  
  // Clear errors
  passwordErrors.current_password = '';
  passwordErrors.new_password = '';
  passwordErrors.new_password_confirmation = '';
  
  // Validate form before submitting
  if (!passwordForm.current_password || !passwordForm.new_password || !passwordForm.new_password_confirmation) {
    if (!passwordForm.current_password) passwordErrors.current_password = 'Current password is required';
    if (!passwordForm.new_password) passwordErrors.new_password = 'New password is required';
    if (!passwordForm.new_password_confirmation) passwordErrors.new_password_confirmation = 'Password confirmation is required';
    passwordLoading.value = false;
    return;
  }
  
  // Check if new password matches confirmation
  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    passwordErrors.new_password_confirmation = 'Passwords do not match';
    passwordLoading.value = false;
    return;
  }
  
  try {
    const payload = {
      current_password: passwordForm.current_password,
      new_password: passwordForm.new_password,
      new_password_confirmation: passwordForm.new_password_confirmation
    };
    
    // ✅ FIXED: Using correct accountService method
    await accountService.changePassword(payload);
    
    // Clear form
    passwordForm.current_password = '';
    passwordForm.new_password = '';
    passwordForm.new_password_confirmation = '';
    
    showToast('Password changed successfully', 'success');
    
  } catch (error) {
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      if (errors.current_password) passwordErrors.current_password = errors.current_password[0];
      if (errors.new_password) passwordErrors.new_password = errors.new_password[0];
      if (errors.new_password_confirmation) passwordErrors.new_password_confirmation = errors.new_password_confirmation[0];
    }
    showToast(error.response?.data?.message || 'Failed to change password', 'error');
  } finally {
    passwordLoading.value = false;
  }
};

// ========== LOGOUT ALL DEVICES ==========
const logoutAllDevices = async () => {
  const result = await Swal.fire({
    title: 'Logout All Devices?',
    text: 'This will sign you out from all other devices. Your current session will remain active.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Yes, logout others',
    cancelButtonText: 'Cancel'
  });
  
  if (result.isConfirmed) {
    try {
      await accountService.logoutAllDevices();
      
      await Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'All other devices have been logged out',
        timer: 2000,
        showConfirmButton: false
      });
      
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: error.response?.data?.message || 'Failed to logout other devices',
        confirmButtonColor: '#dc2626'
      });
    }
  }
};
</script>