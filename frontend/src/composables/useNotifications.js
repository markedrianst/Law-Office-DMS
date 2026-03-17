// src/composables/useNotifications.js
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import notificationService from '@/services/notificationService';
import * as AppUtils from '@/utils/appUtils';
import { useAuth } from './Useauth';

export function useNotifications() {
  const { user } = useAuth();
  const notifications = ref(AppUtils.getNotifications() || []);
  const unreadCount = ref(AppUtils.getUnreadCount() || 0);
  const isLoading = ref(false);
  const lastSyncTime = ref(null);
  let poller = null;

  // For dropdown - ONLY unread notifications
  const unreadNotifications = computed(() => {
    return notifications.value.filter(n => !n.is_read).slice(0, 5);
  });

  // For full page - all notifications
  const allNotifications = computed(() => {
    return notifications.value;
  });

  // Load initial notifications
  const loadNotifications = async (showLoading = false) => {
    if (!user.value) return;
    
    if (showLoading) isLoading.value = true;
    try {
      const response = await notificationService.sync(lastSyncTime.value);
      if (response.data) {
        AppUtils.setNotifications(response.data);
        notifications.value = response.data;
        unreadCount.value = response.unread_count || 0;
        lastSyncTime.value = new Date().toISOString();
      }
    } catch (error) {
      console.error('Failed to load notifications:', error);
    } finally {
      if (showLoading) isLoading.value = false;
    }
  };

  // Poll for updates - PREVENTS DUPLICATES
  const pollUpdates = async () => {
    if (!user.value) return;
    
    try {
      const response = await notificationService.sync(lastSyncTime.value);
      
      if (response.data && response.data.length > 0) {
        const currentNotifications = AppUtils.getNotifications() || [];
        const newNotifications = [];
        
        // Only add notifications that don't already exist
        response.data.forEach(newNotif => {
          const exists = currentNotifications.some(existing => existing.id === newNotif.id);
          if (!exists) {
            newNotifications.push(newNotif);
          }
        });
        
        // Add only new unique notifications
        if (newNotifications.length > 0) {
          newNotifications.forEach(notif => AppUtils.addNotification(notif));
          notifications.value = AppUtils.getNotifications();
          unreadCount.value = AppUtils.getUnreadCount();
        }
        
        lastSyncTime.value = new Date().toISOString();
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
  const startPolling = (interval = 5000) => {
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

  // Manual refresh
  const refresh = async () => {
    await loadNotifications(true);
  };

  onMounted(() => {
    loadNotifications();
    startPolling();
  });

  onBeforeUnmount(() => {
    stopPolling();
  });

  return {
    unreadNotifications,
    allNotifications,
    notifications,
    unreadCount,
    isLoading,
    markAsRead,
    markAllAsRead,
    refresh
  };
}