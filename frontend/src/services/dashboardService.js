// src/services/dashboardService.js

import api from "@/services/api";

const CACHE_TTL = 30000; // 30 seconds

class DashboardService {
  constructor() {
    this.pendingRequest = null;
    this.preloadComplete = false;
  }

  async getDashboardData(forceRefresh = false) {
    // Check cache first
    if (!forceRefresh) {
      const cached = this.getFromCache();
      if (cached) {
        return cached;
      }
    }

    // Prevent duplicate requests
    if (this.pendingRequest) {
      return this.pendingRequest;
    }

    this.pendingRequest = this.fetchFromApi();
    
    try {
      const data = await this.pendingRequest;
      this.preloadComplete = true;
      return data;
    } finally {
      this.pendingRequest = null;
    }
  }

  async fetchFromApi() {
    try {
      const { data } = await api.get('/dashboard');
      this.saveToCache(data);
      return data;
    } catch (error) {
      if (error.response?.status === 401) {
        throw error;
      }
      
      const cached = this.getFromCache(true);
      if (cached) {
        return cached;
      }
      
      throw error;
    }
  }

  getFromCache(ignoreExpiry = false) {
    try {
      const cached = sessionStorage.getItem('dashboard_cache');
      if (!cached) return null;
      
      const { data, timestamp } = JSON.parse(cached);
      
      if (ignoreExpiry || Date.now() - timestamp < CACHE_TTL) {
        return data;
      }
      
      return null;
    } catch (e) {
      return null;
    }
  }

  saveToCache(data) {
    try {
      sessionStorage.setItem('dashboard_cache', JSON.stringify({
        data,
        timestamp: Date.now()
      }));
    } catch (e) {}
  }

  isPreloaded() {
    return this.preloadComplete || !!this.getFromCache();
  }
}

export default new DashboardService();