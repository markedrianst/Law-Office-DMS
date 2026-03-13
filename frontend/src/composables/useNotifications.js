// src/composables/useNotifications.js
import { ref, onMounted, onBeforeUnmount } from 'vue';
import notificationService from '@/services/notificationService';
import { useAuth } from './Useauth';

export function useNotifications() {
  const { user } = useAuth();
  const notifications = ref([]);
  const unreadCount = ref(0);
  const lastSync = ref(null);
  const isLoading = ref(false);
  let poller = null;

  // Load initial notifications
  const loadNotifications = async () => {
    if (!user.value) return;
    
    isLoading.value = true;
    try {
      const response = await notificationService.sync();
      notifications.value = response.data || [];
      unreadCount.value = response.unread_count || 0;
      lastSync.value = response.server_time;
    } catch (error) {
      console.error('Failed to load notifications:', error);
    } finally {
      isLoading.value = false;
    }
  };

  // Smart polling for updates
  const pollUpdates = async () => {
    if (!user.value) return;
    
    try {
      const response = await notificationService.sync(lastSync.value);
      const updatedNotifications = response.data || [];

      if (updatedNotifications.length > 0) {
        // Update existing notifications or add new ones
        updatedNotifications.forEach(updated => {
          const index = notifications.value.findIndex(n => n.id === updated.id);
          if (index !== -1) {
            notifications.value[index] = updated;
          } else {
            notifications.value.unshift(updated);
          }
        });
        
        // Keep only latest 50
        notifications.value = notifications.value.slice(0, 50);
      }

      unreadCount.value = response.unread_count;
      lastSync.value = response.server_time;
    } catch (error) {
      console.error('Polling failed:', error);
    }
  };

  // Mark as read
  const markAsRead = async (id) => {
    try {
      await notificationService.markAsRead(id);
      const index = notifications.value.findIndex(n => n.id === id);
      if (index !== -1) {
        notifications.value[index].is_read = true;
      }
      unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (error) {
      console.error('Failed to mark as read:', error);
    }
  };

  // Mark all as read
  const markAllAsRead = async () => {
    try {
      await notificationService.markAllAsRead();
      notifications.value.forEach(n => n.is_read = true);
      unreadCount.value = 0;
    } catch (error) {
      console.error('Failed to mark all as read:', error);
    }
  };

  // Get notification icon based on type
  const getNotificationIcon = (type) => {
    if (type.includes('case_assigned')) return '📁';
    if (type.includes('case_reassigned')) return '🔄';
    if (type.includes('stage_changed')) return '📊';
    if (type.includes('checklist')) return '✅';
    if (type.includes('folder')) return '📂';
    if (type.includes('task')) return '📋';
    if (type.includes('approved')) return '✔️';
    if (type.includes('rejected')) return '❌';
    return '🔔';
  };

  // Get notification color based on type
  const getNotificationColor = (type) => {
    if (type.includes('approved')) return 'text-emerald-600 bg-emerald-100';
    if (type.includes('rejected')) return 'text-red-600 bg-red-100';
    if (type.includes('pending')) return 'text-amber-600 bg-amber-100';
    if (type.includes('task')) return 'text-blue-600 bg-blue-100';
    return 'text-slate-600 bg-slate-100';
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
    getNotificationIcon,
    getNotificationColor,
    refresh: loadNotifications
  };
}