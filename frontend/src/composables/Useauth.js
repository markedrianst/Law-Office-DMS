// src/composables/useAuth.js
import { ref, computed } from "vue";

// Shared reactive ref — null until explicitly loaded
const _user = ref(null);

// Internal loader
function loadUserFromSession() {
  try {
    const raw = sessionStorage.getItem("user");
    _user.value = raw ? JSON.parse(raw) : null;
  } catch {
    _user.value = null;
  }
}

// Hydrate immediately on first import
loadUserFromSession();

export function useAuth() {
  const userObj = _user;

  const userName = computed(() => {
    // Re-read sessionStorage as fallback if ref is empty
    if (!_user.value) loadUserFromSession();
    const u = _user.value;
    return u?.full_name || u?.name || "";
  });

  const userEmail = computed(() => {
    if (!_user.value) loadUserFromSession();
    return _user.value?.email ?? "";
  });

  const userRole = computed(() => {
    if (!_user.value) loadUserFromSession();
    const u = _user.value;
    if (!u) return "";
    return u.role?.name ?? (typeof u.role === "string" ? u.role.toLowerCase() : "");
  });

  const userInitials = computed(() => {
    const name = userName.value;
    if (!name) return "?";
    const parts = name.trim().split(/\s+/);
    return parts.length >= 2
      ? (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
      : parts[0].slice(0, 2).toUpperCase();
  });

  const isAdmin  = computed(() => userRole.value === "admin");
  const isLawyer = computed(() => userRole.value === "lawyer");
  const isClerk  = computed(() => userRole.value === "clerk");
  const isAuthenticated = computed(() => !!sessionStorage.getItem("token"));

  function refreshUser() {
    loadUserFromSession();
  }

  function patchStoredUser(patch = {}) {
    try {
      const raw = sessionStorage.getItem("user");
      const stored = raw ? JSON.parse(raw) : {};
      const updated = { ...stored, ...patch };
      sessionStorage.setItem("user", JSON.stringify(updated));
      _user.value = updated;
    } catch (e) {
      console.error("useAuth.patchStoredUser failed:", e);
    }
  }

  function clearSession() {
    sessionStorage.removeItem("token");
    sessionStorage.removeItem("user");
    _user.value = null;
  }

  return {
    userObj,
    userName,
    userEmail,
    userRole,
    userInitials,
    isAdmin,
    isLawyer,
    isClerk,
    isAuthenticated,
    refreshUser,
    patchStoredUser,
    clearSession,
  };
}