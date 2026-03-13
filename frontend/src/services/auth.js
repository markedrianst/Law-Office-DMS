// src/services/auth.js
import api from "@/services/api";
import { useAuth } from '@/composables/useAuth';

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
        sessionStorage.setItem('token', data.token);
        sessionStorage.setItem('user', JSON.stringify(data.user));
        
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

  async logout() {
    await this.getCsrfCookie();
    
    try {
      const { data } = await api.post("/logout");
      
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      
      const { clearSession } = useAuth();
      clearSession();
      
      if (_interceptorId !== null) {
        api.interceptors.request.eject(_interceptorId);
        _interceptorId = null;
      }
      initAuthInterceptor();
      
      return data;
    } catch (error) {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      throw error;
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