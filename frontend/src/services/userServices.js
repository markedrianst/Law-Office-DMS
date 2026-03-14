// src/services/userService.js

import api from "@/services/api";

class UserService {
  constructor() {
    this.cache = {
      roles: null,
      rolesTimestamp: null
    };
    this.CACHE_TTL = 3600000; // 1 hour
  }

  async getRoles(forceRefresh = false) {
    // Check cache first
    if (!forceRefresh && this.cache.roles && this.cache.rolesTimestamp) {
      if (Date.now() - this.cache.rolesTimestamp < this.CACHE_TTL) {
        return { data: this.cache.roles };
      }
    }

    const { data } = await api.get("/roles");
    
    // Update cache
    this.cache.roles = data.data || [];
    this.cache.rolesTimestamp = Date.now();
    
    return data;
  }

  async getUsers(params = {}) {
    const { data } = await api.get("/users", { params });
    
    // Transform data for consistent format - REMOVED address and contact_number
    if (data.data) {
      data.data = data.data.map(user => ({
        id: user.id,
        name: user.name || '',
        email: user.email,
        role: user.role,
        status: user.status === 'Active' ? 'Active' : 'Inactive',
        created_at: user.created_at,
        last_login: user.last_login
      }));
    }
    
    return data;
  }

  async getUserById(id) {
    const { data } = await api.get(`/users/${id}`);
    return data;
  }

  async createUser(userData) {
    const { data } = await api.post("/users", userData);
    return data;
  }

  async updateUser(id, userData) {
    const { data } = await api.put(`/users/${id}`, userData);
    return data;
  }

  async deleteUser(id) {
    const { data } = await api.delete(`/users/${id}`);
    return data;
  }

  async toggleUserStatus(id) {
    const { data } = await api.patch(`/users/${id}/toggle-status`);
    return data;
  }

  clearRolesCache() {
    this.cache.roles = null;
    this.cache.rolesTimestamp = null;
  }
}

export default new UserService();