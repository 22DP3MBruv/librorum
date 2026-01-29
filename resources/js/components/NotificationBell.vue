<template>
  <div class="relative" ref="dropdownRef">
    <!-- Notification Bell Button -->
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 rounded-full hover:bg-gray-100 transition-colors"
      :class="{ 'bg-gray-100': isOpen }"
      aria-label="Notifications"
    >
      <!-- Bell Icon -->
      <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      
      <!-- Unread Badge -->
      <span
        v-if="notificationStore.unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
      >
        {{ notificationStore.unreadCount > 99 ? '99+' : notificationStore.unreadCount }}
      </span>
    </button>

    <!-- Dropdown Panel -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="fixed md:absolute left-1/2 md:left-auto right-auto md:right-0 -translate-x-1/2 md:translate-x-0 mt-2 w-80 sm:w-96 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50"
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="text-lg font-semibold text-gray-900">
            {{ t('notifications.title') }}
          </h3>
          <button
            v-if="notificationStore.unreadCount > 0"
            @click="markAllAsRead"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
          >
            {{ t('notifications.markAllRead') }}
          </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
          <!-- Loading State -->
          <div v-if="notificationStore.loading" class="px-4 py-8 text-center">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-gray-200 border-t-blue-600"></div>
          </div>

          <!-- Empty State -->
          <div v-else-if="notificationStore.notifications.length === 0" class="px-4 py-8 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="mt-2">{{ t('notifications.empty') }}</p>
          </div>

          <!-- Notification Items -->
          <div v-else>
            <div
              v-for="notification in notificationStore.notifications"
              :key="notification.notification_id"
              class="px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-100 cursor-pointer"
              :class="{ 'bg-blue-50': !notification.is_read }"
              @click="handleNotificationClick(notification)"
            >
              <div class="flex items-start space-x-3">
                <!-- Icon -->
                <div class="flex-shrink-0">
                  <component :is="getNotificationIcon(notification.type)" class="h-6 w-6" :class="getNotificationColor(notification.type)" />
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-gray-900" :class="{ 'font-semibold': !notification.is_read }">
                    {{ notification.message }}
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ formatTime(notification.created_at) }}
                  </p>
                </div>

                <!-- Delete Button -->
                <button
                  @click.stop="deleteNotification(notification.notification_id)"
                  class="flex-shrink-0 text-gray-400 hover:text-red-600 transition-colors"
                >
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200">
          <router-link
            to="/notifications"
            @click="isOpen = false"
            class="block text-center text-sm text-blue-600 hover:text-blue-700 font-medium"
          >
            {{ t('notifications.viewAll') }}
          </router-link>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, h } from 'vue';
import { useNotificationStore } from '../stores/notifications';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';

const { t } = useI18n();
const router = useRouter();
const notificationStore = useNotificationStore();

const isOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = async () => {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    await notificationStore.fetchNotifications();
  }
};

const markAllAsRead = async () => {
  await notificationStore.markAllAsRead();
};

const deleteNotification = async (id) => {
  await notificationStore.deleteNotification(id);
};

const handleNotificationClick = async (notification) => {
  if (!notification.is_read) {
    await notificationStore.markAsRead(notification.notification_id);
  }
  
  // Navigate based on notification type
  if (notification.type === 'follow_request') {
    // Navigate to follow requests page to accept/reject
    router.push('/follow-requests');
  } else if (notification.type === 'follow_request_accepted' || notification.type === 'new_follower') {
    // Navigate to the user's profile who followed/accepted
    if (notification.related_id) {
      router.push(`/profile/${notification.related_id}`);
    }
  } else if (notification.related_type && notification.related_id) {
    if (notification.related_type.includes('Thread')) {
      router.push(`/discussions/${notification.related_id}`);
    } else if (notification.related_type.includes('Comment')) {
      // Navigate to thread (you may need to fetch thread_id from comment)
      router.push(`/discussions`);
    }
  }
  
  isOpen.value = false;
};

const getNotificationIcon = (type) => {
  const icons = {
    comment_reply: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6' })
    ]),
    thread_reply: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' })
    ]),
    comment_like: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' })
    ]),
    thread_like: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' })
    ]),
    new_follower: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z' })
    ]),
    follow_request: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' })
    ]),
    content_moderated: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
      h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' })
    ]),
    user_banned: () => h('svg', { class: 'h-6 w-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
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

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
  notificationStore.fetchUnreadCount();
  notificationStore.startPolling(30000); // Poll every 30 seconds
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
  notificationStore.stopPolling();
});
</script>
