// src/services/auth.js

import api from "@/services/api";
import { setUser } from "@/utils/appUtils";

let interceptorId = null;

// Initialize interceptor safely
const initAuthInterceptor = () => {
  if (interceptorId !== null) {
    try {
      api.interceptors.request.eject(interceptorId);
    } catch (e) {
    }
    interceptorId = null;
  }

  interceptorId = api.interceptors.request.use(
    (config) => {
      const token = sessionStorage.getItem("token");
      if (token) {
        config.headers = config.headers || {};
        config.headers["Authorization"] = `Bearer ${token}`;
      }
      return config;
    },
    (error) => Promise.reject(error)
  );
  
};

// Initialize immediately
initAuthInterceptor();

// ✅ FIX: Load user from session on page refresh
const loadUserFromSession = () => {
  try {
    const userData = sessionStorage.getItem('user');
    if (userData) {
      const user = JSON.parse(userData);
      setUser(user);
    }
  } catch (error) {
  }
};

// Load user immediately
loadUserFromSession();

const authService = {
 async getCsrfCookie() {
    await api.get("/sanctum/csrf-cookie", {
        baseURL: "https://pinedalawoffice.emberwebsolutions.com"
    });
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
        
        // Store in appUtils
        setUser(data.user);
        
        // Re-initialize interceptor
        initAuthInterceptor();
      }

      return data;

    } catch (error) {
      throw error;
    }
  },

  async logout() {
    await this.getCsrfCookie();
    
    try {
      await api.post("/logout");
    } catch (error) {
    } finally {
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      
      // Re-initialize interceptor
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