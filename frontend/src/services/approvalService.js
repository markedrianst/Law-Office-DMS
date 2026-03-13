// src/services/approvalService.js
import api from "@/services/api";

const approvalService = {

  async getApprovals(params = {}) {
    const { data } = await api.get("/admin/approvals", { params });
    return data;
  },

  // Get pending movement count for badge
  async getPendingCount() {
    const { data } = await api.get("/admin/approvals/pending-count");
    return data.count; // Returns number directly
  },

  // Approve or reject a movement
  async reviewMovement(type, movementId, status, notes = "") {
    const { data } = await api.patch(`/admin/approvals/${type}/${movementId}/approve`, {
      status,
      notes
    });
    return data;
  },
  
  // Get pending document approvals count
  async getPendingDocumentCount() {
    const { data } = await api.get("/admin/documents/pending-approvals");
    return data.data?.length || 0; // Keep this one because API returns array
  },

  // Get pending document approvals list
  async getPendingDocuments() {
    const { data } = await api.get("/admin/documents/pending-approvals");
    return data;
  },

  // Approve a document
  async approveDocument(documentId) {
    const { data } = await api.patch(`/admin/documents/${documentId}/approve`);
    return data;
  },

  // Reject a document
  async rejectDocument(documentId, payload) {
    const { data } = await api.patch(`/admin/documents/${documentId}/reject`, payload);
    return data;
  },

  // ========== COMBINED PENDING COUNT ==========
  
  // Get total pending approvals (movements + documents)
  async getTotalPendingCount() {
    const movementCount = await this.getPendingCount();
    const documentData = await this.getPendingDocumentCount();
    
    return {
      movements: movementCount,
      documents: documentData.data?.length || 0,
      total: movementCount + (documentData.data?.length || 0)
    };
  }
};

export default approvalService;