import api from "@/services/api";
import { 
  setCategories, 
  addCategory, 
  updateCategoryInStore, 
  removeCategoryFromStore,
  getCategories 
} from "@/utils/appUtils";

const caseCategoryService = {
  // Get all categories with filters
  async getCategories(params = {}) {
    const { data } = await api.get("/admin/case-categories", { params });
    
    // Store in appUtils
    if (data.data) {
      setCategories(data.data);
    }
    
    return data;
  },

  // Get active categories for dropdown
  async getActiveCategories() {
    const { data } = await api.get("/admin/case-categories/active");
    
    // Store in appUtils
    if (data.data) {
      setCategories(data.data);
    }
    
    return data;
  },

  // Get single category
  async getCategory(id) {
    const { data } = await api.get(`/admin/case-categories/${id}`);
    return data;
  },

  // Create new category
  async createCategory(payload) {
    const { data } = await api.post("/admin/case-categories", payload);
    
    // If API returns the created category, add it to store
    if (data.data) {
      addCategory(data.data);
    }
    
    return data;
  },

  // Update category
  async updateCategory(id, payload) {
    const { data } = await api.put(`/admin/case-categories/${id}`, payload);
    
    // If API returns the updated category, update it in store
    if (data.data) {
      updateCategoryInStore(id, data.data);
    }
    
    return data;
  },

  // Toggle category active status
  async toggleCategory(id) {
    const { data } = await api.patch(`/admin/case-categories/${id}/toggle`);
    
    // If API returns the updated category, update it in store
    if (data.data) {
      updateCategoryInStore(id, data.data);
    }
    
    return data;
  },

  // Delete category
  async deleteCategory(id) {
    const { data } = await api.delete(`/admin/case-categories/${id}`);
    
    // Remove from store
    removeCategoryFromStore(id);
    
    return data;
  }
};

export default caseCategoryService;