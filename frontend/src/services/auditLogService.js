import api from '@/services/api';
import cacheService from './cacheService'; // 👈 IMPORT CACHE

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

  // Get combined logs (both system and case) - WITH CACHE
  async getCombinedLogs(params = {}, forceRefresh = false) {
    // Try cache first (unless force refresh)
    if (!forceRefresh) {
      const cached = cacheService.getAuditLogs(params);
      if (cached) {
        console.log('📦 Using cached audit logs');
        return cached;
      }
    }
    
    const { data } = await api.get('/admin/audit-logs/combined', { params });
    
    // Cache the result
    if (data.data) {
      cacheService.setAuditLogs(data, params);
    }
    
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

  // Get stats for dashboard - WITH CACHE
  async getStats(forceRefresh = false) {
    // Try cache first
    if (!forceRefresh) {
      const cached = cacheService.getAuditStats();
      if (cached) {
        console.log('📦 Using cached audit stats');
        return cached;
      }
    }
    
    const { data } = await api.get('/admin/audit-logs/stats');
    
    // Cache the result
    if (data.data) {
      cacheService.setAuditStats(data);
    }
    
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