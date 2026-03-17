import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  getUser,
  getUserName,
  getUserRole,
  getUserInitials,
  getRoleLabel,
  isAdmin,
  isLawyer,
  isClerk,
  listenForUpdates
} from '@/utils/appUtils';

export function useAuth() {
  // ==================== STATE ====================
  const user = ref(getUser());
  const userName = ref(getUserName());
  const userRole = ref(getUserRole());
  const userInitials = ref(getUserInitials());
  const userRoleLabel = ref(getRoleLabel(userRole.value));
  
  // ==================== COMPUTED ====================
  const isAuthenticated = computed(() => !!sessionStorage.getItem('token'));
  const isAdminUser = computed(() => isAdmin(userRole.value));
  const isLawyerUser = computed(() => isLawyer(userRole.value));
  const isClerkUser = computed(() => isClerk(userRole.value));
  
  // ==================== UPDATE FUNCTION ====================
  const updateUserData = () => {
    console.log('🔄 Updating user data from appUtils');
    user.value = getUser();
    userName.value = getUserName();
    userRole.value = getUserRole();
    userInitials.value = getUserInitials();
    userRoleLabel.value = getRoleLabel(userRole.value);
  };
  
  // ==================== MANUAL REFRESH ====================
  const refreshUser = () => {
    updateUserData();
  };
  
  // ==================== CLEAR SESSION ====================
  const clearSession = () => {
    user.value = null;
    userName.value = 'User';
    userRole.value = 'user';
    userInitials.value = 'U';
    userRoleLabel.value = 'User';
    sessionStorage.removeItem('user');
    sessionStorage.removeItem('token');
  };
  
  // ==================== LIFECYCLE ====================
  let cleanup = null;
  
  onMounted(() => {
    console.log('📌 useAuth mounted');
    
    // Listen for user updates from appUtils
    cleanup = listenForUpdates('user-updated', updateUserData);
    
    // Also listen for storage events (for multi-tab support)
    const handleStorageChange = (e) => {
      if (e.key === 'user') {
        console.log('📦 User data changed in another tab');
        updateUserData();
      }
    };
    
    window.addEventListener('storage', handleStorageChange);
    
    // Cleanup on unmount
    onUnmounted(() => {
      if (cleanup) cleanup();
      window.removeEventListener('storage', handleStorageChange);
    });
  });
  
  return {
    // State
    user,
    userName,
    userRole,
    userRoleLabel,
    userInitials,
    
    // Computed
    isAuthenticated,
    isAdmin: isAdminUser,
    isLawyer: isLawyerUser,
    isClerk: isClerkUser,
    
    // Methods
    refreshUser,
    clearSession
  };
}