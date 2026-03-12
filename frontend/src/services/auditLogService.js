
import api from '@/services/api';

const auditLogService = {
  // Get system audit logs
  async getSystemLogs(params = {}) {
    const { data } = await api.get('/admin/audit-logs', { params });
    return data;
  },

  // Get case activity logs
  async getCaseLogs(params = {}) {
    const { data } = await api.get('/admin/audit-logs/case-activity', { params });
    return data;
  },

  // Get combined logs (both system and case)
  async getCombinedLogs(params = {}) {
    const { data } = await api.get('/admin/audit-logs/combined', { params });
    return data;
  },

  // Get single log by ID
  async getLogById(id) {
    const { data } = await api.get(`/admin/audit-logs/${id}`);
    return data;
  },

  // Get available actions for filter dropdown
  async getActions() {
    const { data } = await api.get('/admin/audit-logs/actions');
    return data;
  },

  // Get stats for dashboard
  async getStats() {
    const { data } = await api.get('/admin/audit-logs/stats');
    return data;
  },

  // Export logs
  async exportLogs(params = {}) {
    const response = await api.get('/admin/audit-logs/export', {
      params,
      responseType: 'blob'
    });
    return response;
  }
};

export default auditLogService;