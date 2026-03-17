import api from "@/services/api";
import { 
  setDocuments, 
  addDocument, 
  updateDocumentInStore, 
  removeDocumentFromStore 
} from "@/utils/appUtils";

const documentService = {
  // Get all documents with filters
  async getDocuments(params = {}) {
    const { data } = await api.get("/admin/documents", { params });
    
    // Store in appUtils
    if (data.data) {
      setDocuments(data.data);
    }
    
    return data;
  },

  // Get active documents for dropdown
  async getActiveDocuments() {
    const { data } = await api.get("/admin/documents/active");
    
    // Store in appUtils
    if (data.data) {
      setDocuments(data.data);
    }
    
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
    
    // If API returns the created document, add it to store
    if (data.data) {
      addDocument(data.data);
    }
    
    return data;
  },

  // Update document
  async updateDocument(id, payload) {
    const { data } = await api.put(`/admin/documents/${id}`, payload);
    
    // If API returns the updated document, update it in store
    if (data.data) {
      updateDocumentInStore(id, data.data);
    }
    
    return data;
  },

  // Approve document (Lawyer only)
  async approveDocument(id) {
    const { data } = await api.patch(`/admin/documents/${id}/approve`);
    
    // If API returns the approved document, update it in store
    if (data.data) {
      updateDocumentInStore(id, data.data);
    }
    
    return data;
  },

  // Reject document (Lawyer only)
  async rejectDocument(id, payload) {
    const { data } = await api.patch(`/admin/documents/${id}/reject`, payload);
    
    // If API returns the rejected document, update it in store
    if (data.data) {
      updateDocumentInStore(id, data.data);
    }
    
    return data;
  },

  // Toggle document active status
  async toggleDocument(id) {
    const { data } = await api.patch(`/admin/documents/${id}/toggle`);
    
    // If API returns the toggled document, update it in store
    if (data.data) {
      updateDocumentInStore(id, data.data);
    }
    
    return data;
  },

  // Delete document
  async deleteDocument(id) {
    const { data } = await api.delete(`/admin/documents/${id}`);
    
    // Remove from store
    removeDocumentFromStore(id);
    
    return data;
  }
};

export default documentService;