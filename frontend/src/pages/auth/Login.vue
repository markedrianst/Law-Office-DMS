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
                  alt="Logo"
                  class="relative w-24 h-24 object-contain drop-shadow-lg"
                  :style="{ filter: 'drop-shadow(0 4px 6px rgba(0, 0, 0, 0.2))' }"
                />
              </div>
            </div>

            <!-- Title -->
            <div class="text-center mb-8">
              <h1 class="text-3xl font-bold mb-1 text-white">NICOLAS PINEDA</h1>
              <h1 class="text-3xl font-bold mb-1 text-white">LAW OFFICE</h1>
              <p class="text-sm tracking-wide text-white/90">Data Management System</p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mb-4 p-3 rounded-xl text-center bg-red-500/20 border border-red-500/50 text-white">
              {{ error }}
            </div>

            <form @submit.prevent="handleLogin" class="space-y-6">
              <!-- Email -->
              <div>
                <label class="block text-sm font-medium mb-1 text-white/90">Email</label>
                <input
                  v-model="email"
                  type="email"
                  class="w-full px-4 py-3 rounded-xl bg-white/15 border border-white/30 text-white placeholder-white/50 focus:bg-white/25 focus:border-white/50 transition-all"
                  :class="{ 'border-red-500': errors.email }"
                  placeholder="Enter your email"
                  @input="errors.email = ''"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-300">{{ errors.email }}</p>
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm font-medium mb-1 text-white/90">Password</label>
                <div class="relative">
                  <input
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="w-full px-4 py-3 rounded-xl bg-white/15 border border-white/30 text-white placeholder-white/50 focus:bg-white/25 focus:border-white/50 transition-all pr-10"
                    :class="{ 'border-red-500': errors.password }"
                    placeholder="Enter your password"
                    @input="errors.password = ''"
                  />
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white/70">
                    <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"/>
                    </svg>
                  </button>
                </div>
                <p v-if="errors.password" class="mt-1 text-sm text-red-300">{{ errors.password }}</p>
              </div>

              <!-- Login Button -->
              <button
                type="submit"
                :disabled="loading"
                class="relative w-full py-3 rounded-xl font-medium transition-all overflow-hidden disabled:opacity-70"
                :style="{
                  background: 'linear-gradient(135deg, rgba(26, 73, 114, 0.9), rgba(15, 47, 74, 0.95))',
                  color: 'white',
                  border: '1px solid rgba(255, 255, 255, 0.2)'
                }"
              >
                <span class="relative z-10 flex items-center justify-center">
                  <svg v-if="loading" class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                  </svg>
                  {{ loading ? "Logging in..." : "Login" }}
                </span>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Password Change Modal -->
    <Transition name="modal">
      <div v-if="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
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
              <div class="absolute inset-0 rounded-2xl pointer-events-none"
                   :style="{ background: 'radial-gradient(circle at 50% 0%, rgba(255,255,255,0.3), transparent 70%)' }">
              </div>

              <div class="relative z-10">
                <div class="text-center mb-6">
                  <h2 class="text-2xl font-bold text-white">Change Password</h2>
                  <p class="text-sm mt-2 text-white/90">You must change your password before continuing.</p>
                </div>

                <form @submit.prevent="handleResetPassword">
                  <!-- Current Password -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-white/90">Current Password</label>
                    <div class="relative">
                      <input
                        v-model="currentPassword"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        placeholder="Enter current password"
                        class="w-full px-4 py-3 rounded-xl bg-white/15 border border-white/30 text-white placeholder-white/50 focus:bg-white/25 focus:border-white/50 transition-all"
                        :class="{ 'border-red-500': resetErrors.currentPassword }"
                      />
                      <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white/70">
                        <svg v-if="!showCurrentPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"/>
                        </svg>
                      </button>
                    </div>
                    <p v-if="resetErrors.currentPassword" class="mt-1 text-sm text-red-300">{{ resetErrors.currentPassword }}</p>
                  </div>

                  <!-- New Password -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium mb-1 text-white/90">New Password</label>
                    <div class="relative">
                      <input
                        v-model="newPassword"
                        :type="showNewPassword ? 'text' : 'password'"
                        placeholder="Enter new password"
                        class="w-full px-4 py-3 rounded-xl bg-white/15 border border-white/30 text-white placeholder-white/50 focus:bg-white/25 focus:border-white/50 transition-all"
                        :class="{ 'border-red-500': resetErrors.newPassword }"
                      />
                      <button type="button" @click="showNewPassword = !showNewPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white/70">
                        <svg v-if="!showNewPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"/>
                        </svg>
                      </button>
                    </div>
                    <p v-if="resetErrors.newPassword" class="mt-1 text-sm text-red-300">{{ resetErrors.newPassword }}</p>
                  </div>

                  <!-- Confirm Password -->
                  <div class="mb-6">
                    <label class="block text-sm font-medium mb-1 text-white/90">Confirm New Password</label>
                    <div class="relative">
                      <input
                        v-model="confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        placeholder="Confirm new password"
                        class="w-full px-4 py-3 rounded-xl bg-white/15 border border-white/30 text-white placeholder-white/50 focus:bg-white/25 focus:border-white/50 transition-all"
                        :class="{ 'border-red-500': resetErrors.confirmPassword }"
                      />
                      <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white/70">
                        <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314"/>
                        </svg>
                      </button>
                    </div>
                    <p v-if="resetErrors.confirmPassword" class="mt-1 text-sm text-red-300">{{ resetErrors.confirmPassword }}</p>
                  </div>

                  <!-- Success Message -->
                  <Transition name="fade">
                    <div v-if="showSuccessMessage" class="mb-4 p-3 rounded-xl text-center bg-green-500/20 border border-green-500/50 text-white">
                      <div class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>{{ successMessage }}</span>
                      </div>
                    </div>
                  </Transition>

                  <!-- Action Buttons -->
                  <div class="flex justify-end gap-3">
                    <button
                      type="button"
                      @click="closeResetModal"
                      :disabled="resetLoading || showSuccessMessage"
                      class="px-5 py-2.5 rounded-xl font-medium bg-white/15 border border-white/30 text-white hover:bg-white/25 transition-all disabled:opacity-50"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="resetLoading || showSuccessMessage"
                      class="px-5 py-2.5 rounded-xl font-medium bg-gradient-to-r from-[#1a4972] to-[#0f2f4a] text-white border border-white/20 transition-all disabled:opacity-50"
                    >
                      <span class="flex items-center justify-center gap-2">
                        <span v-if="resetLoading" class="animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4"></span>
                        {{ resetLoading ? "Changing..." : showSuccessMessage ? "Success!" : "Change Password" }}
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
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import authService from "@/services/auth";
import { useAuth } from '@/composables/Useauth';
import backgroundImg from "../../assets/images/bg.jpg";

const router = useRouter();
const { refreshUser } = useAuth();
const backgroundImage = ref(backgroundImg);

// Login form
const email = ref("");
const password = ref("");
const loading = ref(false);
const showPassword = ref(false);
const error = ref("");

// Modal state
const showResetModal = ref(false);
const resetEmail = ref("");
const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const resetLoading = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref("");

// Password visibility
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Errors
const errors = reactive({ email: "", password: "" });
const resetErrors = reactive({ 
  currentPassword: "", 
  newPassword: "", 
  confirmPassword: "" 
});

// Login handler - OPTIMIZED with preload
const handleLogin = async () => {
  // Quick validation
  if (!email.value || !password.value) {
    if (!email.value) errors.email = "Email is required";
    if (!password.value) errors.password = "Password is required";
    return;
  }

  loading.value = true;
  error.value = "";
  errors.email = "";
  errors.password = "";

  try {
    const response = await authService.login({ 
      email: email.value, 
      password: password.value 
    });

    if (response.requires_password_change) {
      resetEmail.value = response.user.email;
      showResetModal.value = true;
      loading.value = false;
      return;
    }

    // Refresh auth state
    await refreshUser();
    
    // Navigate to dashboard - it will show cached data INSTANTLY
    // because authService.preloadDashboard() is already running in background
    router.replace("/dashboard");
    
  } catch (err) {
    if (err.response?.data?.errors) {
      const be = err.response.data.errors;
      if (be.email) errors.email = be.email[0];
      if (be.password) errors.password = be.password[0];
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message;
    } else {
      error.value = "Invalid email or password";
    }
  } finally {
    loading.value = false;
  }
};

// Password change handler
const handleResetPassword = async () => {
  // Validate
  if (!currentPassword.value || !newPassword.value || !confirmPassword.value) {
    if (!currentPassword.value) resetErrors.currentPassword = "Current password is required";
    if (!newPassword.value) resetErrors.newPassword = "New password is required";
    if (!confirmPassword.value) resetErrors.confirmPassword = "Please confirm password";
    return;
  }

  if (newPassword.value !== confirmPassword.value) {
    resetErrors.confirmPassword = "Passwords do not match";
    return;
  }

  resetLoading.value = true;
  resetErrors.currentPassword = "";
  resetErrors.newPassword = "";
  resetErrors.confirmPassword = "";

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
      showResetModal.value = false;
      showSuccessMessage.value = false;
      currentPassword.value = "";
      newPassword.value = "";
      confirmPassword.value = "";
      error.value = "Password updated. Please login with your new password.";
    }, 2000);

  } catch (err) {
    if (err.response?.data?.errors) {
      const be = err.response.data.errors;
      if (be.current_password) resetErrors.currentPassword = be.current_password[0];
      if (be.new_password) resetErrors.newPassword = be.new_password[0];
      if (be.new_password_confirmation) resetErrors.confirmPassword = be.new_password_confirmation[0];
    } else if (err.response?.data?.message) {
      resetErrors.newPassword = err.response.data.message;
    }
  } finally {
    resetLoading.value = false;
  }
};

const closeResetModal = () => {
  showResetModal.value = false;
  currentPassword.value = "";
  newPassword.value = "";
  confirmPassword.value = "";
  showSuccessMessage.value = false;
};
</script>

<style scoped>
.min-h-screen::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.3);
  pointer-events: none;
}

.w-full.max-w-md {
  position: relative;
  z-index: 10;
}

input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>