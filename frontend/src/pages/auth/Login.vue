<!-- src/pages/auth/Login.vue -->

<template>
  <div
    class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat p-4"
    :style="{ backgroundImage: 'url(' + backgroundImage + ')' }"
  >
    <div class="w-full max-w-md">
      <div class="relative">
        <div class="absolute inset-0 bg-black/20 rounded-2xl blur-xl transform translate-y-2"></div>

        <div
          class="relative backdrop-blur-md rounded-2xl shadow-2xl p-8 border"
          :style="{
            backgroundColor: 'rgba(255, 255, 255, 0.15)',
            borderColor: 'rgba(255, 255, 255, 0.3)'
          }"
        >
          <div class="absolute inset-0 rounded-2xl pointer-events-none"
               :style="{ background: 'radial-gradient(circle at 50% 0%, rgba(255,255,255,0.3), transparent 70%)' }">
          </div>

          <div class="relative z-10">
            <!-- Logo -->
            <div class="flex justify-center mb-4">
              <div class="relative">
                <div class="absolute inset-0 bg-white/30 blur-xl rounded-full"></div>
                <img
                  src="../../assets/images/lawofficelogo.png"
                  alt="Law Office Logo"
                  class="relative w-24 h-24 object-contain drop-shadow-lg"
                  loading="eager"
                />
              </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-8">
              <h1 class="text-3xl font-bold mb-1 text-white tracking-wide">NICOLAS PINEDA</h1>
              <h1 class="text-3xl font-bold mb-1 text-white tracking-wide">LAW OFFICE</h1>
              <p class="text-sm tracking-wide text-white/80 font-light">Data Management System</p>
            </div>
            <form @submit.prevent="handleLogin" class="space-y-6" novalidate>
              <!-- Email Field -->        <div>
                <label for="email" class="block text-sm font-medium mb-1 text-white/90">
                  Email Address
                </label>
                <div class="relative">
                  <input
                    id="email"
                    v-model="email"
                    type="email"
                    name="email"
                    autocomplete="email"
                    class="w-full px-4 py-3 rounded-xl bg-white/15 border text-white placeholder-white/50 
                           focus:bg-white/25 focus:border-white/50 focus:outline-none transition-all
                           disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="[errors.email ? 'border-red-500' : 'border-white/30']"
                    :placeholder="'Enter your email'"
                    :disabled="loading"
                    @input="errors.email = ''"
                    @blur="validateEmail"
                  />
                  <!-- Email Icon -->
                  <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg
                      class="w-5 h-5 text-white/50"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                </div>
                <Transition name="fade">
                  <p v-if="errors.email" class="mt-1 text-sm text-red-300 font-medium">
                    {{ errors.email }}
                  </p>
                </Transition>
              </div>

              <!-- Password Field -->
              <div>
                <label for="password" class="block text-sm font-medium mb-1 text-white/90">
                  Password
                </label>
                <div class="relative">
                  <input
                    id="password"
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    autocomplete="current-password"
                    class="w-full px-4 py-3 rounded-xl bg-white/15 border text-white placeholder-white/50 
                           focus:bg-white/25 focus:border-white/50 focus:outline-none transition-all pr-10
                           disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="[errors.password ? 'border-red-500' : 'border-white/30']"
                    :placeholder="'Enter your password'"
                    :disabled="loading"
                    @input="errors.password = ''"
                  />
                  <!-- Toggle Password Visibility -->
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute inset-y-0 right-3 flex items-center text-white/70 hover:text-white focus:outline-none transition-colors"
                    :disabled="loading"
                    aria-label="Toggle password visibility"
                  >
                    <svg
                      v-if="!showPassword"
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                      />
                    </svg>
                    <svg
                      v-else
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"
                      />
                    </svg>
                  </button>
                </div>
                <Transition name="fade">
                  <p v-if="errors.password" class="mt-1 text-sm text-red-300 font-medium">
                    {{ errors.password }}
                  </p>
                </Transition>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="loading || !isFormValid"
                class="relative w-full py-3.5 rounded-xl font-semibold text-white transition-all 
                       overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed
                       hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                :style="{
                  background: 'linear-gradient(135deg, rgba(26, 73, 114, 0.95), rgba(15, 47, 74, 0.98))',
                  border: '1px solid rgba(255, 255, 255, 0.2)'
                }"
              >
                <span class="relative z-10 flex items-center justify-center gap-2">
                  <svg
                    v-if="loading"
                    class="animate-spin h-5 w-5"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <circle
                      class="opacity-25"
                      cx="12"
                      cy="12"
                      r="10"
                      stroke="currentColor"
                      stroke-width="4"
                    />
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                  </svg>
                  <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
                </span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <Transition name="modal">
      <div
        v-if="showResetModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click.self="closeResetModal"
      >
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="relative w-full max-w-md">
          <div class="relative">
            <div class="absolute inset-0 bg-black/20 rounded-2xl blur-xl transform translate-y-2"></div>

            <div
              class="relative backdrop-blur-md rounded-2xl shadow-2xl p-8 border"
              :style="{
                backgroundColor: 'rgba(255, 255, 255, 0.15)',
                borderColor: 'rgba(255, 255, 255, 0.3)'
              }"
            >
              <div
                class="absolute inset-0 rounded-2xl pointer-events-none"
                :style="{ background: 'radial-gradient(circle at 50% 0%, rgba(255,255,255,0.3), transparent 70%)' }"
              ></div>

              <div class="relative z-10">
                <div class="text-center mb-6">
                  <h2 class="text-2xl font-bold text-white">Change Password</h2>
                  <p class="text-sm mt-2 text-white/80">
                    You must change your password before continuing
                  </p>
                </div>

                <Transition name="fade-slide">
                  <div
                    v-if="showSuccessMessage"
                    class="mb-4 p-3 rounded-xl text-center bg-green-500/20 border border-green-500/50 text-white"
                  >
                    <div class="flex items-center justify-center gap-2">
                      <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                      <span>{{ successMessage }}</span>
                    </div>
                  </div>
                </Transition>

                <form @submit.prevent="handleResetPassword" class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium mb-1 text-white/90">Current Password</label>
                    <div class="relative">
                      <input
                        v-model="currentPassword"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        class="w-full px-4 py-3 rounded-xl bg-white/15 border text-white placeholder-white/50 
                               focus:bg-white/25 focus:border-white/50 focus:outline-none transition-all pr-10"
                        :class="[resetErrors.currentPassword ? 'border-red-500' : 'border-white/30']"
                        placeholder="Enter current password"
                        :disabled="resetLoading || showSuccessMessage"
                      />
                      <button
                        type="button"
                        @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute inset-y-0 right-3 flex items-center text-white/70 hover:text-white"
                        :disabled="resetLoading || showSuccessMessage"
                      >
                        <svg v-if="!showCurrentPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"/>
                        </svg>
                      </button>
                    </div>
                    <Transition name="fade">
                      <p v-if="resetErrors.currentPassword" class="mt-1 text-sm text-red-300">
                        {{ resetErrors.currentPassword }}
                      </p>
                    </Transition>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-white/90">New Password</label>
                    <div class="relative">
                      <input
                        v-model="newPassword"
                        :type="showNewPassword ? 'text' : 'password'"
                        class="w-full px-4 py-3 rounded-xl bg-white/15 border text-white placeholder-white/50 
                               focus:bg-white/25 focus:border-white/50 focus:outline-none transition-all pr-10"
                        :class="[resetErrors.newPassword ? 'border-red-500' : 'border-white/30']"
                        placeholder="Enter new password"
                        :disabled="resetLoading || showSuccessMessage"
                      />
                      <button
                        type="button"
                        @click="showNewPassword = !showNewPassword"
                        class="absolute inset-y-0 right-3 flex items-center text-white/70 hover:text-white"
                        :disabled="resetLoading || showSuccessMessage"
                      >
                        <svg v-if="!showNewPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"/>
                        </svg>
                      </button>
                    </div>
                    <Transition name="fade">
                      <p v-if="resetErrors.newPassword" class="mt-1 text-sm text-red-300">
                        {{ resetErrors.newPassword }}
                      </p>
                    </Transition>
                  </div>
                  <div>
                    <label class="block text-sm font-medium mb-1 text-white/90">Confirm Password</label>
                    <div class="relative">
                      <input
                        v-model="confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="w-full px-4 py-3 rounded-xl bg-white/15 border text-white placeholder-white/50 
                               focus:bg-white/25 focus:border-white/50 focus:outline-none transition-all pr-10"
                        :class="[resetErrors.confirmPassword ? 'border-red-500' : 'border-white/30']"
                        placeholder="Confirm new password"
                        :disabled="resetLoading || showSuccessMessage"
                      />
                      <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-3 flex items-center text-white/70 hover:text-white"
                        :disabled="resetLoading || showSuccessMessage"
                      >
                        <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"/>
                        </svg>
                      </button>
                    </div>
                    <Transition name="fade">
                      <p v-if="resetErrors.confirmPassword" class="mt-1 text-sm text-red-300">
                        {{ resetErrors.confirmPassword }}
                      </p>
                    </Transition>
                  </div>
                  <div class="flex justify-end gap-3 mt-6">
                    <button
                      type="button"
                      @click="closeResetModal"
                      :disabled="resetLoading || showSuccessMessage"
                      class="px-5 py-2.5 rounded-xl font-medium bg-white/15 border border-white/30 
                             text-white hover:bg-white/25 transition-all disabled:opacity-50"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="resetLoading || showSuccessMessage || !isResetFormValid"
                      class="px-5 py-2.5 rounded-xl font-medium bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] 
                             text-white border border-white/20 transition-all disabled:opacity-50
                             hover:shadow-lg hover:scale-[1.02] active:scale-[0.98]"
                    >
                      <span class="flex items-center justify-center gap-2">
                        <span
                          v-if="resetLoading"
                          class="animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4"
                        ></span>
                        {{ resetLoading ? 'Changing...' : 'Change Password' }}
                      </span>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import authService from "@/services/auth";
import { useAuth } from '@/composables/useAuth';
import backgroundImg from "@/assets/images/bg.jpg";
import api from "@/services/api"
import userService from "@/services/userServices";
import auditLogService from "@/services/auditLogService";
import approvalService from "@/services/approvalService";
import caseCategoryService from "@/services/caseCategoryService";
import caseService from "@/services/caseService";
import { 
  setUser,
  setDashboard
} from "@/utils/appUtils";

const router = useRouter();
const { refreshUser } = useAuth();
const backgroundImage = ref(backgroundImg);

const email = ref("");
const password = ref("");
const loading = ref(false);
const showPassword = ref(false);
const error = ref("");

const showResetModal = ref(false);
const resetEmail = ref("");
const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const resetLoading = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref("");

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

const errors = reactive({ 
  email: "", 
  password: "" 
});

const resetErrors = reactive({ 
  currentPassword: "", 
  newPassword: "", 
  confirmPassword: "" 
});

const isFormValid = computed(() => {
  return email.value && password.value && !errors.email && !errors.password;
});

const isResetFormValid = computed(() => {
  return (
    currentPassword.value &&
    newPassword.value &&
    confirmPassword.value &&
    newPassword.value === confirmPassword.value &&
    !resetErrors.currentPassword &&
    !resetErrors.newPassword &&
    !resetErrors.confirmPassword
  );
});

const validateEmail = () => {
  if (!email.value) {
    errors.email = "Email is required";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    errors.email = "Please enter a valid email address";
  } else {
    errors.email = "";
  }
};

const loadDashboardOnly = async () => {
  try {
    const response = await api.get('/dashboard');
    if (response.data) {
      setDashboard(response.data);
      return true;
    }
  } catch (error) {
    console.error('Dashboard load failed:', error);
    return false;
  }
};

const handleLogin = async () => {
  if (!email.value || !password.value) {
    if (!email.value) errors.email = "Email is required";
    if (!password.value) errors.password = "Password is required";
    
    Swal.fire({
      icon: "warning",
      title: "Invalid Inputs",
      text: "Please fill in all fields",
      showConfirmButton: false,
      timer: 1500,
    });
    return;
  }

  validateEmail();
  if (!isFormValid.value) return;

  loading.value = true;
  error.value = "";

  try {
    const response = await authService.login({ 
      email: email.value.trim(), 
      password: password.value 
    });

    if (response.requires_password_change) {
      resetEmail.value = response.user.email;
      showResetModal.value = true;
      Swal.close();
      return;
    }

    setUser(response.user);
    await refreshUser();

    await loadDashboardOnly();

    Swal.close();
      await Promise.all([
      userService.getUsers({ per_page: 100 }).catch(() => {}),
      auditLogService.getCombinedLogs({ per_page: 100 }).catch(() => {}),
      approvalService.getApprovals({ per_page: 100 }).catch(() => {}),
      caseCategoryService.getCategories({ per_page: 100 }).catch(() => {}),
      caseService.getCases({ per_page: 100 }).catch(() => {})
    ]);
    Swal.fire({
      icon: "success",
      title: "Welcome!",
      text: `Login successful`,
      timer: 1000,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
    
    router.replace("/dashboard");

  } catch (err) {
    Swal.close();
    handleLoginError(err);
    
    Swal.fire({
      icon: "error",
      title: "Login Failed",
      text: err.response?.data?.message || err.message || "Invalid credentials",
      timer: 1500,
      showConfirmButton: false,
      position: 'top-end',
      toast: true
    });
  } finally {
    loading.value = false;
  }
};

const handleLoginError = (err) => {
  if (err.response?.data?.errors) {
    const backendErrors = err.response.data.errors;
    if (backendErrors.email) errors.email = backendErrors.email[0];
    if (backendErrors.password) errors.password = backendErrors.password[0];
  } else if (err.response?.data?.message) {
    error.value = err.response.data.message;
  } else {
    error.value = "Unable to connect to server. Please try again.";
  }
};

const handleResetPassword = async () => {
  if (!currentPassword.value) {
    resetErrors.currentPassword = "Current password is required";
    return;
  }
  if (!newPassword.value) {
    resetErrors.newPassword = "New password is required";
    return;
  }
  if (!confirmPassword.value) {
    resetErrors.confirmPassword = "Please confirm your password";
    return;
  }
  if (newPassword.value !== confirmPassword.value) {
    resetErrors.confirmPassword = "Passwords do not match";
    return;
  }
  if (newPassword.value.length < 6) {
    resetErrors.newPassword = "Password must be at least 6 characters";
    return;
  }

  resetLoading.value = true;

  try {
    const response = await authService.changePassword({
      email: resetEmail.value,
      current_password: currentPassword.value,
      new_password: newPassword.value,
      new_password_confirmation: confirmPassword.value,
    });

    showSuccessMessage.value = true;
    successMessage.value = response.message || "Password updated successfully!";

    setTimeout(() => {
      closeResetModal();
      error.value = "Password updated. Please login with your new password.";
    }, 2000);

  } catch (err) {
    if (err.response?.data?.errors) {
      const be = err.response.data.errors;
      if (be.current_password) resetErrors.currentPassword = be.current_password[0];
      if (be.new_password) resetErrors.newPassword = be.new_password[0];
      if (be.new_password_confirmation) resetErrors.confirmPassword = be.new_password_confirmation[0];
    } else {
      resetErrors.newPassword = err.message || "Failed to change password";
    }
  } finally {
    resetLoading.value = false;
  }
};

const closeResetModal = () => {
  showResetModal.value = false;
  showSuccessMessage.value = false;
  currentPassword.value = "";
  newPassword.value = "";
  confirmPassword.value = "";
  resetErrors.currentPassword = "";
  resetErrors.newPassword = "";
  resetErrors.confirmPassword = "";
};
</script>
<style scoped>
.min-h-screen::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  pointer-events: none;
}

.w-full.max-w-md {
  position: relative;
  z-index: 10;
}

input::placeholder {
  color: rgba(255, 255, 255, 0.5);
  font-weight: 300;
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@media (max-width: 640px) {
  .backdrop-blur-md {
    backdrop-blur: 8px;
  }
  
  .p-8 {
    padding: 1.5rem;
  }
  
  h1.text-3xl {
    font-size: 1.5rem;
  }
}

input:focus-visible {
  outline: 2px solid white;
  outline-offset: 2px;
}

button:focus-visible {
  outline: 2px solid white;
  outline-offset: 2px;
}
</style>