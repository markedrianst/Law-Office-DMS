// src/composables/useAuth.js
import { ref, computed } from "vue";

// Shared reactive refs
const _user = ref(null);
const _isReady = ref(false);

// Internal loader - runs once when module loads
function loadUserFromSession() {
  try {
    const raw = sessionStorage.getItem("user");
    _user.value = raw ? JSON.parse(raw) : null;
    console.log('✅ Auth loaded from session:', _user.value);
  } catch (error) {
    console.error('❌ Failed to parse user from session:', error);
    _user.value = null;
  } finally {
    _isReady.value = true;
    console.log('✅ Auth is ready:', _isReady.value);
  }
}

// Load immediately on module import
loadUserFromSession();

export function useAuth() {
  // ========== COMPUTED PROPERTIES ==========
  const userName = computed(() => {
    return _user.value?.full_name || _user.value?.name || "";
  });

  const userEmail = computed(() => {
    return _user.value?.email || "";
  });

  const userRole = computed(() => {
    const u = _user.value;
    if (!u) return "";
    
    // Handle both { role: { name: "admin" } } and { role: "admin" } formats
    if (u.role?.name) return u.role.name.toLowerCase();
    if (typeof u.role === "string") return u.role.toLowerCase();
    return "";
  });

  const userInitials = computed(() => {
    const name = userName.value;
    if (!name) return "?";
    
    const parts = name.trim().split(/\s+/);
    if (parts.length === 0) return "?";
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  });

  // ========== ROLE CHECKS ==========
  const isAdmin = computed(() => userRole.value === "admin");
  const isLawyer = computed(() => userRole.value === "lawyer");
  const isClerk = computed(() => userRole.value === "clerk");
  const isAuthenticated = computed(() => !!sessionStorage.getItem("token"));

  // ========== READY STATE ==========
  const isAuthReady = computed(() => _isReady.value);

  // ========== METHODS ==========
  function refreshUser() {
    console.log('🔄 Refreshing auth from sessionStorage');
    loadUserFromSession();
    return _user.value;
  }

  function patchStoredUser(patch = {}) {
    try {
      const raw = sessionStorage.getItem("user");
      const stored = raw ? JSON.parse(raw) : {};
      const updated = { ...stored, ...patch };
      sessionStorage.setItem("user", JSON.stringify(updated));
      _user.value = updated;
      console.log('✅ User patched:', updated);
    } catch (e) {
      console.error("❌ useAuth.patchStoredUser failed:", e);
    }
  }

  function clearSession() {
    console.log('🚪 Clearing auth session');
    sessionStorage.removeItem("token");
    sessionStorage.removeItem("user");
    _user.value = null;
    // Don't set _isReady to false - we're still "ready", just no user
  }

  // ========== RETURN ALL PROPERTIES ==========
  return {
    // State
    user: _user,
    
    // Computed user info
    userName,
    userEmail,
    userRole,
    userInitials,
    
    // Role checks
    isAdmin,
    isLawyer,
    isClerk,
    isAuthenticated,
    
    // Ready state
    isAuthReady,
    
    // Methods
    refreshUser,
    patchStoredUser,
    clearSession,
  };
}