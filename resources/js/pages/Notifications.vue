<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white shadow rounded-lg">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-bold text-gray-900">
            {{ t('notifications.title') }}
          </h1>
          <button
            v-if="notificationStore.unreadCount > 0"
            @click="markAllAsRead"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
          >
            {{ t('notifications.markAllRead') }}
          </button>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="px-6 py-3 border-b border-gray-200">
        <div class="flex space-x-4">
          <button
            @click="filter = 'all'"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-md transition-colors',
              filter === 'all' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            {{ t('notifications.all') }} ({{ notificationStore.notifications.length }})
          </button>
          <button
            @click="filter = 'unread'"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-md transition-colors',
              filter === 'unread' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-600 hover:bg-gray-100'
            ]"
          >
            {{ t('notifications.unread') }} ({{ notificationStore.unreadCount }})
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="divide-y divide-gray-200">
        <!-- Loading State -->
        <div v-if="notificationStore.loading" class="px-6 py-12 text-center">
          <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-gray-200 border-t-blue-600"></div>
          <p class="mt-4 text-gray-600">{{ t('common.loading') }}</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredNotifications.length === 0" class="px-6 py-12 text-center text-gray-500">
          <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
          </svg>
          <p class="mt-4 text-lg font-medium">{{ t('notifications.empty') }}</p>
        </div>

        <!-- Notification Items -->
        <div
          v-for="notification in filteredNotifications"
          :key="notification.notification_id"
          class="px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer"
          :class="{ 'bg-blue-50': !notification.is_read }"
          @click="handleNotificationClick(notification)"
        >
          <div class="flex items-start space-x-4">
            <!-- Icon -->
            <div class="flex-shrink-0 mt-1">
              <div class="h-10 w-10 rounded-full flex items-center justify-center" :class="getNotificationBgColor(notification.type)">
                <component :is="getNotificationIcon(notification.type)" class="h-5 w-5" :class="getNotificationColor(notification.type)" />
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm text-gray-900" :class="{ 'font-semibold': !notification.is_read }">
                    {{ notification.message }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ formatTime(notification.created_at) }}
                  </p>
                </div>

                <!-- Actions -->
                <div class="flex items-center space-x-2 ml-4">
                  <button
                    v-if="!notification.is_read"
                    @click.stop="markAsRead(notification.notification_id)"
                    class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                  >
                    {{ t('notifications.markRead') }}
                  </button>
                  <button
                    @click.stop="deleteNotification(notification.notification_id)"
                    class="text-gray-400 hover:text-red-600 transition-colors"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Notification Type Badge -->
              <div class="mt-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getTypeBadgeClass(notification.type)">
                  {{ getTypeLabel(notification.type) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue';
import { useNotificationStore } from '../stores/notifications';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const { t } = useI18n();
const router = useRouter();
const notificationStore = useNotificationStore();

const filter = ref('all');

const filteredNotifications = computed(() => {
  if (filter.value === 'unread') {
    return notificationStore.unreadNotifications;
  }
  return notificationStore.notifications;
});

const markAllAsRead = async () => {
  await notificationStore.markAllAsRead();
};

const markAsRead = async (id) => {
  await notificationStore.markAsRead(id);
};

const deleteNotification = async (id) => {
  await notificationStore.deleteNotification(id);
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await notificationStore.markAsRead(notification.notification_id);
  }
  
  // Navigate based on notification type
  if (notification.type === 'follow_request' || notification.type === 'follow_request_accepted') {
    router.push('/follow-requests');
  } else if (notification.related_type && notification.related_id) {
    if (notification.related_type.includes('Thread')) {
      router.push(`/discussions/${notification.related_id}`);
    }
  }
};

const getNotificationIcon = (type) => {
  const icons = {
    comment_reply: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6' })
    ]),
    thread_reply: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' })
    ]),
    comment_like: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' })
    ]),
    thread_like: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' })
    ]),
    new_follower: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' })
    ]),
    follow_request: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' })
    ]),
    content_moderated: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' })
    ]),
    user_banned: () => h('svg', { class: 'h-5 w-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' })
    ]),
  };
  return icons[type] || icons.thread_reply;
};

const getNotificationColor = (type) => {
  const colors = {
    comment_reply: 'text-blue-600',
    thread_reply: 'text-blue-600',
    comment_like: 'text-red-600',
    thread_like: 'text-red-600',
    new_follower: 'text-green-600',
    follow_request: 'text-purple-600',
    content_moderated: 'text-yellow-600',
    user_banned: 'text-red-700',
  };
  return colors[type] || 'text-gray-600';
};

const getNotificationBgColor = (type) => {
  const colors = {
    comment_reply: 'bg-blue-100',
    thread_reply: 'bg-blue-100',
    comment_like: 'bg-red-100',
    thread_like: 'bg-red-100',
    new_follower: 'bg-green-100',
    follow_request: 'bg-purple-100',
    content_moderated: 'bg-yellow-100',
    user_banned: 'bg-red-100',
  };
  return colors[type] || 'bg-gray-100';
};

const getTypeBadgeClass = (type) => {
  const classes = {
    comment_reply: 'bg-blue-100 text-blue-800',
    thread_reply: 'bg-blue-100 text-blue-800',
    comment_like: 'bg-red-100 text-red-800',
    thread_like: 'bg-red-100 text-red-800',
    new_follower: 'bg-green-100 text-green-800',
    follow_request: 'bg-purple-100 text-purple-800',
    content_moderated: 'bg-yellow-100 text-yellow-800',
    user_banned: 'bg-red-100 text-red-800',
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const getTypeLabel = (type) => {
  return t(`notifications.types.${type}`);
};

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diffInSeconds = Math.floor((now - date) / 1000);

  if (diffInSeconds < 60) return t('notifications.justNow');
  if (diffInSeconds < 3600) return t('notifications.minutesAgo', { minutes: Math.floor(diffInSeconds / 60) });
  if (diffInSeconds < 86400) return t('notifications.hoursAgo', { hours: Math.floor(diffInSeconds / 3600) });
  if (diffInSeconds < 604800) return t('notifications.daysAgo', { days: Math.floor(diffInSeconds / 86400) });
  
  return date.toLocaleDateString();
};

onMounted(() => {
  notificationStore.fetchNotifications();
});
</script>
