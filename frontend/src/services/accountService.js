// src/services/accountService.js
import api from "@/services/api";

const accountService = {
  // Get profile
  async getProfile() {
    try {
      const { data } = await api.get("/account/profile");
      return data;
    } catch (error) {
      console.error('Get profile error:', error.response?.data || error.message);
      throw error.response?.data || error;
    }
  },

  // Update profile
  async updateProfile(profileData) {
    try {
      const { data } = await api.put("/account/profile", profileData);
      return data;
    } catch (error) {
      console.error('Update profile error:', error.response?.data || error.message);
      throw error.response?.data || error;
    }
  },

  // Change password
  async changePassword(passwordData) {
    try {
      const { data } = await api.post("/account/change-password", passwordData);
      return data;
    } catch (error) {
      console.error('Change password error:', error.response?.data || error.message);
      throw error.response?.data || error;
    }
  },

  // Logout all devices
  async logoutAllDevices() {
    try {
      const { data } = await api.post("/account/logout-all");
      return data;
    } catch (error) {
      console.error('Logout all devices error:', error.response?.data || error.message);
      throw error.response?.data || error;
    }
  },

  // Get active sessions
  async getActiveSessions() {
    try {
      const { data } = await api.get("/account/sessions");
      return data;
    } catch (error) {
      console.error('Get sessions error:', error.response?.data || error.message);
      throw error.response?.data || error;
    }
  }
};

export default accountService;