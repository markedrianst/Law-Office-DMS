// src/services/userServices.js
import api from "@/services/api";
import { setUsers } from "@/utils/appUtils";

class UserService {
  async getRoles() {
    try {
      const { data } = await api.get("/roles");
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async getUsers(params = {}) {
    try {
      const { data } = await api.get("/users", { params });
      
      if (data.data) {
        const transformedUsers = data.data.map(user => ({
          id: user.id,
          name: user.name || '',
          email: user.email,
          role: user.role,
          status: user.status === 'Active' ? 'Active' : 'Inactive',
          created_at: user.created_at,
          last_login: user.last_login
        }));
        
        setUsers(transformedUsers);
        data.data = transformedUsers;
      }
      
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async getUserById(id) {
    try {
      const { data } = await api.get(`/users/${id}`);
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async createUser(userData) {
    try {
      const { data } = await api.post("/users", userData);
      
      if (data.data) {
        await this.getUsers({ per_page: 100 });
      }
      
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async updateUser(id, userData) {
    try {
      const { data } = await api.put(`/users/${id}`, userData);
      
      if (data.data) {
        await this.getUsers({ per_page: 100 });
      }
      
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async deleteUser(id) {
    try {
      const { data } = await api.delete(`/users/${id}`);
      await this.getUsers({ per_page: 100 });
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async toggleUserStatus(id) {
    try {
      const { data } = await api.patch(`/users/${id}/toggle-status`);
      
      if (data.data) {
        await this.getUsers({ per_page: 100 });
      }
      
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  // FIXED: Proper error handling
  handleError(error) {
    if (error.response) {
      const { status, data } = error.response;
      
      // For validation errors (422)
      if (status === 422) {
        const formattedErrors = {};
        
        // Check if errors object exists in the response
        if (data.errors) {
          // Laravel validation errors format
          Object.keys(data.errors).forEach(key => {
            formattedErrors[key] = Array.isArray(data.errors[key]) 
              ? data.errors[key][0] 
              : data.errors[key];
          });
        } else if (data.message) {
          // Single error message
          formattedErrors.general = data.message;
        }
        
        return {
          message: data.message || 'Validation failed',
          errors: formattedErrors,
          status
        };
      }
      
      // Handle other HTTP errors
      let message = data.message || `Error: ${status}`;
      
      // Make error messages user-friendly
      if (status === 403) {
        message = 'You do not have permission to perform this action';
      } else if (status === 404) {
        message = 'The requested resource was not found';
      } else if (status === 500) {
        message = 'Server error. Please try again later';
      }
      
      return {
        message,
        errors: data.errors || {},
        status
      };
    } else if (error.request) {
      // Network error
      return {
        message: 'Network error. Please check your connection.',
        errors: {},
        status: 0
      };
    } else {
      // Other errors
      return {
        message: error.message || 'An unexpected error occurred',
        errors: {},
        status: 0
      };
    }
  }
}

export default new UserService();