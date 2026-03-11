<template>
  <!-- Page wrapper — fades in on every mount via `visible` ref -->
  <div
    class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat p-4 transition-opacity duration-700 ease-out"
    :class="visible ? 'opacity-100' : 'opacity-0'"
    :style="{ backgroundImage: 'url(' + backgroundImage + ')' }"
  >
    <!-- Floating Glass Card -->
    <div class="w-full max-w-md">
      <div class="relative">
        <div class="absolute inset-0 bg-black/20 rounded-2xl blur-xl transform translate-y-2"></div>

        <div
          class="relative backdrop-blur-md rounded-2xl shadow-2xl p-8 border transition-transform duration-700 ease-out"
          :class="visible ? 'translate-y-0' : 'translate-y-4'"
          :style="{
            backgroundColor: 'rgba(255, 255, 255, 0.15)',
            borderColor: 'rgba(255, 255, 255, 0.3)',
            boxShadow: '0 20px 40px -10px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1) inset'
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
              <h1 class="text-3xl font-bold mb-1"
                  :style="{ color: 'white', textShadow: '0 2px 4px rgba(0, 0, 0, 0.3)' }">
                NICOLAS PINEDA
              </h1>
              <h1 class="text-3xl font-bold mb-1"
                  :style="{ color: 'white', textShadow: '0 2px 4px rgba(0, 0, 0, 0.3)' }">
                LAW OFFICE
              </h1>
              <p class="text-sm tracking-wide"
                 :style="{ color: 'rgba(255, 255, 255, 0.9)', textShadow: '0 1px 2px rgba(0, 0, 0, 0.2)' }">
                Data Management System
              </p>
            </div>

            <!-- General Error Message -->
            <div v-if="errors.general" class="mb-4 p-3 rounded-xl text-center"
                 :style="{
                   backgroundColor: 'rgba(239, 68, 68, 0.2)',
                   border: '1px solid rgba(239, 68, 68, 0.5)',
                   color: 'white',
                   backdropFilter: 'blur(5px)'
                 }">
              {{ errors.general }}
            </div>

            <form @submit.prevent="handleLogin" class="space-y-6">
              <!-- Email Field -->
              <div>
                <label class="block text-sm font-medium mb-1"
                       :style="{ color: 'rgba(255, 255, 255, 0.9)' }">
                  Email
                </label>
                <input
                  v-model="email"
                  type="email"
                  class="w-full px-4 py-3 rounded-xl transition-all duration-200 placeholder-white/50"
                  :style="{
                    backgroundColor: 'rgba(255, 255, 255, 0.15)',
                    border: errors.email ? '1px solid #ef4444' : '1px solid rgba(255, 255, 255, 0.3)',
                    color: 'white',
                    outline: 'none',
                    backdropFilter: 'blur(5px)'
                  }"
                  placeholder="Enter your email"
                  @focus="handleFocus"
                  @blur="handleBlur"
                  @input="clearFieldError('email')"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-300"
                   :style="{ textShadow: '0 1px 2px rgba(0, 0, 0, 0.2)' }">
                  {{ errors.email }}
                </p>
              </div>

              <!-- Password Field -->
              <div>
                <label class="block text-sm font-medium mb-1"
                       :style="{ color: 'rgba(255, 255, 255, 0.9)' }">
                  Password
                </label>
                <div class="relative">
                  <input
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="w-full px-4 py-3 rounded-xl transition-all duration-200 placeholder-white/50"
                    :style="{
                      backgroundColor: 'rgba(255, 255, 255, 0.15)',
                      border: errors.password ? '1px solid #ef4444' : '1px solid rgba(255, 255, 255, 0.3)',
                      color: 'white',
                      outline: 'none',
                      backdropFilter: 'blur(5px)'
                    }"
                    placeholder="Enter your password"
                    @focus="handleFocus"
                    @blur="handleBlur"
                    @input="clearFieldError('password')"
                  />
                  <button type="button" @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 transition-colors duration-200"
                    :style="{ color: 'rgba(255, 255, 255, 0.7)' }">
                    <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm2.458 5.458C15.732 18.79 13.938 19.5 12 19.5c-1.938 0-3.732-.71-5.458-2.042C4.5 15.562 3 13.5 3 12s1.5-3.562 3.542-5.458C8.268 5.21 10.062 4.5 12 4.5c1.938 0 3.732.71 5.458 2.042C19.5 8.438 21 10.5 21 12s-1.5 3.562-3.542 5.458z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314" />
                    </svg>
                  </button>
                </div>
                <p v-if="errors.password" class="mt-1 text-sm text-red-300"
                   :style="{ textShadow: '0 1px 2px rgba(0, 0, 0, 0.2)' }">
                  {{ errors.password }}
                </p>
              </div>

              <!-- Login Button -->
              <button
                type="submit"
                :disabled="loading"
                class="relative w-full py-3 rounded-xl font-medium transition-all duration-200 overflow-hidden"
                :style="{
                  background: 'linear-gradient(135deg, rgba(26, 73, 114, 0.9), rgba(15, 47, 74, 0.95))',
                  color: 'white',
                  border: '1px solid rgba(255, 255, 255, 0.2)',
                  backdropFilter: 'blur(5px)',
                  boxShadow: '0 4px 15px rgba(0, 0, 0, 0.3)',
                  opacity: loading ? 0.7 : 1,
                  cursor: loading ? 'not-allowed' : 'pointer'
                }"
                @mouseover="hoverButton = true"
                @mouseleave="hoverButton = false"
              >
                <div class="absolute inset-0 transition-opacity duration-200"
                     :style="{ opacity: hoverButton && !loading ? 1 : 0, background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.2), transparent)' }">
                </div>
                <span class="relative z-10 flex items-center justify-center">
                  <svg v-if="loading" class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
                borderColor: 'rgba(255, 255, 255, 0.3)',
                boxShadow: '0 20px 40px -10px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.1) inset'
              }"
            >
              <div class="absolute inset-0 rounded-2xl pointer-events-none"
                   :style="{ background: 'radial-gradient(circle at 50% 0%, rgba(255,255,255,0.3), transparent 70%)' }">
              </div>

              <div class="relative z-10">
                <div class="text-center mb-6">
                  <h2 class="text-2xl font-bold"
                      :style="{ color: 'white', textShadow: '0 2px 4px rgba(0, 0, 0, 0.3)' }">
                    Change Password
                  </h2>
                  <p class="text-sm mt-2"
                     :style="{ color: 'rgba(255, 255, 255, 0.9)', textShadow: '0 1px 2px rgba(0, 0, 0, 0.2)' }">
                    You must change your password before continuing.
                  </p>
                </div>

                <form @submit.prevent="handleResetPassword">
                  <!-- Current Password -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :style="{ color: 'rgba(255, 255, 255, 0.9)' }">
                      Current Password
                    </label>
                    <div class="relative">
                      <input
                        v-model="currentPassword"
                        :type="showCurrentPassword ? 'text' : 'password'"
                        placeholder="Enter current password"
                        class="w-full px-4 py-3 rounded-xl transition-all duration-200 placeholder-white/50"
                        :style="{
                          backgroundColor: 'rgba(255, 255, 255, 0.15)',
                          border: resetErrors.currentPassword ? '1px solid #ef4444' : '1px solid rgba(255, 255, 255, 0.3)',
                          color: 'white', outline: 'none', backdropFilter: 'blur(5px)'
                        }"
                        @focus="handleModalFocus"
                        @blur="handleModalBlur"
                      />
                      <button type="button" @click="showCurrentPassword = !showCurrentPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2"
                        :style="{ color: 'rgba(255, 255, 255, 0.7)' }">
                        <svg v-if="!showCurrentPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm2.458 5.458C15.732 18.79 13.938 19.5 12 19.5c-1.938 0-3.732-.71-5.458-2.042C4.5 15.562 3 13.5 3 12s1.5-3.562 3.542-5.458C8.268 5.21 10.062 4.5 12 4.5c1.938 0 3.732.71 5.458 2.042C19.5 8.438 21 10.5 21 12s-1.5 3.562-3.542 5.458z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="resetErrors.currentPassword" class="mt-1 text-sm text-red-300">{{ resetErrors.currentPassword }}</p>
                  </div>

                  <!-- New Password -->
                  <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" :style="{ color: 'rgba(255, 255, 255, 0.9)' }">
                      New Password
                    </label>
                    <div class="relative">
                      <input
                        v-model="newPassword"
                        :type="showNewPassword ? 'text' : 'password'"
                        placeholder="Enter new password"
                        class="w-full px-4 py-3 rounded-xl transition-all duration-200 placeholder-white/50"
                        :style="{
                          backgroundColor: 'rgba(255, 255, 255, 0.15)',
                          border: resetErrors.newPassword ? '1px solid #ef4444' : '1px solid rgba(255, 255, 255, 0.3)',
                          color: 'white', outline: 'none', backdropFilter: 'blur(5px)'
                        }"
                        @focus="handleModalFocus"
                        @blur="handleModalBlur"
                      />
                      <button type="button" @click="showNewPassword = !showNewPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2"
                        :style="{ color: 'rgba(255, 255, 255, 0.7)' }">
                        <svg v-if="!showNewPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm2.458 5.458C15.732 18.79 13.938 19.5 12 19.5c-1.938 0-3.732-.71-5.458-2.042C4.5 15.562 3 13.5 3 12s1.5-3.562 3.542-5.458C8.268 5.21 10.062 4.5 12 4.5c1.938 0 3.732.71 5.458 2.042C19.5 8.438 21 10.5 21 12s-1.5 3.562-3.542 5.458z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="resetErrors.newPassword" class="mt-1 text-sm text-red-300">{{ resetErrors.newPassword }}</p>
                  </div>

                  <!-- Confirm Password -->
                  <div class="mb-6">
                    <label class="block text-sm font-medium mb-1" :style="{ color: 'rgba(255, 255, 255, 0.9)' }">
                      Confirm New Password
                    </label>
                    <div class="relative">
                      <input
                        v-model="confirmPassword"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        placeholder="Confirm new password"
                        class="w-full px-4 py-3 rounded-xl transition-all duration-200 placeholder-white/50"
                        :style="{
                          backgroundColor: 'rgba(255, 255, 255, 0.15)',
                          border: resetErrors.confirmPassword ? '1px solid #ef4444' : '1px solid rgba(255, 255, 255, 0.3)',
                          color: 'white', outline: 'none', backdropFilter: 'blur(5px)'
                        }"
                        @focus="handleModalFocus"
                        @blur="handleModalBlur"
                      />
                      <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2"
                        :style="{ color: 'rgba(255, 255, 255, 0.7)' }">
                        <svg v-if="!showConfirmPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm2.458 5.458C15.732 18.79 13.938 19.5 12 19.5c-1.938 0-3.732-.71-5.458-2.042C4.5 15.562 3 13.5 3 12s1.5-3.562 3.542-5.458C8.268 5.21 10.062 4.5 12 4.5c1.938 0 3.732.71 5.458 2.042C19.5 8.438 21 10.5 21 12s-1.5 3.562-3.542 5.458z" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19.5c-4.5 0-8.25-3-9-7.5a9.956 9.956 0 012.16-4.112M6.223 6.223A9.953 9.953 0 0112 4.5c4.5 0 8.25 3 9 7.5a9.953 9.953 0 01-4.223 6.277M6.223 6.223L3 3m3.223 3.223l11.314 11.314" />
                        </svg>
                      </button>
                    </div>
                    <p v-if="resetErrors.confirmPassword" class="mt-1 text-sm text-red-300">{{ resetErrors.confirmPassword }}</p>
                  </div>

                  <!-- Success Message -->
                  <Transition name="fade">
                    <div
                      v-if="showSuccessMessage"
                      class="mb-4 p-3 rounded-xl text-center"
                      :style="{
                        backgroundColor: 'rgba(34, 197, 94, 0.2)',
                        border: '1px solid rgba(34, 197, 94, 0.5)',
                        color: 'white',
                        backdropFilter: 'blur(5px)'
                      }"
                    >
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
                      class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200"
                      :style="{
                        backgroundColor: 'rgba(255, 255, 255, 0.15)',
                        color: 'white',
                        border: '1px solid rgba(255, 255, 255, 0.3)',
                        backdropFilter: 'blur(5px)',
                        opacity: (resetLoading || showSuccessMessage) ? 0.5 : 1,
                        cursor: (resetLoading || showSuccessMessage) ? 'not-allowed' : 'pointer'
                      }"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="resetLoading || showSuccessMessage"
                      class="relative px-5 py-2.5 rounded-xl font-medium transition-all duration-200 overflow-hidden"
                      :style="{
                        background: 'linear-gradient(135deg, rgba(26, 73, 114, 0.9), rgba(15, 47, 74, 0.95))',
                        color: 'white',
                        border: '1px solid rgba(255, 255, 255, 0.2)',
                        backdropFilter: 'blur(5px)',
                        opacity: (resetLoading || showSuccessMessage) ? 0.7 : 1,
                        cursor: (resetLoading || showSuccessMessage) ? 'not-allowed' : 'pointer'
                      }"
                    >
                      <span class="relative z-10 flex items-center justify-center gap-2">
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
import { ref, reactive, onMounted } from "vue";
import { useRouter } from "vue-router";
import authService from "@/services/auth";
import backgroundImg from "../../assets/images/bg.jpg";

const router = useRouter();
const backgroundImage = ref(backgroundImg);

// ── Fade-in animation ─────────────────────────────────────────────────────
const visible = ref(false);
onMounted(() => {
  setTimeout(() => { visible.value = true; }, 0);
});

// Login form state
const email = ref("");
const password = ref("");
const loading = ref(false);
const showPassword = ref(false);
const hoverButton = ref(false);

// Modal state
const showResetModal = ref(false);
const resetEmail = ref("");
const currentPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const resetLoading = ref(false);
const showSuccessMessage = ref(false);
const successMessage = ref("");

// Password visibility toggles
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Error state
const errors = reactive({ email: "", password: "", general: "" });
const resetErrors = reactive({ currentPassword: "", newPassword: "", confirmPassword: "" });

// ─── LOGIN HANDLER ────────────────────────────────────────────────────────
const handleLogin = async () => {
  loading.value = true;
  
  // Clear previous errors
  errors.email = "";
  errors.password = "";
  errors.general = "";

  try {
    const response = await authService.login({ 
      email: email.value, 
      password: password.value 
    });
    
    // Handle password change requirement
    if (response.requires_password_change) {
      resetEmail.value = response.user.email;
      showResetModal.value = true;
      return;
    }
    
    // Successful login
    router.push("/dashboard");
    
  } catch (error) {
    // Handle validation errors from backend
    if (error.response?.data?.errors) {
      const backendErrors = error.response.data.errors;
      if (backendErrors.email) errors.email = backendErrors.email[0];
      if (backendErrors.password) errors.password = backendErrors.password[0];
    } else if (error.response?.data?.message) {
      errors.general = error.response.data.message;
    } else {
      errors.general = "An error occurred. Please try again.";
    }
  } finally {
    loading.value = false;
  }
};

// ─── PASSWORD CHANGE HANDLER ──────────────────────────────────────────────
const handleResetPassword = async () => {
  // Clear previous errors
  resetErrors.currentPassword = "";
  resetErrors.newPassword = "";
  resetErrors.confirmPassword = "";
  
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
      showResetModal.value = false;
      showSuccessMessage.value = false;
      
      // Clear form
      currentPassword.value = "";
      newPassword.value = "";
      confirmPassword.value = "";
      
      // Show message on login form
      errors.general = "Password updated. Please login with your new password.";
    }, 2000);

  } catch (error) {
    // Handle validation errors from backend
    if (error.response?.data?.errors) {
      const backendErrors = error.response.data.errors;
      if (backendErrors.current_password) resetErrors.currentPassword = backendErrors.current_password[0];
      if (backendErrors.new_password) resetErrors.newPassword = backendErrors.new_password[0];
      if (backendErrors.new_password_confirmation) resetErrors.confirmPassword = backendErrors.new_password_confirmation[0];
    } else if (error.response?.data?.message) {
      resetErrors.newPassword = error.response.data.message;
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

// Input focus handlers
const handleFocus = (e) => {
  e.target.style.backgroundColor = "rgba(255, 255, 255, 0.25)";
  e.target.style.borderColor = "rgba(255, 255, 255, 0.5)";
};

const handleBlur = (e) => {
  e.target.style.backgroundColor = "rgba(255, 255, 255, 0.15)";
  e.target.style.borderColor = "rgba(255, 255, 255, 0.3)";
};

const handleModalFocus = handleFocus;
const handleModalBlur = handleBlur;

const clearFieldError = (field) => {
  errors[field] = "";
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

input, button, a {
  transition: all 0.2s ease;
}

input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.text-red-300 {
  color: #fca5a5;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  padding-left: 0.25rem;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
  20%, 40%, 60%, 80% { transform: translateX(2px); }
}

.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>