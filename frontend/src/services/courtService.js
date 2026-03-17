import api from "@/services/api";
import { 
  setCourts, 
  addCourt, 
  updateCourtInStore, 
  removeCourtFromStore 
} from "@/utils/appUtils";

const courtService = {
  // Get all courts with filters
  async getCourts(params = {}) {
    const { data } = await api.get("/admin/courts", { params });
    
    // Store in appUtils
    if (data.data) {
      setCourts(data.data);
    }
    
    return data;
  },

  // Get active courts for dropdown
  async getActiveCourts() {
    const { data } = await api.get("/admin/courts/active");
    
    // Store in appUtils
    if (data.data) {
      setCourts(data.data);
    }
    
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
    
    // If API returns the created court, add it to store
    if (data.data) {
      addCourt(data.data);
    }
    
    return data;
  },

  // Update court
  async updateCourt(id, payload) {
    const { data } = await api.put(`/admin/courts/${id}`, payload);
    
    // If API returns the updated court, update it in store
    if (data.data) {
      updateCourtInStore(id, data.data);
    }
    
    return data;
  },

  // Toggle court active status
  async toggleCourt(id) {
    const { data } = await api.patch(`/admin/courts/${id}/toggle`);
    
    // If API returns the updated court, update it in store
    if (data.data) {
      updateCourtInStore(id, data.data);
    }
    
    return data;
  },

  // Delete court
  async deleteCourt(id) {
    const { data } = await api.delete(`/admin/courts/${id}`);
    
    // Remove from store
    removeCourtFromStore(id);
    
    return data;
  }
};

export default courtService;