// src/services/dashboardService.js
import api from "@/services/api";
import caseService from "./caseService";
import approvalService from "./approvalService";
import documentService from "./documentService";

const dashboardService = {
  // ========== PARALLEL DATA FETCHING ==========
  async getAdminDashboardData(userId) {
    try {
      // Run ALL API calls in PARALLEL (not sequential)
      const [
        casesResponse,
        usersFromSession,
        pendingCounts,
        clientsFromSession
      ] = await Promise.all([
        // Only get counts, not full data
        caseService.getCases({ per_page: 1 }).catch(() => ({ meta: { total: 0 } })),
        
        // Get from session (instant)
        Promise.resolve(this.getFromSession('master_users')),
        
        // Get pending counts in one call
        approvalService.getTotalPendingCount().catch(() => ({ movements: 0, documents: 0, total: 0 })),
        
        // Get from session (instant)
        Promise.resolve(this.getFromSession('master_clients'))
      ]);

      return {
        stats: {
          total_cases: casesResponse.meta?.total || 0,
          active_cases: 0, // You can add separate count calls if needed
          closed_cases: 0,
          archived_cases: 0,
          pending_approvals: pendingCounts.total || 0,
          total_clients: clientsFromSession.length || 0,
          total_documents: 0
        },
        adminStats: {
          total_users: usersFromSession.length || 0,
          active_users: usersFromSession.length || 0,
          lawyers: usersFromSession.filter(u => u.role === 'lawyer').length,
          clerks: usersFromSession.filter(u => u.role === 'clerk').length,
          active_today: 0,
          logins_today: 0,
          activities_last_7_days: 0
        },
        pendingDocuments: pendingCounts.documents || 0,
        pendingMovements: pendingCounts.movements || 0,
        pendingTotal: pendingCounts.total || 0
      };
    } catch (error) {
      console.error('Dashboard data fetch failed:', error);
      return this.getFallbackData();
    }
  },

  async getLawyerDashboardData(lawyerId) {
    try {
      // Parallel fetching for lawyer dashboard
      const [casesResponse, pendingCounts, pendingMovements] = await Promise.all([
        caseService.getCases({ 
          assigned_lawyer_id: lawyerId,
          per_page: 5, // Only get 5 most recent
          case_status: 'active'
        }),
        approvalService.getTotalPendingCount(),
        approvalService.getApprovals({ status: 'PENDING', per_page: 5 })
      ]);

      return {
        lawyerStats: {
          assigned_cases: casesResponse.meta?.total || 0,
          active_cases: casesResponse.meta?.total || 0
        },
        myCases: casesResponse.data || [],
        pendingItems: pendingMovements.data || [],
        pendingDocuments: pendingCounts.documents || 0,
        pendingMovements: pendingCounts.movements || 0,
        pendingTotal: pendingCounts.total || 0
      };
    } catch (error) {
      console.error('Lawyer dashboard data failed:', error);
      return this.getFallbackLawyerData();
    }
  },

  async getClerkDashboardData(clerkId) {
    try {
      // Parallel fetching for clerk dashboard
      const [casesResponse, tasksResponse] = await Promise.all([
        caseService.getCases({ 
          assigned_clerk_id: clerkId,
          per_page: 1
        }),
        // You'll need to implement this endpoint
        this.getClerkTasks(clerkId)
      ]);

      return {
        clerkStats: {
          assigned_cases: casesResponse.meta?.total || 0,
          total_tasks: tasksResponse.length || 0,
          pending_tasks: tasksResponse.filter(t => t.status !== 'done').length || 0,
          completed_tasks: tasksResponse.filter(t => t.status === 'done').length || 0
        },
        myTasks: tasksResponse || []
      };
    } catch (error) {
      console.error('Clerk dashboard data failed:', error);
      return this.getFallbackClerkData();
    }
  },

  // ========== HELPER METHODS ==========
  getFromSession(key) {
    try {
      const stored = sessionStorage.getItem(key);
      if (!stored) return [];
      return JSON.parse(stored).data || [];
    } catch {
      return [];
    }
  },

  async getClerkTasks(clerkId) {
    // Placeholder - implement your actual task fetching
    return [
      { id: 1, task: 'Review document for Case 2024-001', case_code: '2024-001', status: 'todo', due_date: '2024-03-20' },
      { id: 2, task: 'Prepare folder for Case 2024-002', case_code: '2024-002', status: 'in-progress', due_date: '2024-03-18' },
      { id: 3, task: 'File motion for Case 2024-003', case_code: '2024-003', status: 'todo', due_date: '2024-03-15' }
    ];
  },

  getFallbackData() {
    return {
      stats: { total_cases: 0, active_cases: 0, closed_cases: 0, archived_cases: 0, pending_approvals: 0, total_clients: 0, total_documents: 0 },
      adminStats: { total_users: 0, active_users: 0, lawyers: 0, clerks: 0, active_today: 0, logins_today: 0, activities_last_7_days: 0 },
      pendingDocuments: 0,
      pendingMovements: 0,
      pendingTotal: 0
    };
  },

  getFallbackLawyerData() {
    return {
      lawyerStats: { assigned_cases: 0, active_cases: 0 },
      myCases: [],
      pendingItems: [],
      pendingDocuments: 0,
      pendingMovements: 0,
      pendingTotal: 0
    };
  },

  getFallbackClerkData() {
    return {
      clerkStats: { assigned_cases: 0, total_tasks: 0, pending_tasks: 0, completed_tasks: 0 },
      myTasks: []
    };
  }
};

export default dashboardService;