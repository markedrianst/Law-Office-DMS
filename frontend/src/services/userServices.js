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
      // Only fetch if not already in cache or forced refresh
      const { data } = await api.get("/users", { params });
      
      if (data.data) {
        // Optimized transformation
        const transformedUsers = data.data.map(user => ({
          id: user.id,
          name: user.name || '',
          email: user.email,
          role: user.role,
          status: user.status === 'Active' ? 'Active' : 'Inactive',
          created_at: user.created_at,
          last_login: user.last_login,
          contact_no: user.contact_no || user.contact || '',
          address: user.address || '',
          contact: user.contact || user.contact_no || ''
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

  // OPTIMIZED: Remove automatic refresh after operations
  async createUser(userData) {
    try {
      const { data } = await api.post("/users", userData);
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  // OPTIMIZED: Remove automatic refresh after operations
  async updateUser(id, userData) {
    try {
      const { data } = await api.put(`/users/${id}`, userData);
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  // OPTIMIZED: Remove automatic refresh after operations
  async deleteUser(id) {
    try {
      const { data } = await api.delete(`/users/${id}`);
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  async toggleUserStatus(id) {
    try {
      const { data } = await api.patch(`/users/${id}/toggle-status`);
      return data;
    } catch (error) {
      throw this.handleError(error);
    }
  }

  // OPTIMIZED: Simplified validation
  validatePhilippineMobile(number) {
    if (!number || number.trim() === '') {
      return { isValid: true };
    }
    
    const cleaned = number.replace(/\D/g, '');
    const isValid = (
      (cleaned.length === 11 && cleaned.startsWith('09')) ||
      (cleaned.length === 12 && cleaned.startsWith('63')) ||
      number.replace(/[^\d+]/g, '').match(/^\+639\d{9}$/)
    );
    
    return {
      isValid,
      message: isValid ? '' : 'Invalid Philippine mobile number'
    };
  }

  handleError(error) {
    if (error.response) {
      const { status, data } = error.response;
      
      if (status === 422) {
        const formattedErrors = {};
        
        if (data.errors) {
          Object.keys(data.errors).forEach(key => {
            formattedErrors[key] = Array.isArray(data.errors[key]) 
              ? data.errors[key][0] 
              : data.errors[key];
          });
        }
        
        return {
          message: data.message || 'Validation failed',
          errors: formattedErrors,
          status
        };
      }
      
      let message = data.message || `Error: ${status}`;
      
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
      return {
        message: 'Network error. Please check your connection.',
        errors: {},
        status: 0
      };
    } else {
      return {
        message: error.message || 'An unexpected error occurred',
        errors: {},
        status: 0
      };
    }
  }
}

export default new UserService();