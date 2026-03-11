// frontend/src/services/userService.js
import api from "@/services/api";

const userService = {
  // Get roles for dropdown
  async getRoles() {
    const { data } = await api.get("/roles");
    return data;
  },

  // Get users with filters
  async getUsers(params = {}) {
    const { data } = await api.get("/users", { params });
    return data;
  },

  // Get single user
  async getUserById(id) {
    const { data } = await api.get(`/users/${id}`);
    return data;
  },

  // Create user
  async createUser(userData) {
    const { data } = await api.post("/users", userData);
    return data;
  },

  // Update user
  async updateUser(id, userData) {
    const { data } = await api.put(`/users/${id}`, userData);
    return data;
  },

  // Delete user
  async deleteUser(id) {
    const { data } = await api.delete(`/users/${id}`);
    return data;
  },

  // Toggle user status
  async toggleUserStatus(id) {
    const { data } = await api.patch(`/users/${id}/toggle-status`);
    return data;
  },

  // Update password
  async updatePassword(id, passwordData) {
    const { data } = await api.post(`/users/${id}/change-password`, passwordData);
    return data;
  }
};

export default userService;