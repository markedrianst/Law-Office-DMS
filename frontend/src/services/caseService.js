// frontend/src/services/caseService.js
import api from "@/services/api";

const caseService = {
  // ========== CASES ==========
  
  // Get all cases with filters
  async getCases(params = {}) {
    const { data } = await api.get("/admin/cases", { params });
    return data;
  },

  // Get lookup data for case forms
  async getLookups() {
    const { data } = await api.get("/admin/case-lookups");
    return data;
  },

  // Get single case WITH ALL RELATED DATA (checklists, trackers, logs)
  async getCase(id) {
    const { data } = await api.get(`/admin/cases/${id}`);
    return data;
  },

  // Create new case
  async createCase(payload) {
    const { data } = await api.post("/admin/cases", payload);
    return data;
  },

  // Update case
  async updateCase(id, payload) {
    const { data } = await api.put(`/admin/cases/${id}`, payload);
    return data;
  },

  // Delete case
  async deleteCase(id) {
    const { data } = await api.delete(`/admin/cases/${id}`);
    return data;
  },

  // Archive case
  async archiveCase(id) {
    const { data } = await api.patch(`/admin/cases/${id}/archive`);
    return data;
  },

  // Get case activity logs
  async getActivityLogs(caseId, params = {}) {
    const { data } = await api.get(`/admin/cases/${caseId}/activity-logs`, { params });
    return data;
  },

  // Export cases
  async exportCases(format = 'xlsx', params = {}) {
    const response = await api.get('/admin/cases/export', {
      params: { format, ...params },
      responseType: 'blob'
    });
    return response;
  },

  // ========== CASE STAGE ==========

  // Get stage history
  async getStageHistory(caseId) {
    const { data } = await api.get(`/admin/cases/${caseId}/stages/history`);
    return data;
  },

  // Update case stage
  async updateStage(caseId, payload) {
    const { data } = await api.put(`/admin/cases/${caseId}/stage`, payload);
    return data;
  },

  // ========== CASE CHECKLIST ==========

  // Get all checklist items for a case
  async getChecklist(caseId) {
    const { data } = await api.get(`/admin/cases/${caseId}/checklist`);
    return data;
  },

  // Create checklist task
  async createChecklistTask(caseId, payload) {
    const { data } = await api.post(`/admin/cases/${caseId}/checklist`, payload);
    return data;
  },

  // Update checklist task
  async updateChecklistTask(caseId, taskId, payload) {
    const { data } = await api.put(`/admin/cases/${caseId}/checklist/${taskId}`, payload);
    return data;
  },

  // Delete checklist task
  async deleteChecklistTask(caseId, taskId) {
    const { data } = await api.delete(`/admin/cases/${caseId}/checklist/${taskId}`);
    return data;
  },

  // Update task status only
  async updateChecklistTaskStatus(caseId, taskId, status) {
    const { data } = await api.patch(`/admin/cases/${caseId}/checklist/${taskId}/status`, { status });
    return data;
  },

  // ========== FOLDER TRACKER ==========

  // Get all folder movements for a case
  async getFolderTracker(caseId) {
    const { data } = await api.get(`/admin/cases/${caseId}/folder-tracker`);
    return data;
  },

  // Create folder tracker entry
  async createFolderTrackerEntry(caseId, payload) {
    const { data } = await api.post(`/admin/cases/${caseId}/folder-tracker`, payload);
    return data;
  },

  // Get pending folder movements
  async getPendingFolderMovements(caseId) {
    const { data } = await api.get(`/admin/cases/${caseId}/folder-tracker/pending`);
    return data;
  },

  // Approve or reject folder movement
  async approveFolderMovement(caseId, movementId, approval_status) {
    const { data } = await api.patch(`/admin/cases/${caseId}/folder-tracker/${movementId}/approve`, { approval_status });
    return data;
  },

  // ========== CHECKLIST TRACKER ==========

  // Get all checklist movements for a case
  async getChecklistTracker(caseId) {
    const { data } = await api.get(`/admin/cases/${caseId}/checklist-tracker`);
    return data;
  },

  // Create checklist tracker entry
  async createChecklistTrackerEntry(caseId, payload) {
    const { data } = await api.post(`/admin/cases/${caseId}/checklist-tracker`, payload);
    return data;
  },

  // Get pending checklist movements
  async getPendingChecklistMovements(caseId) {
    const { data } = await api.get(`/admin/cases/${caseId}/checklist-tracker/pending`);
    return data;
  },

  // Approve or reject checklist movement
  async approveChecklistMovement(caseId, movementId, approval_status) {
    const { data } = await api.patch(`/admin/cases/${caseId}/checklist-tracker/${movementId}/approve`, { approval_status });
    return data;
  }
};

export default caseService;