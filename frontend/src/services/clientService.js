// frontend/src/services/clientService.js
import api from "@/services/api";

const clientService = {
  // Get all clients with optional filters
  async getAll(params = {}) {
    const { data } = await api.get("/admin/clients", { params });
    return data;
  },

  // Get single client
  async getById(id) {
    const { data } = await api.get(`/admin/clients/${id}`);
    return data;
  },

  // Create new client
  async create(payload) {
    const { data } = await api.post("/admin/clients", payload);
    return data;
  },

  // Update client
  async update(id, payload) {
    const { data } = await api.put(`/admin/clients/${id}`, payload);
    return data;
  },

  // Delete client
  async delete(id) {
    const { data } = await api.delete(`/admin/clients/${id}`);
    return data;
  },

  // Search clients (for dropdown)
  async search(query, limit = 50) {
    const { data } = await api.get("/admin/clients/search", {
      params: { search: query, limit }
    });
    return data;
  },

  // Get client cases
  async getClientCases(clientId, params = {}) {
    const { data } = await api.get(`/admin/clients/${clientId}/cases`, { params });
    return data;
  }
};

export default clientService;