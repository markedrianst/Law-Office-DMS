import api from "@/services/api";
import { setUser } from "@/utils/appUtils";

let interceptorId = null;

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

initAuthInterceptor();
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

loadUserFromSession();

const authService = {
 async getCsrfCookie() {
    await api.get("/sanctum/csrf-cookie", {
        // baseURL: "https://pinedalawoffice.emberwebsolutions.com"
        baseURL: "http://localhost:8000"
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

        sessionStorage.setItem('token', data.token);
        sessionStorage.setItem('user', JSON.stringify(data.user));
        setUser(data.user);
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