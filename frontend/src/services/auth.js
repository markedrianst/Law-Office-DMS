// src/services/auth.js
import api from "@/services/api";
import { useAuth } from '@/composables/useAuth';
import { 
  cacheService,
  caseService,
  approvalService,
  clientService,
  documentService 
} from '@/services/masterData';

let _interceptorId = null;

const initAuthInterceptor = () => {
  if (_interceptorId !== null) {
    api.interceptors.request.eject(_interceptorId);
  }

  _interceptorId = api.interceptors.request.use((config) => {
    const token = sessionStorage.getItem("token");
    if (token) {
      config.headers = config.headers ?? {};
      config.headers["Authorization"] = `Bearer ${token}`;
    }
    return config;
  });
};

initAuthInterceptor();

const authService = {
  async getCsrfCookie() {
    await api.get("/sanctum/csrf-cookie");
  },

  async login(payload) {
    await this.getCsrfCookie();
    
    try {
      const { data } = await api.post("/login", payload);

      if (data.requires_password_change) {
        return {
          requires_password_change: true,
          user: data.user
        };
      }

      if (data.token) {
        sessionStorage.setItem('token', data.token);
        sessionStorage.setItem('user', JSON.stringify(data.user));
        
        this.fetchAllDataInBackground(data.user);
        
        const { refreshUser } = useAuth();
        refreshUser();
      }

      return data;

    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },

  async fetchAllDataInBackground(user) {
    const role = user.role?.name || user.role;
    const userId = user.id;

    try {
      const promises = [
        this.fetchMasterData(),
        this.fetchClients(),
        this.fetchPendingCounts(),
        this.fetchUsers(),
        this.fetchRoles(),
        this.fetchAuditData(),
      ];
      
      const [masterData, clients, pendingCounts] = await Promise.all(promises);
      
      if (role === 'admin') {
        await this.fetchAdminData(masterData, clients, pendingCounts);
      } else if (role === 'lawyer') {
        await this.fetchLawyerData(userId, pendingCounts);
      } else if (role === 'clerk') {
        await this.fetchClerkData(userId);
      }
      
      await this.fetchRecentMovements();
      
    } catch (error) {
      console.error('Background data fetch error:', error);
    }
  },

  // ========== FETCH USERS WITH PROPER FORMATTING ==========
  async fetchUsers() {
    try {
      const { default: userService } = await import('@/services/userServices');
      const response = await userService.getUsers({ per_page: 100 });
      
      const users = (response.data || []).map(user => ({
        id: user.id,
        name: user.name || `${user.firstName || ''} ${user.lastName || ''}`.trim(),
        firstName: user.firstName || '',
        lastName: user.lastName || '',
        email: user.email,
        role: user.role === 'lawyer' ? 'Lawyer' : (user.role === 'clerk' ? 'Clerk' : user.role),
        status: user.status === 'active' ? 'Active' : 'Inactive',
        created_at: user.created_at,
        last_login: user.last_login,
        address: user.address || '',
        contact_number: user.contact_number || ''
      }));
      
      cacheService.setUsers(users);
      return users;
    } catch (error) {
      return [];
    }
  },

  // ========== FETCH ROLES WITH PROPER FORMATTING ==========
  async fetchRoles() {
    try {
      const { default: userService } = await import('@/services/userServices');
      const response = await userService.getRoles();
      
      const roles = (response.data || []).map(role => ({
        id: role.id,
        name: role.name.charAt(0).toUpperCase() + role.name.slice(1)
      }));
      
      cacheService.setUserRoles(roles);
      return roles;
    } catch (error) {
      return [];
    }
  },

  async fetchAuditData() {
    try {
      const { default: auditLogService } = await import('@/services/auditLogService');
      const logsResponse = await auditLogService.getCombinedLogs({ per_page: 20 }, true);
      const statsResponse = await auditLogService.getStats(true);
      return { logs: logsResponse, stats: statsResponse };
    } catch (error) {
      return null;
    }
  },

  async fetchMasterData() {
    try {
      const response = await caseService.getLookups();
      const data = response.data || {};
      
      if (data.categories) cacheService.setCategories(data.categories);
      if (data.stages) cacheService.setStages(data.stages);
      if (data.courts) cacheService.setCourts(data.courts);
      if (data.documents) cacheService.setDocuments(data.documents || []);
      if (data.users) cacheService.setUsers(data.users);
      
      return data;
    } catch (error) {
      return {};
    }
  },

  async fetchClients() {
    try {
      const response = await clientService.getAll({ limit: 100 });
      const clients = response.data || [];
      cacheService.setClients(clients);
      return clients;
    } catch (error) {
      return [];
    }
  },

  async fetchPendingCounts() {
    try {
      const counts = await approvalService.getTotalPendingCount();
      cacheService.setPendingCounts(counts);
      return counts;
    } catch (error) {
      return { documents: 0, movements: 0, total: 0 };
    }
  },

  async fetchAdminData(masterData, clients, pendingCounts) {
    try {
      const [casesResponse, docsResponse] = await Promise.all([
        caseService.getCases({ per_page: 100 }),
        documentService.getDocuments({ per_page: 1 })
      ]);
      
      const cases = casesResponse.data || [];
      const users = cacheService.getUsers() || [];
      
      const adminData = {
        stats: {
          total_cases: casesResponse.meta?.total || cases.length,
          active_cases: cases.filter(c => c.case_status === 'active').length,
          closed_cases: cases.filter(c => c.case_status === 'closed').length,
          archived_cases: cases.filter(c => c.case_status === 'archived').length,
          pending_approvals: pendingCounts.total || 0,
          total_clients: clients.length,
          total_documents: docsResponse.meta?.total || 0
        },
        adminStats: {
          total_users: users.length,
          lawyers: users.filter(u => u.role === 'Lawyer').length,
          clerks: users.filter(u => u.role === 'Clerk').length
        },
        pendingDocuments: pendingCounts.documents || 0,
        pendingMovements: pendingCounts.movements || 0,
        pendingTotal: pendingCounts.total || 0
      };
      
      cacheService.setAdminDashboard(adminData);
    } catch (error) {}
  },

  async fetchLawyerData(userId, pendingCounts) {
    try {
      const [casesResponse, movementsResponse] = await Promise.all([
        caseService.getCases({ assigned_lawyer_id: userId, per_page: 5, case_status: 'active' }),
        approvalService.getApprovals({ status: 'PENDING', per_page: 5 })
      ]);
      
      const lawyerData = {
        lawyerStats: {
          assigned_cases: casesResponse.meta?.total || 0,
          active_cases: (casesResponse.data || []).length
        },
        myCases: casesResponse.data || [],
        pendingItems: movementsResponse.data || [],
        pendingDocuments: pendingCounts.documents || 0,
        pendingMovements: pendingCounts.movements || 0,
        pendingTotal: pendingCounts.total || 0
      };
      
      cacheService.setLawyerDashboard(lawyerData);
    } catch (error) {}
  },

  async fetchClerkData(userId) {
    try {
      const casesResponse = await caseService.getCases({ assigned_clerk_id: userId, per_page: 1 });
      
      const tasks = [
        { id: 1, task: 'Review document for Case 2024-001', case_code: '2024-001', status: 'todo', due_date: '2024-03-20' },
        { id: 2, task: 'Prepare folder for Case 2024-002', case_code: '2024-002', status: 'in-progress', due_date: '2024-03-18' },
        { id: 3, task: 'File motion for Case 2024-003', case_code: '2024-003', status: 'todo', due_date: '2024-03-15' }
      ];
      
      const clerkData = {
        clerkStats: {
          assigned_cases: casesResponse.meta?.total || 0,
          total_tasks: tasks.length,
          pending_tasks: tasks.filter(t => t.status !== 'done').length,
          completed_tasks: tasks.filter(t => t.status === 'done').length
        },
        myTasks: tasks
      };
      
      cacheService.setClerkDashboard(clerkData);
    } catch (error) {}
  },

  async fetchRecentMovements() {
    try {
      const response = await approvalService.getApprovals({ per_page: 5 });
      cacheService.setRecentMovements(response.data || []);
    } catch (error) {
      cacheService.setRecentMovements([]);
    }
  },

  async logout() {
    await this.getCsrfCookie();
    
    try {
      const { data } = await api.post("/logout");
      
      cacheService.clearAll();
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      
      const { clearSession } = useAuth();
      clearSession();
      
      if (_interceptorId !== null) {
        api.interceptors.request.eject(_interceptorId);
        _interceptorId = null;
      }
      initAuthInterceptor();
      
      return data;
    } catch (error) {
      cacheService.clearAll();
      sessionStorage.removeItem('token');
      sessionStorage.removeItem('user');
      throw error;
    }
  },

  async getUser() {
    await this.getCsrfCookie();
    const { data } = await api.get("/user");
    return data;
  },

  async changePassword(payload) {
    await this.getCsrfCookie();
    const { data } = await api.put("/changepassword", payload);
    return data;
  }
};

export default authService;