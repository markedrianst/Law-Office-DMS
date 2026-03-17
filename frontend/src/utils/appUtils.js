// =====================================================================
// MASTER UTILITY FILE - Complete with CRUD operations and real-time events
// Zero dependencies, pure vanilla JS, instant updates across components
// =====================================================================

const dataStore = {
  // User data
  user: null,
  
  // Dashboard data
  dashboard: null,
  
  // Master data
  categories: null,
  stages: null,
  courts: null,
  documents: null,
  users: null,
  clients: null,
  cases: null,
  approvals: null,
  approvalStats: null,
  auditLogs: null,
  auditStats: null,
  hearings: [],
  hearingStats: {
    today: 0,
    tomorrow: 0,
    this_week: 0,
    this_month: 0,
    upcoming: 0,
    past: 0,
    by_type: {}
  },
  
  // Notifications
  notifications: null,
  unreadCount: 0,
  
  // Timestamp
  timestamp: null,
};

// ==================== EVENT DISPATCHER ====================
const dispatchEvent = (eventName, detail) => {
  if (typeof window !== 'undefined') {
    window.dispatchEvent(new CustomEvent(eventName, { detail }));
  }
};

// ==================== USER METHODS ====================
export const setUser = (user) => {
  dataStore.user = user;
  dataStore.timestamp = Date.now();
  if (user) {
    sessionStorage.setItem('user', JSON.stringify(user));
  }
  dispatchEvent('user-updated', user);
};

export const getUser = () => dataStore.user;

export const getUserName = () => dataStore.user?.full_name || 'User';

export const getUserRole = () => {
  const role = dataStore.user?.role?.name || dataStore.user?.role;
  return role?.toLowerCase() || 'user';
};

export const getUserInitials = () => {
  const name = getUserName();
  if (!name || name === 'User') return 'U';
  const parts = name.split(' ').filter(Boolean);
  if (parts.length === 1) return parts[0][0].toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
};

export const isAuthenticated = () => !!sessionStorage.getItem('token');

// ==================== DASHBOARD METHODS ====================
export const setDashboard = (dashboard) => {
  dataStore.dashboard = dashboard;
  dataStore.timestamp = Date.now();
  dispatchEvent('dashboard-updated', dashboard);
};

export const getDashboard = () => dataStore.dashboard;

export const updateDashboardStats = (stats) => {
  if (dataStore.dashboard) {
    dataStore.dashboard = { ...dataStore.dashboard, ...stats };
    dataStore.timestamp = Date.now();
    dispatchEvent('dashboard-updated', dataStore.dashboard);
  }
};

// ==================== USERS METHODS ====================
export const setUsers = (users) => {
  dataStore.users = users;
  dataStore.timestamp = Date.now();
  dispatchEvent('users-updated', users);
};

export const getUserNameById = (id) => {
  if (!id || !dataStore.users) return '—';
  const found = dataStore.users.find(u => u.id === id);
  return found?.full_name || found?.name || '—';
};

export const getUsers = () => dataStore.users || [];

export const addUser = (user) => {
  if (!dataStore.users) dataStore.users = [];
  dataStore.users.unshift(user);
  dataStore.timestamp = Date.now();
  dispatchEvent('users-updated', dataStore.users);
  
  if (dataStore.dashboard?.adminStats) {
    updateDashboardStats({
      adminStats: {
        ...dataStore.dashboard.adminStats,
        total_users: (dataStore.dashboard.adminStats.total_users || 0) + 1,
        [user.role?.toLowerCase() === 'lawyer' ? 'lawyers' : 'clerks']: 
          (dataStore.dashboard.adminStats[user.role?.toLowerCase() === 'lawyer' ? 'lawyers' : 'clerks'] || 0) + 1
      }
    });
  }
};

export const updateUserInStore = (id, updatedUser) => {
  if (!dataStore.users) return;
  const index = dataStore.users.findIndex(u => u.id === id);
  if (index !== -1) {
    const oldRole = dataStore.users[index].role;
    const newRole = updatedUser.role;
    
    dataStore.users[index] = { ...dataStore.users[index], ...updatedUser };
    dataStore.timestamp = Date.now();
    dispatchEvent('users-updated', dataStore.users);
    
    if (oldRole !== newRole && dataStore.dashboard?.adminStats) {
      const stats = { ...dataStore.dashboard.adminStats };
      if (oldRole?.toLowerCase() === 'lawyer') stats.lawyers = Math.max(0, stats.lawyers - 1);
      if (oldRole?.toLowerCase() === 'clerk') stats.clerks = Math.max(0, stats.clerks - 1);
      if (newRole?.toLowerCase() === 'lawyer') stats.lawyers = (stats.lawyers || 0) + 1;
      if (newRole?.toLowerCase() === 'clerk') stats.clerks = (stats.clerks || 0) + 1;
      
      updateDashboardStats({ adminStats: stats });
    }
  }
};

export const removeUserFromStore = (id) => {
  if (!dataStore.users) return;
  const removedUser = dataStore.users.find(u => u.id === id);
  dataStore.users = dataStore.users.filter(u => u.id !== id);
  dataStore.timestamp = Date.now();
  dispatchEvent('users-updated', dataStore.users);
  
  if (removedUser && dataStore.dashboard?.adminStats) {
    const stats = { ...dataStore.dashboard.adminStats };
    stats.total_users = Math.max(0, stats.total_users - 1);
    if (removedUser.role?.toLowerCase() === 'lawyer') stats.lawyers = Math.max(0, stats.lawyers - 1);
    if (removedUser.role?.toLowerCase() === 'clerk') stats.clerks = Math.max(0, stats.clerks - 1);
    
    updateDashboardStats({ adminStats: stats });
  }
};

export const getUserById = (id) => {
  if (!dataStore.users) return null;
  return dataStore.users.find(u => u.id === id);
};

export const getLawyers = () => {
  if (!dataStore.users) return [];
  return dataStore.users.filter(u => u.role?.toLowerCase() === 'lawyer');
};

export const getClerks = () => {
  if (!dataStore.users) return [];
  return dataStore.users.filter(u => u.role?.toLowerCase() === 'clerk');
};

// ==================== CATEGORIES METHODS ====================
export const setCategories = (categories) => {
  dataStore.categories = categories;
  dataStore.timestamp = Date.now();
  dispatchEvent('categories-updated', categories);
};

export const getCategories = () => dataStore.categories || [];

export const addCategory = (category) => {
  if (!dataStore.categories) dataStore.categories = [];
  dataStore.categories.unshift(category);
  dataStore.timestamp = Date.now();
  dispatchEvent('categories-updated', dataStore.categories);
};

export const updateCategoryInStore = (id, updatedCategory) => {
  if (!dataStore.categories) return;
  const index = dataStore.categories.findIndex(c => c.id === id);
  if (index !== -1) {
    dataStore.categories[index] = { ...dataStore.categories[index], ...updatedCategory };
    dataStore.timestamp = Date.now();
    dispatchEvent('categories-updated', dataStore.categories);
  }
};

export const removeCategoryFromStore = (id) => {
  if (!dataStore.categories) return;
  dataStore.categories = dataStore.categories.filter(c => c.id !== id);
  dataStore.timestamp = Date.now();
  dispatchEvent('categories-updated', dataStore.categories);
};

export const getCategoryName = (id) => {
  if (!id || !dataStore.categories) return '—';
  const found = dataStore.categories.find(c => c.id === id);
  return found?.name || '—';
};

export const getCategoryColor = (id) => {
  if (!id || !dataStore.categories) return '#1a4972';
  const found = dataStore.categories.find(c => c.id === id);
  return found?.color || '#1a4972';
};

// ==================== STAGES METHODS ====================
export const setStages = (stages) => {
  dataStore.stages = stages;
  dataStore.timestamp = Date.now();
  dispatchEvent('stages-updated', stages);
};

export const getStages = () => dataStore.stages || [];

export const getStageName = (id) => {
  if (!id || !dataStore.stages) return '—';
  const found = dataStore.stages.find(s => s.id === id);
  return found?.name || '—';
};

export const getStageColor = (id) => {
  if (!id || !dataStore.stages) return '#64748b';
  const found = dataStore.stages.find(s => s.id === id);
  return found?.color || '#64748b';
};

// ==================== COURTS METHODS ====================
export const setCourts = (courts) => {
  dataStore.courts = courts;
  dataStore.timestamp = Date.now();
  dispatchEvent('courts-updated', courts);
};

export const getCourts = () => dataStore.courts || [];

export const addCourt = (court) => {
  if (!dataStore.courts) dataStore.courts = [];
  dataStore.courts.unshift(court);
  dataStore.timestamp = Date.now();
  dispatchEvent('courts-updated', dataStore.courts);
};

export const updateCourtInStore = (id, updatedCourt) => {
  if (!dataStore.courts) return;
  const index = dataStore.courts.findIndex(c => c.id === id);
  if (index !== -1) {
    dataStore.courts[index] = { ...dataStore.courts[index], ...updatedCourt };
    dataStore.timestamp = Date.now();
    dispatchEvent('courts-updated', dataStore.courts);
  }
};

export const removeCourtFromStore = (id) => {
  if (!dataStore.courts) return;
  dataStore.courts = dataStore.courts.filter(c => c.id !== id);
  dataStore.timestamp = Date.now();
  dispatchEvent('courts-updated', dataStore.courts);
};

export const getCourtName = (id) => {
  if (!id || !dataStore.courts) return '—';
  const found = dataStore.courts.find(c => c.id === id);
  return found?.name || '—';
};

// ==================== DOCUMENTS METHODS ====================
export const setDocuments = (documents) => {
  dataStore.documents = documents;
  dataStore.timestamp = Date.now();
  dispatchEvent('documents-updated', documents);
};

export const getDocuments = () => dataStore.documents || [];

export const addDocument = (document) => {
  if (!dataStore.documents) dataStore.documents = [];
  dataStore.documents.unshift(document);
  dataStore.timestamp = Date.now();
  dispatchEvent('documents-updated', dataStore.documents);
};

export const updateDocumentInStore = (id, updatedDocument) => {
  if (!dataStore.documents) return;
  const index = dataStore.documents.findIndex(d => d.id === id);
  if (index !== -1) {
    dataStore.documents[index] = { ...dataStore.documents[index], ...updatedDocument };
    dataStore.timestamp = Date.now();
    dispatchEvent('documents-updated', dataStore.documents);
  }
};

export const removeDocumentFromStore = (id) => {
  if (!dataStore.documents) return;
  dataStore.documents = dataStore.documents.filter(d => d.id !== id);
  dataStore.timestamp = Date.now();
  dispatchEvent('documents-updated', dataStore.documents);
};

export const getDocumentType = (id) => {
  if (!id || !dataStore.documents) return '—';
  const found = dataStore.documents.find(d => d.id === id);
  return found?.type || '—';
};

export const getDocumentColor = (id) => {
  if (!id || !dataStore.documents) return '#94a3b8';
  const found = dataStore.documents.find(d => d.id === id);
  return found?.color || '#94a3b8';
};

// ==================== CLIENTS METHODS ====================
export const setClients = (clients) => {
  dataStore.clients = clients;
  dataStore.timestamp = Date.now();
  dispatchEvent('clients-updated', clients);
};

export const getClients = () => dataStore.clients || [];

export const addClient = (client) => {
  if (!dataStore.clients) dataStore.clients = [];
  dataStore.clients.unshift(client);
  dataStore.timestamp = Date.now();
  dispatchEvent('clients-updated', dataStore.clients);
  
  if (dataStore.dashboard?.stats) {
    updateDashboardStats({
      stats: {
        ...dataStore.dashboard.stats,
        total_clients: (dataStore.dashboard.stats.total_clients || 0) + 1
      }
    });
  }
};

export const updateClientInStore = (id, updatedClient) => {
  if (!dataStore.clients) return;
  const index = dataStore.clients.findIndex(c => c.id === id);
  if (index !== -1) {
    dataStore.clients[index] = { ...dataStore.clients[index], ...updatedClient };
    dataStore.timestamp = Date.now();
    dispatchEvent('clients-updated', dataStore.clients);
  }
};

export const removeClientFromStore = (id) => {
  if (!dataStore.clients) return;
  const removedClient = dataStore.clients.find(c => c.id === id);
  dataStore.clients = dataStore.clients.filter(c => c.id !== id);
  dataStore.timestamp = Date.now();
  dispatchEvent('clients-updated', dataStore.clients);
  
  if (removedClient && dataStore.dashboard?.stats) {
    updateDashboardStats({
      stats: {
        ...dataStore.dashboard.stats,
        total_clients: Math.max(0, dataStore.dashboard.stats.total_clients - 1)
      }
    });
  }
};

export const getClientById = (id) => {
  if (!dataStore.clients) return null;
  return dataStore.clients.find(c => c.id === id);
};

export const getClientName = (id) => {
  if (!id || !dataStore.clients) return '—';
  const found = dataStore.clients.find(c => c.id === id);
  return found?.full_name || '—';
};

// ==================== NOTIFICATIONS METHODS ====================
export const setNotifications = (notifications) => {
  dataStore.notifications = notifications;
  dataStore.unreadCount = notifications?.filter(n => !n.is_read).length || 0;
  dataStore.timestamp = Date.now();
  dispatchEvent('notifications-updated', { notifications, unreadCount: dataStore.unreadCount });
};

export const getNotifications = () => dataStore.notifications || [];

export const getUnreadCount = () => dataStore.unreadCount;

export const addNotification = (notification) => {
  if (!dataStore.notifications) dataStore.notifications = [];
  dataStore.notifications.unshift(notification);
  if (!notification.is_read) dataStore.unreadCount++;
  if (dataStore.notifications.length > 50) dataStore.notifications = dataStore.notifications.slice(0, 50);
  dataStore.timestamp = Date.now();
  dispatchEvent('notifications-updated', { notifications: dataStore.notifications, unreadCount: dataStore.unreadCount });
};

export const markNotificationAsRead = (id) => {
  if (!dataStore.notifications) return;
  const index = dataStore.notifications.findIndex(n => n.id === id);
  if (index !== -1 && !dataStore.notifications[index].is_read) {
    dataStore.notifications[index].is_read = true;
    dataStore.unreadCount = Math.max(0, dataStore.unreadCount - 1);
    dataStore.timestamp = Date.now();
    dispatchEvent('notifications-updated', { notifications: dataStore.notifications, unreadCount: dataStore.unreadCount });
  }
};

export const markAllNotificationsAsRead = () => {
  if (!dataStore.notifications) return;
  dataStore.notifications.forEach(n => n.is_read = true);
  dataStore.unreadCount = 0;
  dataStore.timestamp = Date.now();
  dispatchEvent('notifications-updated', { notifications: dataStore.notifications, unreadCount: 0 });
};

export const updateNotificationInStore = (id, updatedNotification) => {
  if (!dataStore.notifications) return;
  const index = dataStore.notifications.findIndex(n => n.id === id);
  if (index !== -1) {
    dataStore.notifications[index] = { ...dataStore.notifications[index], ...updatedNotification };
    dataStore.unreadCount = dataStore.notifications.filter(n => !n.is_read).length;
    dataStore.timestamp = Date.now();
    dispatchEvent('notifications-updated', { notifications: dataStore.notifications, unreadCount: dataStore.unreadCount });
  }
};

// ==================== CASES METHODS ====================
export const setCases = (cases) => {
  dataStore.cases = cases;
  dataStore.timestamp = Date.now();
  dispatchEvent('cases-updated', cases);
};

export const getCases = () => dataStore.cases || [];

export const addCase = (caseItem) => {
  if (!dataStore.cases) dataStore.cases = [];
  dataStore.cases.unshift(caseItem);
  dataStore.timestamp = Date.now();
  dispatchEvent('cases-updated', dataStore.cases);
  
  if (dataStore.dashboard?.stats) {
    updateDashboardStats({
      stats: {
        ...dataStore.dashboard.stats,
        total_cases: (dataStore.dashboard.stats.total_cases || 0) + 1,
        active_cases: caseItem.case_status === 'active' 
          ? (dataStore.dashboard.stats.active_cases || 0) + 1 
          : dataStore.dashboard.stats.active_cases || 0
      }
    });
  }
};

export const updateCaseInStore = (id, updatedCase) => {
  if (!dataStore.cases) return;
  const index = dataStore.cases.findIndex(c => c.id === id);
  if (index !== -1) {
    const oldStatus = dataStore.cases[index].case_status;
    dataStore.cases[index] = { ...dataStore.cases[index], ...updatedCase };
    dataStore.timestamp = Date.now();
    dispatchEvent('cases-updated', dataStore.cases);
    
    if (oldStatus !== updatedCase.case_status && dataStore.dashboard?.stats) {
      const stats = { ...dataStore.dashboard.stats };
      if (oldStatus === 'active') stats.active_cases = Math.max(0, stats.active_cases - 1);
      if (updatedCase.case_status === 'active') stats.active_cases = (stats.active_cases || 0) + 1;
      updateDashboardStats({ stats });
    }
  }
};

export const removeCaseFromStore = (id) => {
  if (!dataStore.cases) return;
  const removedCase = dataStore.cases.find(c => c.id === id);
  dataStore.cases = dataStore.cases.filter(c => c.id !== id);
  dataStore.timestamp = Date.now();
  dispatchEvent('cases-updated', dataStore.cases);
  
  if (removedCase && dataStore.dashboard?.stats) {
    const stats = { ...dataStore.dashboard.stats };
    stats.total_cases = Math.max(0, stats.total_cases - 1);
    if (removedCase.case_status === 'active') stats.active_cases = Math.max(0, stats.active_cases - 1);
    updateDashboardStats({ stats });
  }
};

export const getCaseById = (id) => {
  if (!dataStore.cases) return null;
  return dataStore.cases.find(c => c.id === id);
};

export const getCasesByStatus = (status) => {
  if (!dataStore.cases) return [];
  return dataStore.cases.filter(c => c.case_status === status);
};

export const getCasesByLawyer = (lawyerId) => {
  if (!dataStore.cases) return [];
  return dataStore.cases.filter(c => c.assigned_lawyer_id === lawyerId);
};

export const getCasesByClerk = (clerkId) => {
  if (!dataStore.cases) return [];
  return dataStore.cases.filter(c => c.assigned_clerk_id === clerkId);
};

export const getCaseCode = (id) => {
  if (!id || !dataStore.cases) return '—';
  const found = dataStore.cases.find(c => c.id === id);
  return found?.case_code || '—';
};

export const getCaseTitle = (id) => {
  if (!id || !dataStore.cases) return '—';
  const found = dataStore.cases.find(c => c.id === id);
  return found?.title || '—';
};

// ==================== APPROVALS METHODS ====================
export const setApprovals = (approvals) => {
  dataStore.approvals = approvals;
  dataStore.timestamp = Date.now();
  dispatchEvent('approvals-updated', approvals);
  
  const stats = {
    total: approvals.length,
    pending: approvals.filter(a => a.approval_status === 'PENDING').length,
    approved: approvals.filter(a => a.approval_status === 'APPROVED').length,
    rejected: approvals.filter(a => a.approval_status === 'REJECTED').length
  };
  setApprovalStats(stats);
};

export const getApprovals = () => dataStore.approvals || [];

export const setApprovalStats = (stats) => {
  dataStore.approvalStats = stats;
  dataStore.timestamp = Date.now();
  dispatchEvent('approval-stats-updated', stats);
};

export const getApprovalStats = () => dataStore.approvalStats || { total: 0, pending: 0, approved: 0, rejected: 0 };

export const updateApprovalInStore = (id, updatedApproval) => {
  if (!dataStore.approvals) return;
  const index = dataStore.approvals.findIndex(a => a.id === id);
  if (index !== -1) {
    dataStore.approvals[index] = { ...dataStore.approvals[index], ...updatedApproval };
    dataStore.timestamp = Date.now();
    
    const stats = {
      total: dataStore.approvals.length,
      pending: dataStore.approvals.filter(a => a.approval_status === 'PENDING').length,
      approved: dataStore.approvals.filter(a => a.approval_status === 'APPROVED').length,
      rejected: dataStore.approvals.filter(a => a.approval_status === 'REJECTED').length
    };
    dataStore.approvalStats = stats;
    
    dispatchEvent('approvals-updated', dataStore.approvals);
    dispatchEvent('approval-stats-updated', stats);
  }
};

export const removeApprovalFromStore = (id) => {
  if (!dataStore.approvals) return;
  dataStore.approvals = dataStore.approvals.filter(a => a.id !== id);
  dataStore.timestamp = Date.now();
  
  const stats = {
    total: dataStore.approvals.length,
    pending: dataStore.approvals.filter(a => a.approval_status === 'PENDING').length,
    approved: dataStore.approvals.filter(a => a.approval_status === 'APPROVED').length,
    rejected: dataStore.approvals.filter(a => a.approval_status === 'REJECTED').length
  };
  dataStore.approvalStats = stats;
  
  dispatchEvent('approvals-updated', dataStore.approvals);
  dispatchEvent('approval-stats-updated', stats);
};

export const clearApprovalsCache = () => {
  dataStore.approvals = null;
  dataStore.approvalStats = null;
  dataStore.timestamp = Date.now();
  dispatchEvent('approvals-updated', []);
  dispatchEvent('approval-stats-updated', { total: 0, pending: 0, approved: 0, rejected: 0 });
};

// ==================== AUDIT LOGS METHODS ====================
export const setAuditLogs = (logs) => {
  dataStore.auditLogs = logs;
  dataStore.timestamp = Date.now();
  dispatchEvent('audit-logs-updated', logs);
};

export const getAuditLogs = () => dataStore.auditLogs || [];

export const setAuditStats = (stats) => {
  dataStore.auditStats = stats;
  dataStore.timestamp = Date.now();
  dispatchEvent('audit-stats-updated', stats);
};

export const getAuditStats = () => dataStore.auditStats || { total_logs: 0, login_stats: { success: 0, failed: 0 } };

export const addAuditLog = (log) => {
  if (!dataStore.auditLogs) dataStore.auditLogs = [];
  dataStore.auditLogs.unshift(log);
  dataStore.timestamp = Date.now();
  dispatchEvent('audit-logs-updated', dataStore.auditLogs);
};

export const clearAuditCache = () => {
  dataStore.auditLogs = null;
  dataStore.auditStats = null;
  dataStore.timestamp = Date.now();
  dispatchEvent('audit-logs-updated', []);
  dispatchEvent('audit-stats-updated', { total_logs: 0, login_stats: { success: 0, failed: 0 } });
};

// ==================== UTILITY METHODS ====================
export const hasData = (type) => {
  if (type) return dataStore[type] !== null;
  return dataStore.user !== null;
};

export const hasCategories = () => dataStore.categories !== null;
export const hasStages = () => dataStore.stages !== null;
export const hasCourts = () => dataStore.courts !== null;
export const hasDocuments = () => dataStore.documents !== null;
export const hasUsers = () => dataStore.users !== null;
export const hasClients = () => dataStore.clients !== null;
export const hasCases = () => dataStore.cases !== null;
export const hasNotifications = () => dataStore.notifications !== null;
export const hasApprovals = () => dataStore.approvals !== null;

export const clearData = () => {
  dataStore.user = null;
  dataStore.dashboard = null;
  dataStore.categories = null;
  dataStore.stages = null;
  dataStore.courts = null;
  dataStore.documents = null;
  dataStore.users = null;
  dataStore.clients = null;
  dataStore.cases = null;
  dataStore.approvals = null;
  dataStore.approvalStats = null;
  dataStore.auditLogs = null;
  dataStore.auditStats = null;
  dataStore.notifications = null;
  dataStore.unreadCount = 0;
  dataStore.hearings = [];
  dataStore.hearingStats = { today: 0, tomorrow: 0, this_week: 0, this_month: 0, upcoming: 0, past: 0, by_type: {} };
  dataStore.timestamp = null;
  sessionStorage.clear();
  dispatchEvent('all-data-cleared', null);
};

export const getTimestamp = () => dataStore.timestamp;

export const isStale = (maxAge = 30000) => {
  if (!dataStore.timestamp) return true;
  return Date.now() - dataStore.timestamp > maxAge;
};

export const listenForUpdates = (eventName, callback) => {
  if (typeof window !== 'undefined') {
    window.addEventListener(eventName, callback);
    return () => window.removeEventListener(eventName, callback);
  }
  return () => {};
};

// ==================== ROLE HELPERS ====================
export const getRoleLabel = (role) => {
  const roles = { admin: 'Administrator', lawyer: 'Lawyer', clerk: 'Clerk' };
  return roles[role?.toLowerCase()] || role || 'User';
};

export const isAdmin = (role) => role?.toLowerCase() === 'admin';
export const isLawyer = (role) => role?.toLowerCase() === 'lawyer';
export const isClerk = (role) => role?.toLowerCase() === 'clerk';

// ==================== SIDEBAR METHODS ====================
export const getSidebarItems = (role) => {
  const items = {
    admin: [
      { path: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
      { path: '/usermanagement', label: 'Users', icon: 'users' },
      { path: '/casemaster', label: 'Case Master', icon: 'cases' },
      { path: '/approvals', label: 'Approvals', icon: 'approvals' },
      { path: '/audit-trail', label: 'Activity Logs', icon: 'logs' },
      { path: '/calendar', label: 'Calendar', icon: 'calendar' },
      { label: 'Master Data', icon: 'tasks', isDropdown: true,
        children: [
          { path: '/casecategories', label: 'Case Categories', icon: 'tasks' },
          { path: '/courts', label: 'Courts', icon: 'tasks' },
          { path: '/documents', label: 'Documents', icon: 'tasks' },
        ]
      }
    ],
    lawyer: [
      { path: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
      { path: '/casemaster', label: 'My Cases', icon: 'cases' },
      { path: '/approvals', label: 'Approvals', icon: 'approvals' },
      { path: '/calendar', label: 'Calendar', icon: 'calendar' },
      { label: 'Master Data', icon: 'tasks', isDropdown: true,
        children: [
          { path: '/casecategories', label: 'Case Categories', icon: 'tasks' },
          { path: '/courts', label: 'Courts', icon: 'tasks' },
          { path: '/documents', label: 'Documents', icon: 'tasks' },
        ]
      }
    ],
    clerk: [
      { path: '/dashboard', label: 'Dashboard', icon: 'dashboard' },
      { path: '/casemaster', label: 'Case Master', icon: 'cases' },
      { path: '/calendar', label: 'Calendar', icon: 'calendar' },
      { label: 'Master Data', icon: 'tasks', isDropdown: true,
        children: [
          { path: '/casecategories', label: 'Case Categories', icon: 'tasks' },
          { path: '/courts', label: 'Courts', icon: 'tasks' },
          { path: '/documents', label: 'Documents', icon: 'tasks' },
        ]
      }
    ]
  };
  return items[role] || items.admin;
};

export const getIcon = (name) => {
  const icons = {
    dashboard: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
    users: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
    logs: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
    cases: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
    tasks: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>`,
    approvals: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>`,
    calendar: `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
  };
  return icons[name] || '';
};

// ==================== CONSTANTS ====================
const CONSTANTS = {
  CASE_STATUSES: [
    { value: 'active', label: 'Active', color: '#10b981', bg: '#d1fae5', icon: '●' },
    { value: 'closed', label: 'Closed', color: '#6b7280', bg: '#f3f4f6', icon: '▼' },
    { value: 'archived', label: 'Archived', color: '#374151', bg: '#e5e7eb', icon: '📦' }
  ],
  
  PRIORITIES: [
    { value: 'urgent', label: 'Urgent', color: '#ef4444', bg: '#fee2e2', dot: 'bg-red-500' },
    { value: 'normal', label: 'Normal', color: '#3b82f6', bg: '#dbeafe', dot: 'bg-blue-500' },
    { value: 'low', label: 'Low', color: '#6b7280', bg: '#f3f4f6', dot: 'bg-slate-400' }
  ],
  
  CATEGORY_BADGE_CLASSES: {
    'Pleading': 'bg-blue-50 text-blue-700 border border-blue-200',
    'Letter': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'Evidence': 'bg-amber-50 text-amber-700 border border-amber-200',
    'Court Issuance': 'bg-red-50 text-red-700 border border-red-200',
    'Other': 'bg-slate-50 text-slate-600 border border-slate-200'
  },
  
  APPROVAL_STATUS: {
    PENDING: { label: 'Pending', color: '#f59e0b', bg: '#fed7aa', class: 'bg-amber-100 text-amber-700 border border-amber-200', icon: '⏳' },
    APPROVED: { label: 'Approved', color: '#10b981', bg: '#d1fae5', class: 'bg-emerald-100 text-emerald-700 border border-emerald-200', icon: '✅' },
    REJECTED: { label: 'Rejected', color: '#ef4444', bg: '#fee2e2', class: 'bg-red-100 text-red-700 border border-red-200', icon: '❌' }
  },
  
  TASK_STATUS: {
    'todo': { label: 'To-do', class: 'bg-slate-100 text-slate-600', dot: 'bg-slate-400' },
    'in-progress': { label: 'In Progress', class: 'bg-amber-100 text-amber-700', dot: 'bg-amber-400' },
    'done': { label: 'Done', class: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500' }
  },
  
  DOCUMENT_CATEGORIES: ['Pleading', 'Letter', 'Evidence', 'Court Issuance', 'Other']
};

// ==================== STATUS HELPERS ====================
export const getStatusInfo = (status) => {
  return CONSTANTS.CASE_STATUSES.find(s => s.value === status) || 
    { label: status || 'Unknown', color: '#6b7280', bg: '#f3f4f6' };
};

export const getStatusClass = (status) => {
  const classes = {
    'active': 'bg-emerald-50 text-emerald-700 border border-emerald-200',
    'closed': 'bg-slate-100 text-slate-600 border border-slate-200',
    'archived': 'bg-amber-50 text-amber-700 border border-amber-200'
  };
  return classes[status] || 'bg-slate-100 text-slate-500';
};

export const getPriorityInfo = (priority) => {
  return CONSTANTS.PRIORITIES.find(p => p.value === priority) || 
    { label: priority || 'Normal', color: '#6b7280', bg: '#f3f4f6', dot: 'bg-slate-400' };
};

export const getPriorityClass = (priority) => {
  const classes = {
    'urgent': 'bg-red-50 text-red-700 border border-red-200',
    'normal': 'bg-blue-50 text-blue-700 border border-blue-200',
    'low': 'bg-slate-100 text-slate-600 border border-slate-200'
  };
  return classes[priority] || 'bg-slate-100 text-slate-500';
};

export const getPriorityDot = (priority) => {
  const dots = {
    'urgent': 'bg-red-500',
    'normal': 'bg-blue-500',
    'low': 'bg-slate-400'
  };
  return dots[priority] || 'bg-slate-400';
};

export const getTaskStatusInfo = (status) => {
  return CONSTANTS.TASK_STATUS[status] || 
    { label: status || 'Unknown', class: 'bg-slate-100 text-slate-500', dot: 'bg-slate-400' };
};

export const getTaskStatusClass = (status) => getTaskStatusInfo(status).class;
export const getTaskStatusDot = (status) => getTaskStatusInfo(status).dot;
export const getTaskStatusLabel = (status) => getTaskStatusInfo(status).label;

export const getApprovalStatusInfo = (status) => {
  return CONSTANTS.APPROVAL_STATUS[status] || 
    { label: status || 'Unknown', class: 'bg-slate-100 text-slate-600', icon: '❓' };
};

export const getApprovalStatusClass = (status) => getApprovalStatusInfo(status).class;
export const getApprovalStatusIcon = (status) => getApprovalStatusInfo(status).icon;
export const getApprovalStatusLabel = (status) => getApprovalStatusInfo(status).label;

export const getMovementTypeClass = (type) => {
  return type === 'OUT' ? 'bg-rose-100 text-rose-700 border border-rose-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200';
};

export const getMovementTypeLabel = (type) => type === 'OUT' ? 'Release' : 'Receive';

export const getCategoryBadgeClass = (category) => {
  return CONSTANTS.CATEGORY_BADGE_CLASSES[category] || 'bg-slate-50 text-slate-600 border border-slate-200';
};

export const getDocumentCategories = () => CONSTANTS.DOCUMENT_CATEGORIES;

export const getNotificationIcon = (type) => {
  if (!type) return '🔔';
  
  const icons = {
    case_assigned: '📁', case_reassigned: '🔄', stage_changed: '📊',
    checklist_movement_pending: '📋', folder_movement_pending: '📂',
    task_assigned: '📝', task_status_changed: '✓',
    document_approved: '✅', document_rejected: '❌'
  };
  
  for (const [key, icon] of Object.entries(icons)) {
    if (type.includes(key)) return icon;
  }
  
  if (type.includes('approved')) return '✅';
  if (type.includes('rejected')) return '❌';
  if (type.includes('pending')) return '⏳';
  if (type.includes('folder')) return '📂';
  if (type.includes('checklist')) return '📋';
  if (type.includes('task')) return '📝';
  if (type.includes('case')) return '📁';
  
  return '🔔';
};

export const getNotificationIconClass = (type) => {
  if (type.includes('approved')) return 'bg-emerald-100';
  if (type.includes('rejected')) return 'bg-red-100';
  if (type.includes('pending')) return 'bg-amber-100';
  if (type.includes('task')) return 'bg-blue-100';
  if (type.includes('folder')) return 'bg-orange-100';
  if (type.includes('checklist')) return 'bg-indigo-100';
  return 'bg-slate-100';
};

// ==================== FORMATTING HELPERS ====================
export const formatDate = (date) => {
  if (!date) return '—';
  const d = new Date(date);
  if (isNaN(d.getTime())) return date;
  
  const today = new Date();
  const yesterday = new Date(today);
  yesterday.setDate(yesterday.getDate() - 1);
  
  if (d.toDateString() === today.toDateString()) return 'Today';
  if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
  
  return d.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: d.getFullYear() !== today.getFullYear() ? 'numeric' : undefined
  }).replace(',', '');
};

export const formatDateTime = (date) => {
  if (!date) return '—';
  const d = new Date(date);
  if (isNaN(d.getTime())) return date;
  
  return d.toLocaleString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};

export const formatTimeAgo = (date) => {
  if (!date) return '';
  const seconds = Math.floor((Date.now() - new Date(date)) / 1000);
  if (seconds < 5) return 'just now';
  if (seconds < 60) return `${seconds}s ago`;
  const minutes = Math.floor(seconds / 60);
  if (minutes < 60) return `${minutes}m ago`;
  const hours = Math.floor(minutes / 60);
  if (hours < 24) return `${hours}h ago`;
  const days = Math.floor(hours / 24);
  if (days < 7) return `${days}d ago`;
  return formatDate(date);
};

export const getInitials = (name) => {
  if (!name || name === '—' || name === 'Unassigned') return '?';
  const parts = name.split(' ').filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
};

export const capitalize = (str) => {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
};

export const truncate = (str, length = 50) => {
  if (!str || str.length <= length) return str;
  return str.substring(0, length) + '…';
};

export const isOverdue = (date) => {
  if (!date) return false;
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const due = new Date(date);
  due.setHours(0, 0, 0, 0);
  return due < today;
};

// ==================== GROUP AND SORT HELPERS ====================
export const groupBy = (array, key) => {
  return array.reduce((result, item) => {
    const groupKey = typeof key === 'function' ? key(item) : item[key];
    if (!result[groupKey]) result[groupKey] = [];
    result[groupKey].push(item);
    return result;
  }, {});
};

export const sortBy = (array, key, direction = 'asc') => {
  return [...array].sort((a, b) => {
    const aVal = typeof key === 'function' ? key(a) : a[key];
    const bVal = typeof key === 'function' ? key(b) : b[key];
    if (aVal < bVal) return direction === 'asc' ? -1 : 1;
    if (aVal > bVal) return direction === 'asc' ? 1 : -1;
    return 0;
  });
};

// ==================== HEARINGS METHODS ====================
export const setHearings = (hearings) => {
  dataStore.hearings = Array.isArray(hearings) ? hearings.filter(h => h && h.hearing_date) : [];
  dataStore.timestamp = Date.now();
  dispatchEvent('hearings-updated', dataStore.hearings);
  updateHearingStats();
};

export const getHearings = () => Array.isArray(dataStore.hearings) ? dataStore.hearings : [];

export const getHearingById = (id) => {
  if (!Array.isArray(dataStore.hearings)) return null;
  return dataStore.hearings.find(h => h.id === id) || null;
};

export const addHearing = (hearing) => {
  if (!hearing || !hearing.hearing_date || isPastDate(hearing.hearing_date)) return;
  
  if (!Array.isArray(dataStore.hearings)) dataStore.hearings = [];
  dataStore.hearings.unshift(hearing);
  dataStore.timestamp = Date.now();
  dispatchEvent('hearings-updated', dataStore.hearings);
  updateHearingStats();
};

export const updateHearingInStore = (id, updatedHearing) => {
  if (!Array.isArray(dataStore.hearings)) return;
  if (updatedHearing.hearing_date && isPastDate(updatedHearing.hearing_date)) return;
  
  const index = dataStore.hearings.findIndex(h => h.id === id);
  if (index !== -1) {
    dataStore.hearings[index] = { ...dataStore.hearings[index], ...updatedHearing };
    dataStore.timestamp = Date.now();
    dispatchEvent('hearings-updated', dataStore.hearings);
    updateHearingStats();
  }
};

export const removeHearingFromStore = (id) => {
  if (!Array.isArray(dataStore.hearings)) return;
  
  const hearing = dataStore.hearings.find(h => h.id === id);
  if (!hearing || isPastDate(hearing.hearing_date)) return;
  
  dataStore.hearings = dataStore.hearings.filter(h => h.id !== id);
  dataStore.timestamp = Date.now();
  dispatchEvent('hearings-updated', dataStore.hearings);
  updateHearingStats();
};

export const getHearingsByDate = (date) => {
  if (!Array.isArray(dataStore.hearings)) return [];
  const targetDate = new Date(date).toDateString();
  return dataStore.hearings
    .filter(h => h && h.hearing_date && new Date(h.hearing_date).toDateString() === targetDate)
    .sort((a, b) => {
      if (!a.start_time) return -1;
      if (!b.start_time) return 1;
      return a.start_time.localeCompare(b.start_time);
    });
};

export const getHearingsByCase = (caseId) => {
  if (!Array.isArray(dataStore.hearings)) return [];
  return dataStore.hearings.filter(h => h.case_id === caseId);
};

export const getUpcomingHearings = (days = 30) => {
  if (!Array.isArray(dataStore.hearings)) return [];
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const future = new Date();
  future.setDate(today.getDate() + days);
  
  return dataStore.hearings
    .filter(h => {
      if (!h || !h.hearing_date) return false;
      const date = new Date(h.hearing_date);
      return date >= today && date <= future && h.status === 'scheduled';
    })
    .sort((a, b) => new Date(a.hearing_date) - new Date(b.hearing_date));
};

export const getTodaysHearings = () => {
  if (!Array.isArray(dataStore.hearings)) return [];
  const today = new Date().toDateString();
  return dataStore.hearings
    .filter(h => h && h.hearing_date && new Date(h.hearing_date).toDateString() === today)
    .sort((a, b) => {
      if (!a.start_time) return -1;
      if (!b.start_time) return 1;
      return a.start_time.localeCompare(b.start_time);
    });
};

export const setHearingStats = (stats) => {
  dataStore.hearingStats = stats || { today: 0, tomorrow: 0, this_week: 0, this_month: 0, upcoming: 0, past: 0, by_type: {} };
  dataStore.timestamp = Date.now();
  dispatchEvent('hearing-stats-updated', dataStore.hearingStats);
};

export const getHearingStats = () => dataStore.hearingStats || { today: 0, tomorrow: 0, this_week: 0, this_month: 0, upcoming: 0, past: 0, by_type: {} };

export const clearHearingsCache = () => {
  dataStore.hearings = [];
  dataStore.hearingStats = { today: 0, tomorrow: 0, this_week: 0, this_month: 0, upcoming: 0, past: 0, by_type: {} };
  dataStore.timestamp = Date.now();
  dispatchEvent('hearings-updated', []);
  dispatchEvent('hearing-stats-updated', dataStore.hearingStats);
};

// ==================== DATE HELPER METHODS ====================
export const isPastDate = (date) => {
  if (!date) return false;
  const hearingDate = new Date(date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return hearingDate < today;
};
// Add this alias for backward compatibility
export const isPast = isPastDate;
export const isToday = (date) => {
  if (!date) return false;
  const hearingDate = new Date(date);
  const today = new Date();
  return hearingDate.toDateString() === today.toDateString();
};

export const isFutureDate = (date) => {
  if (!date) return false;
  const hearingDate = new Date(date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return hearingDate >= today;
};

export const getEventColor = (type) => {
  const colors = {
    hearing: '#1a4972', meeting: '#10b981', deadline: '#ef4444',
    task: '#f59e0b', personal: '#8b5cf6', other: '#6b7280'
  };
  return colors[type] || '#6b7280';
};

export const getEventIcon = (type) => {
  const icons = {
    hearing: '⚖️', meeting: '🤝', deadline: '⏰',
    task: '✅', personal: '📌', other: '📅'
  };
  return icons[type] || '📅';
};

// ==================== UPDATE HEARING STATS HELPER ====================
const updateHearingStats = () => {
  if (!Array.isArray(dataStore.hearings)) return;
  
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);
  const startOfWeek = new Date(today);
  startOfWeek.setDate(today.getDate() - today.getDay());
  const endOfWeek = new Date(startOfWeek);
  endOfWeek.setDate(endOfWeek.getDate() + 6);
  
  const stats = {
    today: dataStore.hearings.filter(h => h && h.hearing_date && new Date(h.hearing_date).toDateString() === today.toDateString() && h.status === 'scheduled').length,
    tomorrow: dataStore.hearings.filter(h => h && h.hearing_date && new Date(h.hearing_date).toDateString() === tomorrow.toDateString() && h.status === 'scheduled').length,
    this_week: dataStore.hearings.filter(h => {
      if (!h || !h.hearing_date) return false;
      const date = new Date(h.hearing_date);
      return date >= startOfWeek && date <= endOfWeek && h.status === 'scheduled';
    }).length,
    this_month: dataStore.hearings.filter(h => {
      if (!h || !h.hearing_date) return false;
      const date = new Date(h.hearing_date);
      return date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear() && h.status === 'scheduled';
    }).length,
    upcoming: dataStore.hearings.filter(h => h && h.hearing_date && new Date(h.hearing_date) >= today && h.status === 'scheduled').length,
    past: dataStore.hearings.filter(h => h && h.hearing_date && new Date(h.hearing_date) < today).length,
    by_type: {
      hearings: dataStore.hearings.filter(h => h && h.type === 'hearing').length,
      meetings: dataStore.hearings.filter(h => h && h.type === 'meeting').length,
      deadlines: dataStore.hearings.filter(h => h && h.type === 'deadline').length,
      tasks: dataStore.hearings.filter(h => h && h.type === 'task').length,
      personal: dataStore.hearings.filter(h => h && h.type === 'personal').length,
      other: dataStore.hearings.filter(h => h && h.type === 'other').length
    }
  };
  
  dataStore.hearingStats = stats;
  dispatchEvent('hearing-stats-updated', stats);
};

// ==================== DEFAULT EXPORT ====================
export default {
  // User
  setUser, getUser, getUserName, getUserRole, getUserInitials, isAuthenticated,
  
  // Dashboard
  setDashboard, getDashboard, updateDashboardStats,
  
  // Cases
  setCases, addCase, updateCaseInStore, removeCaseFromStore, getCases, listenForUpdates,
  getCaseById, getCasesByStatus, getCasesByLawyer, getCasesByClerk, getCaseCode, getCaseTitle,

  // Categories
  setCategories, getCategories, addCategory, updateCategoryInStore, removeCategoryFromStore,
  getCategoryName, getCategoryColor,
  
  // Stages
  setStages, getStages, getStageName, getStageColor,
  
  // Courts
  setCourts, getCourts, addCourt, updateCourtInStore, removeCourtFromStore, getCourtName,
  
  // Documents
  setDocuments, getDocuments, addDocument, updateDocumentInStore, removeDocumentFromStore,
  getDocumentType, getDocumentColor,
  
  // Users
  setUsers, getUsers, addUser, updateUserInStore, removeUserFromStore, getUserById,
  getLawyers, getClerks, getUserNameById,
  
  // Clients
  setClients, getClients, addClient, updateClientInStore, removeClientFromStore,
  getClientById, getClientName,
  
  // Notifications
  setNotifications, getNotifications, getUnreadCount, addNotification,
  markNotificationAsRead, markAllNotificationsAsRead, updateNotificationInStore,
  
  // Utilities
  hasData, hasCategories, hasStages, hasCourts, hasDocuments, hasUsers, hasClients,
  hasCases, hasNotifications, hasApprovals, clearData, getTimestamp, isStale, listenForUpdates,
  
  // Role helpers
  getRoleLabel, isAdmin, isLawyer, isClerk,
  
  // Sidebar
  getSidebarItems, getIcon,
  
  // Status helpers
  getStatusInfo, getStatusClass, getPriorityInfo, getPriorityClass, getPriorityDot,
  getTaskStatusInfo, getTaskStatusClass, getTaskStatusDot, getTaskStatusLabel,
  getApprovalStatusInfo, getApprovalStatusClass, getApprovalStatusIcon, getApprovalStatusLabel,
  getMovementTypeClass, getMovementTypeLabel, getCategoryBadgeClass, getDocumentCategories,
  getNotificationIcon, getNotificationIconClass,
  
  // Formatting
  formatDate, formatDateTime, formatTimeAgo, getInitials, capitalize, truncate,
  
  // Validation
  isOverdue,
  
  // Array helpers
  groupBy, sortBy,
  
  // Audit Logs
  setAuditLogs, getAuditLogs, setAuditStats, getAuditStats, addAuditLog, clearAuditCache,

  // Approvals
  setApprovals, getApprovals, setApprovalStats, getApprovalStats,
  updateApprovalInStore, removeApprovalFromStore, clearApprovalsCache,

  // Hearings - COMPLETE
  setHearings, getHearings, addHearing, updateHearingInStore, removeHearingFromStore,
  getHearingById, getHearingsByDate, getHearingsByCase, getUpcomingHearings, getTodaysHearings,
  setHearingStats, getHearingStats, clearHearingsCache,
  
  // Date Helpers
  isPastDate, isToday, isFutureDate, getEventColor, getEventIcon,
    isPast, // <-- MAKE SURE THIS IS HERE
  isToday,
  // Constants
  CONSTANTS
};