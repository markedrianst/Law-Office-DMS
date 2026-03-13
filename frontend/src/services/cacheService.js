// src/services/cacheService.js
class CacheService {
  constructor() {
    this.CACHE_KEYS = {
      // Master Data
      CATEGORIES: 'cache_categories',
      STAGES: 'cache_stages',
      COURTS: 'cache_courts',
      DOCUMENTS: 'cache_documents',
      USERS: 'cache_users',
      CLIENTS: 'cache_clients',
      
      // Dashboard
      DASHBOARD_ADMIN: 'cache_dashboard_admin',
      DASHBOARD_LAWYER: 'cache_dashboard_lawyer',
      DASHBOARD_CLERK: 'cache_dashboard_clerk',
      
      // Pending Counts
      PENDING_COUNTS: 'cache_pending_counts',
      
      // Recent Movements
      RECENT_MOVEMENTS: 'cache_recent_movements'
    };
    
    this.CACHE_TTL = 5 * 60 * 1000; // 5 minutes
  }

  // ========== CORE METHODS ==========
  
  set(key, data, ttl = this.CACHE_TTL) {
    try {
      const cacheItem = {
        data,
        timestamp: Date.now(),
        expiresAt: Date.now() + ttl
      };
      sessionStorage.setItem(key, JSON.stringify(cacheItem));
      return true;
    } catch (e) {
      console.warn('Cache write failed:', e);
      return false;
    }
  }

  get(key, allowStale = false) {
    try {
      const item = sessionStorage.getItem(key);
      if (!item) return null;
      
      const parsed = JSON.parse(item);
      
      // Check if expired
      if (!allowStale && parsed.expiresAt && Date.now() > parsed.expiresAt) {
        sessionStorage.removeItem(key);
        return null;
      }
      
      return parsed.data;
    } catch (e) {
      return null;
    }
  }

  remove(key) {
    sessionStorage.removeItem(key);
  }

  clearAll() {
    // Clear ALL cache keys (for logout)
    Object.values(this.CACHE_KEYS).forEach(key => {
      sessionStorage.removeItem(key);
    });
  }

  // ========== MASTER DATA METHODS ==========
  
  setCategories(categories) {
    this.set(this.CACHE_KEYS.CATEGORIES, categories);
  }
  
  getCategories() {
    return this.get(this.CACHE_KEYS.CATEGORIES, true) || [];
  }

  setStages(stages) {
    this.set(this.CACHE_KEYS.STAGES, stages);
  }
  
  getStages() {
    return this.get(this.CACHE_KEYS.STAGES, true) || [];
  }

  setCourts(courts) {
    this.set(this.CACHE_KEYS.COURTS, courts);
  }
  
  getCourts() {
    return this.get(this.CACHE_KEYS.COURTS, true) || [];
  }

  setDocuments(documents) {
    this.set(this.CACHE_KEYS.DOCUMENTS, documents);
  }
  
  getDocuments() {
    return this.get(this.CACHE_KEYS.DOCUMENTS, true) || [];
  }

  setUsers(users) {
    this.set(this.CACHE_KEYS.USERS, users);
  }
  
  getUsers() {
    return this.get(this.CACHE_KEYS.USERS, true) || [];
  }

  setClients(clients) {
    this.set(this.CACHE_KEYS.CLIENTS, clients);
  }
  
  getClients() {
    return this.get(this.CACHE_KEYS.CLIENTS, true) || [];
  }

  // ========== DASHBOARD METHODS ==========
  
  setAdminDashboard(data) {
    this.set(this.CACHE_KEYS.DASHBOARD_ADMIN, data);
  }

  getAdminDashboard() {
    return this.get(this.CACHE_KEYS.DASHBOARD_ADMIN, true);
  }

  setLawyerDashboard(data) {
    this.set(this.CACHE_KEYS.DASHBOARD_LAWYER, data);
  }

  getLawyerDashboard() {
    return this.get(this.CACHE_KEYS.DASHBOARD_LAWYER, true);
  }

  setClerkDashboard(data) {
    this.set(this.CACHE_KEYS.DASHBOARD_CLERK, data);
  }

  getClerkDashboard() {
    return this.get(this.CACHE_KEYS.DASHBOARD_CLERK, true);
  }

  // ========== PENDING COUNTS ==========
  
  setPendingCounts(counts) {
    this.set(this.CACHE_KEYS.PENDING_COUNTS, counts);
  }

  getPendingCounts() {
    return this.get(this.CACHE_KEYS.PENDING_COUNTS, true) || { documents: 0, movements: 0, total: 0 };
  }

  // ========== RECENT MOVEMENTS ==========
  
  setRecentMovements(movements) {
    this.set(this.CACHE_KEYS.RECENT_MOVEMENTS, movements);
  }

  getRecentMovements() {
    return this.get(this.CACHE_KEYS.RECENT_MOVEMENTS, true) || [];
  }
}

export default new CacheService();