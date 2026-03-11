// frontend/src/services/courtService.js
import api from "@/services/api";

const courtService = {
  // Get all courts with filters
  async getCourts(params = {}) {
    const { data } = await api.get("/admin/courts", { params });
    return data;
  },

  // Get active courts for dropdown
  async getActiveCourts() {
    const { data } = await api.get("/admin/courts/active");
    return data;
  },

  // Get court types for dropdown
  async getCourtTypes() {
    const { data } = await api.get("/admin/courts/types");
    return data;
  },

  // Get single court
  async getCourt(id) {
    const { data } = await api.get(`/admin/courts/${id}`);
    return data;
  },

  // Create new court
  async createCourt(payload) {
    const { data } = await api.post("/admin/courts", payload);
    return data;
  },

  // Update court
  async updateCourt(id, payload) {
    const { data } = await api.put(`/admin/courts/${id}`, payload);
    return data;
  },

  // Toggle court active status
  async toggleCourt(id) {
    const { data } = await api.patch(`/admin/courts/${id}/toggle`);
    return data;
  },

  // Delete court
  async deleteCourt(id) {
    const { data } = await api.delete(`/admin/courts/${id}`);
    return data;
  }
};

export default courtService;