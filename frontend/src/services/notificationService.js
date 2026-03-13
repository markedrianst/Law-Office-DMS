// src/services/notificationService.js
import api from '@/services/api';

const notificationService = {
  // Smart polling sync - gets only new/changed notifications
  async sync(since = null) {
    const params = since ? { since } : {};
    const { data } = await api.get('/notifications/sync', { params });
    return data;
  },

  // Get unread count only (lightweight)
  async getUnreadCount() {
    const { data } = await api.get('/notifications/unread-count');
    return data.count;
  },

  // Mark single notification as read
  async markAsRead(id) {
    const { data } = await api.post(`/notifications/${id}/read`);
    return data;
  },

  // Mark all as read
  async markAllAsRead() {
    const { data } = await api.post('/notifications/mark-all-read');
    return data;
  }
};

export default notificationService;