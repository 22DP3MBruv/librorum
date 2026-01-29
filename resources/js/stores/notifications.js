import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useNotificationStore = defineStore('notifications', () => {
  const notifications = ref([]);
  const unreadCount = ref(0);
  const loading = ref(false);
  const pollingInterval = ref(null);

  const unreadNotifications = computed(() => {
    return notifications.value.filter(n => !n.is_read);
  });

  const readNotifications = computed(() => {
    return notifications.value.filter(n => n.is_read);
  });

  // Fetch all notifications
  const fetchNotifications = async (unreadOnly = false) => {
    loading.value = true;
    try {
      const token = localStorage.getItem('auth_token');
      const params = new URLSearchParams();
      if (unreadOnly) params.append('unread_only', 'true');

      const response = await fetch(`/api/notifications?${params.toString()}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        notifications.value = data.notifications.data || [];
        return { success: true };
      }
      return { success: false };
    } catch (error) {
      console.error('Failed to fetch notifications:', error);
      return { success: false };
    } finally {
      loading.value = false;
    }
  };

  // Fetch unread count
  const fetchUnreadCount = async () => {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/notifications/unread-count', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const data = await response.json();
        unreadCount.value = data.unread_count;
        return { success: true };
      }
      return { success: false };
    } catch (error) {
      console.error('Failed to fetch unread count:', error);
      return { success: false };
    }
  };

  // Mark specific notification as read
  const markAsRead = async (notificationId) => {
    try {
      console.log('Marking notification as read:', notificationId);
      const token = localStorage.getItem('auth_token');
      const response = await fetch(`/api/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      console.log('Mark as read response:', response.status, response.ok);
      
      if (response.ok) {
        const data = await response.json();
        console.log('Mark as read data:', data);
        const notification = notifications.value.find(n => n.notification_id === notificationId);
        if (notification) {
          notification.is_read = true;
          notification.read_at = new Date().toISOString();
          unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
        return { success: true };
      }
      const errorData = await response.json().catch(() => null);
      console.error('Failed to mark as read, error:', errorData);
      return { success: false };
    } catch (error) {
      console.error('Failed to mark notification as read:', error);
      return { success: false };
    }
  };

  // Mark all notifications as read
  const markAllAsRead = async () => {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        notifications.value.forEach(n => {
          n.is_read = true;
          n.read_at = new Date().toISOString();
        });
        unreadCount.value = 0;
        return { success: true };
      }
      return { success: false };
    } catch (error) {
      console.error('Failed to mark all as read:', error);
      return { success: false };
    }
  };

  // Delete notification
  const deleteNotification = async (notificationId) => {
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch(`/api/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });

      if (response.ok) {
        const index = notifications.value.findIndex(n => n.notification_id === notificationId);
        if (index !== -1) {
          const notification = notifications.value[index];
          if (!notification.is_read) {
            unreadCount.value = Math.max(0, unreadCount.value - 1);
          }
          notifications.value.splice(index, 1);
        }
        return { success: true };
      }
      return { success: false };
    } catch (error) {
      console.error('Failed to delete notification:', error);
      return { success: false };
    }
  };

  // Start polling for new notifications
  const startPolling = (interval = 30000) => {
    if (pollingInterval.value) {
      stopPolling();
    }
    
    // Initial fetch
    fetchUnreadCount();
    
    // Set up polling
    pollingInterval.value = setInterval(() => {
      fetchUnreadCount();
    }, interval);
  };

  // Stop polling
  const stopPolling = () => {
    if (pollingInterval.value) {
      clearInterval(pollingInterval.value);
      pollingInterval.value = null;
    }
  };

  // Reset store
  const reset = () => {
    notifications.value = [];
    unreadCount.value = 0;
    stopPolling();
  };

  return {
    notifications,
    unreadCount,
    loading,
    unreadNotifications,
    readNotifications,
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    startPolling,
    stopPolling,
    reset,
  };
});
