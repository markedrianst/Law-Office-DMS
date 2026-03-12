// frontend/src/services/caseCategoryService.js
import api from "@/services/api";

const caseCategoryService = {
  // Get all categories with filters
  async getCategories(params = {}) {
    const { data } = await api.get("/admin/case-categories", { params });
    return data;
  },

  // Get active categories for dropdown
  async getActiveCategories() {
    const { data } = await api.get("/admin/case-categories/active");
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
    return data;
  },

  // Update category
  async updateCategory(id, payload) {
    const { data } = await api.put(`/admin/case-categories/${id}`, payload);
    return data;
  },

  // Toggle category active status
  async toggleCategory(id) {
    const { data } = await api.patch(`/admin/case-categories/${id}/toggle`);
    return data;
  },

  // Delete category
  async deleteCategory(id) {
    const { data } = await api.delete(`/admin/case-categories/${id}`);
    return data;
  }
};

export default caseCategoryService;