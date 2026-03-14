// src/services/auth.js

import api from "@/services/api";
import { useAuth } from '@/composables/Useauth';

let _interceptorId = null;

const initAuthInterceptor = () => {
  if (_interceptorId !== null) {
    api.interceptors.request.eject(_interceptorId);
  }

  _interceptorId = api.interceptors.request.use((config) => {
    const token = sessionStorage.getItem("token");
    if (token) {
      config.headers = config.headers ?? {};
      config.headers["Authorization"] = `Bearer ${token}`;
    }
    return config;
  });
};

initAuthInterceptor();

// Preload cache - will store dashboard data before navigation
let preloadPromise = null;

const authService = {
  async getCsrfCookie() {
    await api.get("/sanctum/csrf-cookie");
  },

  async login(payload) {
    await this.getCsrfCookie();
    
    try {
      const { data } = await api.post("/login", payload);

      if (data.requires_password_change) {
        return {
          requires_password_change: true,
          user: data.user
        };
      }

      if (data.token) {
        // Store token and user
        sessionStorage.setItem('token', data.token);
        sessionStorage.setItem('user', JSON.stringify(data.user));
        
        // CRITICAL: PRELOAD dashboard data NOW (before navigation)
        // This runs in parallel - doesn't block login response
        preloadPromise = this.preloadDashboard();
        
        // Refresh auth state
        const { refreshUser } = useAuth();
        refreshUser();
      }

      return data;

    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },

  // Preload dashboard data into cache
  async preloadDashboard() {
    try {
      console.log('🔄 Preloading dashboard data...');
      const start = performance.now();
      
      const { data } = await api.get('/dashboard');
      
      // Store in cache
      sessionStorage.setItem('dashboard_cache', JSON.stringify({
        data,
        timestamp: Date.now()
      }));
      
      const duration = performance.now() - start;
      console.log(`✅ Dashboard preloaded in ${duration.toFixed(2)}ms`);
      
      return data;
    } catch (error) {
      console.error('❌ Preload failed:', error);
      return null;
    }
  },

  // Wait for preload to complete (optional - for dashboard to await)
  async waitForPreload() {
    if (preloadPromise) {
      return preloadPromise;
    }
    return null;
  },

  async logout() {
    await this.getCsrfCookie();
    
    try {
      await api.post("/logout");
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      sessionStorage.removeItem('dashboard_cache');
      
      const { clearSession } = useAuth();
      clearSession();
      
      if (_interceptorId !== null) {
        api.interceptors.request.eject(_interceptorId);
        _interceptorId = null;
      }
      initAuthInterceptor();
      
      window.location.href = '/';
    }
  },

  async getUser() {
    await this.getCsrfCookie();
    const { data } = await api.get("/user");
    return data;
  },

  async changePassword(payload) {
    await this.getCsrfCookie();
    const { data } = await api.put("/changepassword", payload);
    return data;
  }
};

export default authService;