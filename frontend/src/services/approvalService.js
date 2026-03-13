// src/services/approvalService.js
import api from "@/services/api";

const approvalService = {
  // ========== MOVEMENT APPROVALS (Folder/Checklist) ==========
  
  // Get all movements with filters (status, type, direction, search)
  async getApprovals(params = {}) {
    const { data } = await api.get("/admin/approvals", { params });
    return data;
  },

  // Get pending movement count for badge
  async getPendingCount() {
    const { data } = await api.get("/admin/approvals/pending-count");
    return data.count ?? 0;
  },

  // Approve or reject a movement
  async reviewMovement(type, movementId, status, notes = "") {
    const { data } = await api.patch(`/admin/approvals/${type}/${movementId}/approve`, {
      status,
      notes
    });
    return data;
  },

  // ========== DOCUMENT APPROVALS ==========
  
  // Get pending document approvals count
  async getPendingDocumentCount() {
    try {
      const { data } = await api.get("/admin/documents/pending-approvals");
      return data.data?.length || 0;
    } catch (error) {
      console.error('Failed to fetch pending document count:', error);
      return 0;
    }
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
    try {
      const [movementCount, documentCount] = await Promise.all([
        this.getPendingCount().catch(() => 0),
        this.getPendingDocumentCount().catch(() => 0)
      ]);
      
      return {
        movements: movementCount,
        documents: documentCount,
        total: movementCount + documentCount
      };
    } catch (error) {
      console.error('Failed to get total pending count:', error);
      return { movements: 0, documents: 0, total: 0 };
    }
  }
};

export default approvalService;