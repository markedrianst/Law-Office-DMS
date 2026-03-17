// src/services/hearingService.js
import api from "@/services/api";
import { 
  setHearings,
  getHearings,
  addHearing,
  updateHearingInStore,
  removeHearingFromStore,
  setHearingStats,
  isPastDate
} from "@/utils/appUtils";

const hearingService = {
  // Get hearings with filters
  async getHearings(params = {}) {
    try {
      const { data } = await api.get("/hearings", { params });
      
      if (data.success) {
        const hearingsData = Array.isArray(data.data) ? data.data : [];
        setHearings(hearingsData);
      }
      
      return data;
    } catch (error) {
      const cachedHearings = getHearings();
      return { 
        success: true, 
        data: cachedHearings
      };
    }
  },

  // Get single hearing
  async getHearing(id) {
    try {
      const { data } = await api.get(`/hearings/${id}`);
      return data;
    } catch (error) {
      throw error;
    }
  },

  // Get calendar stats
  async getStats() {
    try {
      const { data } = await api.get("/hearings/stats");
      if (data.success) {
        setHearingStats(data.data);
      }
      return data;
    } catch (error) {
      return { 
        success: false, 
        data: {
          today: 0,
          tomorrow: 0,
          this_week: 0,
          this_month: 0,
          upcoming: 0,
          past: 0,
          by_type: {}
        }
      };
    }
  },

  // Create new hearing
  async createHearing(payload) {
    try {
      if (isPastDate(payload.hearing_date)) {
        throw new Error('Cannot create events in the past');
      }
      
      const { data } = await api.post("/hearings", payload);
      
      if (data.success && data.data) {
        addHearing(data.data);
      }
      
      return data;
    } catch (error) {
      throw error;
    }
  },

  // Update hearing
  async updateHearing(id, payload) {
    try {
      if (payload.hearing_date && isPastDate(payload.hearing_date)) {
        throw new Error('Cannot update events in the past');
      }
      
      const { data } = await api.put(`/hearings/${id}`, payload);
      
      if (data.success && data.data) {
        updateHearingInStore(id, data.data);
      }
      
      return data;
    } catch (error) {
      throw error;
    }
  },

  // Update hearing status
  async updateStatus(id, status) {
    try {
      const { data } = await api.patch(`/hearings/${id}/status`, { status });
      
      if (data.success && data.data) {
        updateHearingInStore(id, data.data);
      }
      
      return data;
    } catch (error) {
      throw error;
    }
  },

  // Reschedule hearing
  async rescheduleHearing(id, payload) {
    try {
      const { data } = await api.post(`/hearings/${id}/reschedule`, payload);
      
      if (data.success) {
        if (data.data?.old_hearing) {
          updateHearingInStore(id, data.data.old_hearing);
        }
        if (data.data?.new_hearing) {
          addHearing(data.data.new_hearing);
        }
      }
      
      return data;
    } catch (error) {
      throw error;
    }
  },

  // Cancel hearing
  async cancelHearing(id, payload) {
    try {
      const { data } = await api.post(`/hearings/${id}/cancel`, payload);
      
      if (data.success && data.data) {
        updateHearingInStore(id, data.data);
      }
      
      return data;
    } catch (error) {
      throw error;
    }
  },

  // Delete hearing
  async deleteHearing(id) {
    try {
      const { data } = await api.delete(`/hearings/${id}`);
      
      if (data.success) {
        removeHearingFromStore(id);
      }
      
      return data;
    } catch (error) {
      throw error;
    }
  }
};

export default hearingService;