// src/services/auditLogService.js

import api from '@/services/api';

class AuditLogService {
  constructor() {
    this.cache = {
      stats: null,
      statsTimestamp: null,
      logs: new Map()
    };
    this.CACHE_TTL = 5000; // 5 seconds
  }

  async getCombinedLogs(params = {}) {
    const cacheKey = 'logs_' + JSON.stringify(params);
    
    // Check cache
    if (this.cache.logs.has(cacheKey)) {
      const cached = this.cache.logs.get(cacheKey);
      if (Date.now() - cached.timestamp < this.CACHE_TTL) {
        return cached.data;
      }
    }

    try {
      const { data } = await api.get('/admin/audit-logs/combined', { params });
      
      // Store in cache
      this.cache.logs.set(cacheKey, {
        data,
        timestamp: Date.now()
      });
      
      return data;
    } catch (error) {
      console.error('Failed to fetch logs:', error);
      return { data: [], meta: { total: 0 } };
    }
  }

  async getStats(forceRefresh = false) {
    if (!forceRefresh && this.cache.stats && this.cache.statsTimestamp) {
      if (Date.now() - this.cache.statsTimestamp < this.CACHE_TTL) {
        return this.cache.stats;
      }
    }

    try {
      const { data } = await api.get('/admin/audit-logs/stats');
      
      this.cache.stats = data;
      this.cache.statsTimestamp = Date.now();
      
      return data;
    } catch (error) {
      console.error('Failed to fetch stats:', error);
      return { data: { total_logs: 0, login_stats: { success: 0, failed: 0 } } };
    }
  }

  async getSystemLogs(params = {}) {
    const { data } = await api.get('/admin/audit-logs', { params });
    return data;
  }

  async getCaseLogs(params = {}) {
    const { data } = await api.get('/admin/audit-logs/case-activity', { params });
    return data;
  }

  async getLogById(id) {
    const { data } = await api.get(`/admin/audit-logs/${id}`);
    return data;
  }

  async getActions() {
    const { data } = await api.get('/admin/audit-logs/actions');
    return data;
  }

  async exportLogs(params = {}) {
    const response = await api.get('/admin/audit-logs/export', {
      params,
      responseType: 'blob'
    });
    return response;
  }

  clearCache() {
    this.cache.logs.clear();
    this.cache.stats = null;
    this.cache.statsTimestamp = null;
  }
}

export default new AuditLogService();