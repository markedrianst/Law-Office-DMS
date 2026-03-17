// src/composables/useNotifications.js
import { ref, onMounted, onBeforeUnmount } from 'vue';
import notificationService from '@/services/notificationService';
import AppUtils from '@/utils/appUtils';
import { useAuth } from './Useauth';

export function useNotifications() {
  const { user } = useAuth();
  const notifications = ref(AppUtils.getNotifications() || []);
  const unreadCount = ref(AppUtils.getUnreadCount() || 0);
  const isLoading = ref(false);
  let poller = null;

  // Load initial notifications
  const loadNotifications = async () => {
    if (!user.value) return;
    
    isLoading.value = true;
    try {
      const response = await notificationService.sync();
      if (response.data) {
        AppUtils.setNotifications(response.data);
        notifications.value = response.data;
        unreadCount.value = response.unread_count || 0;
      }
    } catch (error) {
      console.error('Failed to load notifications:', error);
    } finally {
      isLoading.value = false;
    }
  };

  // Poll for updates
  const pollUpdates = async () => {
    if (!user.value) return;
    
    try {
      const response = await notificationService.sync();
      if (response.data?.length > 0) {
        response.data.forEach(notif => AppUtils.addNotification(notif));
        notifications.value = AppUtils.getNotifications();
        unreadCount.value = AppUtils.getUnreadCount();
      }
    } catch (error) {
      console.error('Polling failed:', error);
    }
  };

  // Mark as read
  const markAsRead = async (id) => {
    try {
      await notificationService.markAsRead(id);
      AppUtils.markNotificationAsRead(id);
      notifications.value = AppUtils.getNotifications();
      unreadCount.value = AppUtils.getUnreadCount();
    } catch (error) {
      console.error('Failed to mark as read:', error);
    }
  };

  // Mark all as read
  const markAllAsRead = async () => {
    try {
      await notificationService.markAllAsRead();
      AppUtils.markAllNotificationsAsRead();
      notifications.value = AppUtils.getNotifications();
      unreadCount.value = 0;
    } catch (error) {
      console.error('Failed to mark all as read:', error);
    }
  };

  // Start polling
  const startPolling = (interval = 3000) => {
    stopPolling();
    poller = setInterval(pollUpdates, interval);
  };

  // Stop polling
  const stopPolling = () => {
    if (poller) {
      clearInterval(poller);
      poller = null;
    }
  };

  // Lifecycle hooks - MUST be called directly in setup function
  onMounted(() => {
    loadNotifications();
    startPolling();
  });

  onBeforeUnmount(() => {
    stopPolling();
  });

  return {
    notifications,
    unreadCount,
    isLoading,
    markAsRead,
    markAllAsRead,
    refresh: loadNotifications
  };
}