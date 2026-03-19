import api from "@/services/api";
import { 
  setApprovals, 
  getApprovals, 
  setApprovalStats, 
  getApprovalStats,
  updateApprovalInStore
} from "@/utils/appUtils";

const approvalService = {

  async getApprovals(params = {}) {
    try {
      const { data } = await api.get("/admin/approvals", { params });
      
      if (data.success && data.data) {
        // Store in appUtils
        setApprovals(data.data);
        if (data.stats) {
          setApprovalStats(data.stats);
        }
      }
      
      return data;
    } catch (error) {
      console.error('Failed to fetch approvals:', error);
      
      // Return cached data from appUtils if available
      const cachedApprovals = getApprovals();
      const cachedStats = getApprovalStats();
      
      if (cachedApprovals.length > 0) {
        return { 
          success: true, 
          data: cachedApprovals, 
          stats: cachedStats 
        };
      }
      
      return { success: false, data: [], stats: { total: 0, pending: 0, approved: 0, rejected: 0 } };
    }
  },

  // Get pending movement count for badge
  async getPendingCount() {
    try {
      // Check cache first
      const cachedStats = getApprovalStats();
      if (cachedStats && cachedStats.pending !== undefined) {
        return cachedStats.pending;
      }
      
      const { data } = await api.get("/admin/approvals/pending-count");
      return data.count;
    } catch (error) {
      console.error('Failed to get pending count:', error);
      return 0;
    }
  },

  // Approve or reject a movement
  async reviewMovement(type, movementId, status, notes = "") {
    try {
      const { data } = await api.patch(`/admin/approvals/${type}/${movementId}/approve`, {
        status,
        notes
      });
      
      // After successful review, update the specific approval in store
      if (data.success && data.data) {
        updateApprovalInStore(movementId, { 
          approval_status: status,
          approved_by: data.data.approved_by,
          approved_at: data.data.approved_at,
          notes: notes || data.data.notes
        });
        
        // Refresh approvals in background
        this.getApprovals().catch(() => {});
      }
      
      return data;
    } catch (error) {
      console.error('Review movement error:', error);
      throw error;
    }
  },
  
  // Get pending document approvals count
  async getPendingDocumentCount() {
    try {
      const { data } = await api.get("/admin/documents/pending-approvals");
      return data.data?.length || 0;
    } catch (error) {
      console.error('Failed to get pending document count:', error);
      return 0;
    }
  },

  // Get pending document approvals list
  async getPendingDocuments() {
    try {
      const { data } = await api.get("/admin/documents/pending-approvals");
      return data;
    } catch (error) {
      console.error('Failed to get pending documents:', error);
      return { data: [] };
    }
  },

  // Approve a document
  async approveDocument(documentId) {
    try {
      const { data } = await api.patch(`/admin/documents/${documentId}/approve`);
      return data;
    } catch (error) {
      console.error('Failed to approve document:', error);
      throw error;
    }
  },

  // Reject a document
  async rejectDocument(documentId, payload) {
    try {
      const { data } = await api.patch(`/admin/documents/${documentId}/reject`, payload);
      return data;
    } catch (error) {
      console.error('Failed to reject document:', error);
      throw error;
    }
  },

  // Get total pending approvals (movements + documents)
  async getTotalPendingCount() {
    try {
      const movementCount = await this.getPendingCount();
      const documentData = await this.getPendingDocumentCount();
      
      return {
        movements: movementCount,
        documents: documentData.data?.length || 0,
        total: movementCount + (documentData.data?.length || 0)
      };
    } catch (error) {
      console.error('Failed to get total pending count:', error);
      return { movements: 0, documents: 0, total: 0 };
    }
  },

  // Clear appUtils cache
  clearCache() {
    import('@/utils/appUtils').then(({ clearApprovalsCache }) => {
      clearApprovalsCache();
    });
  }
};

export default approvalService;