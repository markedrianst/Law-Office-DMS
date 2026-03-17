import api from '@/services/api';
import { 
  setAuditLogs, 
  getAuditLogs, 
  setAuditStats, 
  getAuditStats,
  listenForUpdates
} from '@/utils/appUtils';

class AuditLogService {
  constructor() {
    // No cache needed - using appUtils instead
    console.log('📋 AuditLogService initialized');
  }

  async getCombinedLogs(params = {}) {
    try {
      console.log('📡 Fetching combined logs from API...');
      const { data } = await api.get('/admin/audit-logs/combined', { params });
      
      // Store in appUtils
      if (data.data) {
        setAuditLogs(data.data);
        console.log('✅ Audit logs stored in appUtils:', data.data.length);
      }
      
      return data;
    } catch (error) {
      console.error('Failed to fetch logs:', error);
      
      // Return cached data from appUtils if available
      const cachedLogs = getAuditLogs();
      if (cachedLogs.length > 0) {
        console.log('📋 Returning cached logs from appUtils');
        return { 
          data: cachedLogs, 
          meta: { total: cachedLogs.length } 
        };
      }
      
      return { data: [], meta: { total: 0 } };
    }
  }

  async getStats(forceRefresh = false) {
    try {
      console.log('📡 Fetching audit stats from API...');
      const { data } = await api.get('/admin/audit-logs/stats');
      
      // Store in appUtils
      if (data.data) {
        setAuditStats(data.data);
        console.log('✅ Audit stats stored in appUtils');
      }
      
      return data;
    } catch (error) {
      console.error('Failed to fetch stats:', error);
      
      // Return cached stats from appUtils
      const cachedStats = getAuditStats();
      return { data: cachedStats };
    }
  }

  async getSystemLogs(params = {}) {
    try {
      const { data } = await api.get('/admin/audit-logs', { params });
      
      // Update appUtils with system logs (merge with existing)
      const currentLogs = getAuditLogs();
      if (data.data) {
        const mergedLogs = [...data.data, ...currentLogs].slice(0, 100);
        setAuditLogs(mergedLogs);
      }
      
      return data;
    } catch (error) {
      console.error('Failed to fetch system logs:', error);
      return { data: [] };
    }
  }

  async getCaseLogs(params = {}) {
    try {
      const { data } = await api.get('/admin/audit-logs/case-activity', { params });
      
      // Update appUtils with case logs (merge with existing)
      const currentLogs = getAuditLogs();
      if (data.data) {
        const mergedLogs = [...data.data, ...currentLogs].slice(0, 100);
        setAuditLogs(mergedLogs);
      }
      
      return data;
    } catch (error) {
      console.error('Failed to fetch case logs:', error);
      return { data: [] };
    }
  }

  async getLogById(id) {
    try {
      const { data } = await api.get(`/admin/audit-logs/${id}`);
      return data;
    } catch (error) {
      console.error('Failed to fetch log by id:', error);
      throw error;
    }
  }

  async getActions() {
    try {
      const { data } = await api.get('/admin/audit-logs/actions');
      return data;
    } catch (error) {
      console.error('Failed to fetch actions:', error);
      return { data: [] };
    }
  }

  async exportLogs(params = {}) {
    try {
      const response = await api.get('/admin/audit-logs/export', {
        params,
        responseType: 'blob'
      });
      return response;
    } catch (error) {
      console.error('Failed to export logs:', error);
      throw error;
    }
  }

  // Clear appUtils cache
  clearCache() {
    console.log('🗑️ Clearing audit logs cache');
    import('@/utils/appUtils').then(({ clearAuditCache }) => {
      clearAuditCache();
    });
  }
}

export default new AuditLogService();