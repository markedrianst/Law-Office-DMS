// frontend/src/services/documentService.js
import api from "@/services/api";

const documentService = {
  // Get all documents with filters
  async getDocuments(params = {}) {
    const { data } = await api.get("/admin/documents", { params });
    return data;
  },

  // Get active documents for dropdown
  async getActiveDocuments() {
    const { data } = await api.get("/admin/documents/active");
    return data;
  },

  // Get document categories for dropdown
  async getDocumentCategories() {
    const { data } = await api.get("/admin/documents/categories");
    return data;
  },

  // Get pending approvals (for lawyer dashboard)
  async getPendingApprovals() {
    const { data } = await api.get("/admin/documents/pending-approvals");
    return data;
  },

  // Get single document
  async getDocument(id) {
    const { data } = await api.get(`/admin/documents/${id}`);
    return data;
  },

  // Create new document
  async createDocument(payload) {
    const { data } = await api.post("/admin/documents", payload);
    return data;
  },

  // Update document
  async updateDocument(id, payload) {
    const { data } = await api.put(`/admin/documents/${id}`, payload);
    return data;
  },

  // Approve document (Lawyer only)
  async approveDocument(id) {
    const { data } = await api.patch(`/admin/documents/${id}/approve`);
    return data;
  },

  // Reject document (Lawyer only)
  async rejectDocument(id, payload) {
    const { data } = await api.patch(`/admin/documents/${id}/reject`, payload);
    return data;
  },

  // Toggle document active status
  async toggleDocument(id) {
    const { data } = await api.patch(`/admin/documents/${id}/toggle`);
    return data;
  },

  // Delete document
  async deleteDocument(id) {
    const { data } = await api.delete(`/admin/documents/${id}`);
    return data;
  }
};

export default documentService;