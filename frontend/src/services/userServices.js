// frontend/src/services/userService.js
import api from "@/services/api";
import cacheService from './cacheService';

const userService = {
  async getRoles() {
    const { data } = await api.get("/roles");
    return data;
  },

  async getUsers(params = {}) {
    const { data } = await api.get("/users", { params });
    
    // Transform data to ensure consistent format
    if (data.data) {
      data.data = data.data.map(user => ({
        id: user.id,
        name: user.name || `${user.firstName || ''} ${user.lastName || ''}`.trim(),
        email: user.email,
        role: user.role,
        status: user.status === 'active' ? 'Active' : (user.status || 'Inactive'),
        created_at: user.created_at,
        last_login: user.last_login,
        address: user.address,
        contact_number: user.contact_number
      }));
    }
    
    return data;
  },

  async getUserById(id) {
    const { data } = await api.get(`/users/${id}`);
    return data;
  },

  async createUser(userData) {
    const { data } = await api.post("/users", userData);
    cacheService.invalidateUserCache();
    return data;
  },

  async updateUser(id, userData) {
    const { data } = await api.put(`/users/${id}`, userData);
    cacheService.invalidateUserCache();
    return data;
  },

  async deleteUser(id) {
    const { data } = await api.delete(`/users/${id}`);
    cacheService.invalidateUserCache();
    return data;
  },

  async toggleUserStatus(id) {
    const { data } = await api.patch(`/users/${id}/toggle-status`);
    cacheService.invalidateUserCache();
    return data;
  },

  async updatePassword(id, passwordData) {
    const { data } = await api.post(`/users/${id}/change-password`, passwordData);
    return data;
  }
};

export default userService;