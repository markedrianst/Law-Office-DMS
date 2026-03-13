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
      USER_ROLES: 'cache_user_roles',
      
      // Dashboard
      DASHBOARD_ADMIN: 'cache_dashboard_admin',
      DASHBOARD_LAWYER: 'cache_dashboard_lawyer',
      DASHBOARD_CLERK: 'cache_dashboard_clerk',
      
      // Pending Counts
      PENDING_COUNTS: 'cache_pending_counts',
      
      // Recent Movements
      RECENT_MOVEMENTS: 'cache_recent_movements',
      
      // Audit Trail
      AUDIT_LOGS: 'cache_audit_logs',
      AUDIT_STATS: 'cache_audit_stats',
      
      // Cases
      CASES_LIST: 'cache_cases_list',
      
      // Approvals
      APPROVALS_LIST: 'cache_approvals_list'
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
      return false;
    }
  }

  get(key, allowStale = false) {
    try {
      const item = sessionStorage.getItem(key);
      if (!item) return null;
      
      const parsed = JSON.parse(item);
      
      if (!allowStale && parsed.expiresAt && Date.now() > parsed.expiresAt) {
        sessionStorage.removeItem(key);
        return null;
      }
      
      return parsed.data;
    } catch (e) {
      return null;
    }
  }

  getWithParams(key, params = {}, allowStale = false) {
    const cacheKey = key + '_' + JSON.stringify(params);
    return this.get(cacheKey, allowStale);
  }

  setWithParams(key, params = {}, data, ttl = this.CACHE_TTL) {
    const cacheKey = key + '_' + JSON.stringify(params);
    return this.set(cacheKey, data, ttl);
  }

  remove(key) {
    sessionStorage.removeItem(key);
  }

  removeWithPattern(pattern) {
    const keys = Object.keys(sessionStorage);
    keys.forEach(key => {
      if (key.startsWith(pattern)) {
        sessionStorage.removeItem(key);
      }
    });
  }

  clearAll() {
    Object.values(this.CACHE_KEYS).forEach(key => {
      if (typeof key === 'string') {
        sessionStorage.removeItem(key);
      }
    });
    this.removeWithPattern('cache_');
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

  setUserRoles(roles) {
    this.set(this.CACHE_KEYS.USER_ROLES, roles, 60 * 60 * 1000);
  }
  
  getUserRoles() {
    return this.get(this.CACHE_KEYS.USER_ROLES, true) || [];
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

  // ========== AUDIT TRAIL METHODS ==========
  
  setAuditLogs(data, params = {}) {
    this.setWithParams(this.CACHE_KEYS.AUDIT_LOGS, params, data, 2 * 60 * 1000);
  }

  getAuditLogs(params = {}) {
    return this.getWithParams(this.CACHE_KEYS.AUDIT_LOGS, params, true);
  }

  setAuditStats(stats) {
    this.set(this.CACHE_KEYS.AUDIT_STATS, stats, 5 * 60 * 1000);
  }

  getAuditStats() {
    return this.get(this.CACHE_KEYS.AUDIT_STATS, true);
  }

  // ========== CASES METHODS ==========
  
  setCasesList(data, params = {}) {
    this.setWithParams(this.CACHE_KEYS.CASES_LIST, params, data, 2 * 60 * 1000);
  }

  getCasesList(params = {}) {
    return this.getWithParams(this.CACHE_KEYS.CASES_LIST, params, true);
  }

  // ========== APPROVALS METHODS ==========
  
  setApprovalsList(data, params = {}) {
    this.setWithParams(this.CACHE_KEYS.APPROVALS_LIST, params, data, 2 * 60 * 1000);
  }

  getApprovalsList(params = {}) {
    return this.getWithParams(this.CACHE_KEYS.APPROVALS_LIST, params, true);
  }

  // ========== INVALIDATION METHODS ==========
  
  invalidateDashboardCache() {
    sessionStorage.removeItem(this.CACHE_KEYS.DASHBOARD_ADMIN);
    sessionStorage.removeItem(this.CACHE_KEYS.DASHBOARD_LAWYER);
    sessionStorage.removeItem(this.CACHE_KEYS.DASHBOARD_CLERK);
    sessionStorage.removeItem(this.CACHE_KEYS.PENDING_COUNTS);
    sessionStorage.removeItem(this.CACHE_KEYS.RECENT_MOVEMENTS);
    sessionStorage.removeItem(this.CACHE_KEYS.USERS);
    sessionStorage.removeItem(this.CACHE_KEYS.CLIENTS);
  }

  invalidateUserCache() {
    sessionStorage.removeItem(this.CACHE_KEYS.USERS);
    sessionStorage.removeItem(this.CACHE_KEYS.USER_ROLES);
    sessionStorage.removeItem(this.CACHE_KEYS.DASHBOARD_ADMIN);
    sessionStorage.removeItem(this.CACHE_KEYS.DASHBOARD_LAWYER);
    sessionStorage.removeItem(this.CACHE_KEYS.DASHBOARD_CLERK);
  }

  invalidateAuditCache() {
    this.removeWithPattern(this.CACHE_KEYS.AUDIT_LOGS);
    sessionStorage.removeItem(this.CACHE_KEYS.AUDIT_STATS);
  }

  invalidateCasesCache() {
    this.removeWithPattern(this.CACHE_KEYS.CASES_LIST);
  }

  invalidateApprovalsCache() {
    this.removeWithPattern(this.CACHE_KEYS.APPROVALS_LIST);
  }

  // ========== REFRESH METHODS ==========
  
  async refreshDashboardCache(role, fetchFunction) {
    try {
      const newData = await fetchFunction();
      
      if (role === 'admin') {
        this.setAdminDashboard(newData);
      } else if (role === 'lawyer') {
        this.setLawyerDashboard(newData);
      } else if (role === 'clerk') {
        this.setClerkDashboard(newData);
      }
      
      return newData;
    } catch (error) {
      return null;
    }
  }

  // ========== UTILITY METHODS ==========
  
  clearExpired() {
    const keys = Object.keys(sessionStorage);
    const now = Date.now();
    
    keys.forEach(key => {
      try {
        const item = sessionStorage.getItem(key);
        if (!item) return;
        
        const parsed = JSON.parse(item);
        if (parsed.expiresAt && now > parsed.expiresAt) {
          sessionStorage.removeItem(key);
        }
      } catch (e) {}
    });
  }

  getCacheSize() {
    let total = 0;
    const keys = Object.keys(sessionStorage);
    
    keys.forEach(key => {
      const item = sessionStorage.getItem(key);
      if (item) {
        total += item.length;
      }
    });
    
    return (total / 1024).toFixed(2) + ' KB';
  }

  hasKey(key) {
    return sessionStorage.getItem(key) !== null;
  }
}

export default new CacheService();