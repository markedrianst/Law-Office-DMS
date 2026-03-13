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

      // CASE 1: PASSWORD CHANGE REQUIRED
      if (data.requires_password_change) {
        return {
          requires_password_change: true,
          user: data.user
        };
      }

      // CASE 2: SUCCESSFUL LOGIN
      if (data.token) {
        // Store auth data IMMEDIATELY
        sessionStorage.setItem('token', data.token);
        sessionStorage.setItem('user', JSON.stringify(data.user));
        
        // 🔥 FETCH ALL DATA IN BACKGROUND - NO AWAIT!
        this.fetchAllDataInBackground(data.user);
        
        // Refresh auth state
        const { refreshUser } = useAuth();
        refreshUser();
      }

      return data;

    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  },

  // 🔥 ALL DATA FETCHING - Runs in background
  async fetchAllDataInBackground(user) {
    console.log('📦 Background data fetch started...');
    
    const role = user.role?.name || user.role;
    const userId = user.id;

    try {
      // ========== FETCH EVERYTHING IN PARALLEL ==========
      const promises = [
        this.fetchMasterData(),
        this.fetchClients(),
        this.fetchPendingCounts()
      ];
      
      // Wait for common data
      const [masterData, clients, pendingCounts] = await Promise.all(promises);
      
      // ========== FETCH ROLE-SPECIFIC DATA ==========
      if (role === 'admin') {
        await this.fetchAdminData(masterData, clients, pendingCounts);
      } else if (role === 'lawyer') {
        await this.fetchLawyerData(userId, pendingCounts);
      } else if (role === 'clerk') {
        await this.fetchClerkData(userId);
      }
      
      // ========== FETCH RECENT MOVEMENTS ==========
      await this.fetchRecentMovements();
      
      console.log('✅ All data cached successfully!');
      
    } catch (error) {
      console.error('Background data fetch error:', error);
    }
  },

  // ========== INDIVIDUAL FETCH METHODS ==========
  
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
          lawyers: users.filter(u => u.role === 'lawyer').length,
          clerks: users.filter(u => u.role === 'clerk').length
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