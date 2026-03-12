// src/services/approvalService.js
import api from "@/services/api";

const approvalService = {
  // ========== APPROVALS ==========
  
  // Get all approvals with filters (status, type, direction, search)
  async getApprovals(params = {}) {
    const { data } = await api.get("/admin/approvals", { params });
    return data;
  },

  // Get pending count for badge
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

  // Get approval history for a specific case
  async getCaseApprovalHistory(caseId) {
    const { data } = await api.get(`/admin/approvals/case/${caseId}`);
    return data;
  },

  // ========== CONVENIENCE METHODS ==========
  
  // Approve checklist movement
  async approveChecklist(movementId, notes = "") {
    return this.reviewMovement("checklist", movementId, "APPROVED", notes);
  },

  // Reject checklist movement
  async rejectChecklist(movementId, notes) {
    return this.reviewMovement("checklist", movementId, "REJECTED", notes);
  },

  // Approve folder movement
  async approveFolder(movementId, notes = "") {
    return this.reviewMovement("folder", movementId, "APPROVED", notes);
  },

  // Reject folder movement
  async rejectFolder(movementId, notes) {
    return this.reviewMovement("folder", movementId, "REJECTED", notes);
  }
};

export default approvalService;