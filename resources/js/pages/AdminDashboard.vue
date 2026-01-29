<template>
  <div class="admin-dashboard">
    <div class="container mx-auto px-4 sm:px-6 py-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4 sm:mb-6">{{ $t('admin.dashboard') }}</h1>

      <!-- Tabs Navigation -->
      <div class="mb-6 border-b border-gray-200">
        <nav class="flex space-x-4 overflow-x-auto">
          <button
            @click="activeTab = 'overview'"
            :class="[
              'px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
              activeTab === 'overview'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
            ]"
          >
            {{ $t('admin.overview') }}
          </button>
          <button
            @click="activeTab = 'users'"
            :class="[
              'px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
              activeTab === 'users'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
            ]"
          >
            {{ $t('admin.userManagement') }}
          </button>
          <button
            @click="activeTab = 'flagged'"
            :class="[
              'px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 transition-colors',
              activeTab === 'flagged'
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-600 hover:text-gray-900 hover:border-gray-300'
            ]"
          >
            {{ $t('admin.flaggedUsers') }}
            <span v-if="flaggedUsers.length > 0" class="ml-2 bg-red-100 text-red-800 text-xs px-2 py-0.5 rounded-full">
              {{ flaggedUsers.length }}
            </span>
          </button>
        </nav>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <p class="mt-4 text-gray-600">{{ $t('common.loading') }}</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
        <p class="text-red-800">{{ error }}</p>
      </div>

      <!-- Tab Content -->
      <div v-else>
        <!-- Overview Tab -->
        <div v-show="activeTab === 'overview'">
          <!-- Overview Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-4 sm:mb-6">
          <!-- Users Card -->
          <div class="bg-white rounded-lg shadow-sm border p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-600">{{ $t('admin.totalUsers') }}</p>
                <p class="text-2xl sm:text-3xl font-bold text-blue-600">{{ stats.overview?.total_users || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ stats.recent_activity?.new_users_7d || 0 }} {{ $t('admin.thisWeek') }}
                </p>
              </div>
              <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Books Card -->
          <div class="bg-white rounded-lg shadow-sm border p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-600">{{ $t('admin.totalBooks') }}</p>
                <p class="text-2xl sm:text-3xl font-bold text-purple-600">{{ stats.overview?.total_books || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ stats.overview?.completed_books || 0 }} {{ $t('admin.completed') }}
                </p>
              </div>
              <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Threads Card -->
          <div class="bg-white rounded-lg shadow-sm border p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-600">{{ $t('admin.totalThreads') }}</p>
                <p class="text-2xl sm:text-3xl font-bold text-green-600">{{ stats.overview?.total_threads || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ stats.recent_activity?.new_threads_7d || 0 }} {{ $t('admin.thisWeek') }}
                </p>
              </div>
              <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Comments Card -->
          <div class="bg-white rounded-lg shadow-sm border p-4 sm:p-6">
            <div class="flex items-center justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-600">{{ $t('admin.totalComments') }}</p>
                <p class="text-2xl sm:text-3xl font-bold text-orange-600">{{ stats.overview?.total_comments || 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">
                  {{ stats.recent_activity?.new_comments_7d || 0 }} {{ $t('admin.thisWeek') }}
                </p>
              </div>
              <div class="w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Additional Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
          <div class="bg-white rounded-lg shadow-sm border p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-gray-600">{{ $t('admin.activeUsers30d') }}</p>
            <p class="text-xl sm:text-2xl font-bold text-gray-900">{{ stats.overview?.active_users_30d || 0 }}</p>
          </div>
          <div class="bg-white rounded-lg shadow-sm border p-3 sm:p-4">
            <p class="text-xs sm:text-sm text-gray-600">{{ $t('admin.flaggedUsers') }}</p>
            <p class="text-xl sm:text-2xl font-bold text-red-600">{{ stats.overview?.flagged_users || 0 }}</p>
          </div>
        </div>

        <!-- Popular Books -->
        <div class="bg-white rounded-lg shadow-sm border mb-4 sm:mb-6">
          <div class="px-4 sm:px-6 py-3 sm:py-4 border-b">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900">{{ $t('admin.popularBooks') }}</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('admin.title') }}</th>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">{{ $t('admin.author') }}</th>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('admin.threads') }}</th>
                  <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('admin.comments') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="book in stats.popular_books" :key="book.book_id" class="hover:bg-gray-50">
                  <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm">
                    <div class="font-medium text-gray-900 truncate max-w-[150px] sm:max-w-none">{{ book.title }}</div>
                    <div class="text-gray-600 text-xs sm:hidden">{{ book.author }}</div>
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 text-gray-600 text-sm hidden sm:table-cell">{{ book.author }}</td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 text-sm">{{ book.threads_count }}</td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-gray-900 text-sm">{{ book.comments_count }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        </div>

        <!-- User Management Tab -->
        <div v-show="activeTab === 'users'">
          <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b">
              <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4">{{ $t('admin.userManagement') }}</h2>
              
              <!-- Search and Filter -->
              <div class="flex flex-col sm:flex-row gap-3">
                <input
                  v-model="userSearch"
                  @input="searchUsers"
                  type="text"
                  :placeholder="$t('admin.searchUsers')"
                  class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <select
                  v-model="userRoleFilter"
                  @change="filterUsers"
                  class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">{{ $t('admin.allRoles') }}</option>
                  <option value="user">{{ $t('admin.regularUsers') }}</option>
                  <option value="admin">{{ $t('admin.admins') }}</option>
                </select>
              </div>
            </div>

            <!-- Users List -->
            <div v-if="usersLoading" class="p-6 text-center">
              <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
            <div v-else-if="users.length === 0" class="p-6 text-center text-gray-500">
              {{ $t('admin.noUsersFound') }}
            </div>
            <div v-else class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('admin.username') }}</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">{{ $t('admin.email') }}</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('admin.role') }}</th>
                    <th class="px-3 sm:px-6 py-2 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $t('admin.actions') }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="user in users" :key="user.user_id" class="hover:bg-gray-50">
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                      <router-link :to="`/profile/${user.user_id}`" class="text-sm font-medium text-blue-600 hover:underline">
                        {{ user.username }}
                      </router-link>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4 text-sm text-gray-600 hidden md:table-cell">{{ user.email }}</td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                      <span :class="[
                        'px-2 py-1 text-xs rounded',
                        user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'
                      ]">
                        {{ user.role }}
                      </span>
                    </td>
                    <td class="px-3 sm:px-6 py-3 sm:py-4">
                      <div class="flex gap-2">
                        <button
                          v-if="user.role === 'user' && currentUserId !== user.user_id"
                          @click="makeAdmin(user.user_id)"
                          class="px-3 py-1 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 transition-colors"
                        >
                          {{ $t('admin.makeAdmin') }}
                        </button>
                        <button
                          v-if="user.role === 'admin' && currentUserId !== user.user_id"
                          @click="removeAdmin(user.user_id)"
                          class="px-3 py-1 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors"
                        >
                          {{ $t('admin.removeAdmin') }}
                        </button>
                        <span v-if="currentUserId === user.user_id" class="text-xs text-gray-500 italic">
                          {{ $t('admin.you') }}
                        </span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="usersPagination.total > usersPagination.per_page" class="px-4 sm:px-6 py-4 border-t flex items-center justify-between">
              <div class="text-sm text-gray-600">
                {{ $t('pagination.showing') }} {{ ((usersPagination.current_page - 1) * usersPagination.per_page) + 1 }} - 
                {{ Math.min(usersPagination.current_page * usersPagination.per_page, usersPagination.total) }} 
                {{ $t('pagination.of') }} {{ usersPagination.total }}
              </div>
              <div class="flex gap-2">
                <button
                  @click="loadUsersPage(usersPagination.current_page - 1)"
                  :disabled="usersPagination.current_page === 1"
                  class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ $t('pagination.previous') }}
                </button>
                <button
                  @click="loadUsersPage(usersPagination.current_page + 1)"
                  :disabled="usersPagination.current_page === usersPagination.last_page"
                  class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ $t('pagination.next') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Flagged Users Section -->
        <div v-show="activeTab === 'flagged'">
          <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-900">{{ $t('admin.flaggedUsers') }}</h2>
            <button 
              @click="refreshFlaggedUsers" 
              class="w-full sm:w-auto px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              {{ $t('common.refresh') }}
            </button>
          </div>
          <div v-if="flaggedUsers.length === 0" class="p-4 sm:p-6 text-center text-gray-500">
            {{ $t('admin.noFlaggedUsers') }}
          </div>
          <div v-else class="divide-y divide-gray-200">
            <div v-for="user in paginatedFlaggedUsers" :key="user.user_id" class="p-4 sm:p-6">
              <div class="flex flex-col sm:flex-row items-start justify-between gap-3">
                <div class="flex-1 min-w-0 w-full sm:w-auto">
                  <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                    <router-link :to="`/profile/${user.user_id}`" class="text-base sm:text-lg font-semibold text-blue-600 hover:underline truncate">
                      {{ user.username }}
                    </router-link>
                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded whitespace-nowrap">{{ $t('admin.flagged') }}</span>
                  </div>
                  <p class="text-xs sm:text-sm text-gray-600 mb-1 truncate">{{ user.email }}</p>
                  <p class="text-xs sm:text-sm text-gray-900 mb-2"><strong>{{ $t('admin.reason') }}:</strong> {{ user.flag_reason }}</p>
                  <p class="text-xs text-gray-500">
                    {{ $t('admin.flaggedBy') }}: {{ user.flagged_by?.username || 'Unknown' }} | 
                    {{ $t('admin.flaggedAt') }}: {{ formatDate(user.flagged_at) }}
                  </p>
                </div>
                <button
                  @click="unflagUser(user.user_id)"
                  class="w-full sm:w-auto px-4 py-2 text-sm bg-green-600 text-white rounded hover:bg-green-700 whitespace-nowrap"
                >
                  {{ $t('admin.unflag') }}
                </button>
              </div>
            </div>
          </div>
          
          <!-- Pagination Controls -->
          <div v-if="flaggedUsers.length > 0 && totalPages > 1" class="px-4 sm:px-6 py-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="text-sm text-gray-600">
                {{ $t('pagination.showing') }} {{ ((currentPage - 1) * itemsPerPage) + 1 }} - {{ Math.min(currentPage * itemsPerPage, flaggedUsers.length) }} {{ $t('pagination.of') }} {{ flaggedUsers.length }}
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="prevPage"
                  :disabled="currentPage === 1"
                  class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <span class="hidden sm:inline">{{ $t('pagination.previous') }}</span>
                  <span class="sm:hidden">‹</span>
                </button>
                <button
                  v-for="page in paginationRange"
                  :key="page"
                  @click="goToPage(page)"
                  :class="[
                    'px-3 py-2 text-sm rounded-lg transition-colors',
                    page === currentPage
                      ? 'bg-blue-600 text-white'
                      : 'border hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                  <span class="hidden sm:inline">{{ $t('pagination.next') }}</span>
                  <span class="sm:hidden">›</span>
                </button>
              </div>
            </div>
          </div>
          </div>
          </div>
          </div>
          </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const authStore = useAuthStore();
const router = useRouter();
const { t } = useI18n();

const loading = ref(true);
const error = ref(null);
const stats = ref({});
const flaggedUsers = ref([]);
const activeTab = ref('overview');

// User Management
const users = ref([]);
const usersLoading = ref(false);
const userSearch = ref('');
const userRoleFilter = ref('');
const usersPagination = ref({
  current_page: 1,
  per_page: 50,
  total: 0,
  last_page: 1
});
const currentUserId = computed(() => authStore.user?.user_id);

// Pagination for flagged users
const currentPage = ref(1);
const itemsPerPage = ref(10);

const totalPages = computed(() => {
  return Math.ceil(flaggedUsers.value.length / itemsPerPage.value);
});

const paginatedFlaggedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return flaggedUsers.value.slice(start, end);
});

const paginationRange = computed(() => {
  const range = [];
  const showPages = 5;
  let start = Math.max(1, currentPage.value - Math.floor(showPages / 2));
  let end = Math.min(totalPages.value, start + showPages - 1);
  
  if (end - start + 1 < showPages) {
    start = Math.max(1, end - showPages + 1);
  }
  
  for (let i = start; i <= end; i++) {
    range.push(i);
  }
  return range;
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    goToPage(currentPage.value - 1);
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    goToPage(currentPage.value + 1);
  }
};

const fetchStatistics = async () => {
  try {
    loading.value = true;
    error.value = null;

    const response = await fetch('/api/admin/statistics', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      const data = await response.json();
      stats.value = data.data;
    } else if (response.status === 403) {
      error.value = t('admin.unauthorized');
      router.push('/');
    } else {
      error.value = t('admin.fetchError');
    }
  } catch (err) {
    error.value = t('common.networkError');
  } finally {
    loading.value = false;
  }
};

const fetchFlaggedUsers = async () => {
  try {
    const response = await fetch('/api/moderation/flagged-users', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      const data = await response.json();
      flaggedUsers.value = data.data;
    }
  } catch (err) {
    console.error('Failed to fetch flagged users:', err);
  }
};

const unflagUser = async (userId) => {
  if (!confirm(t('admin.confirmUnflag'))) return;

  try {
    const response = await fetch(`/api/moderation/unflag-user/${userId}`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      await refreshFlaggedUsers();
      await fetchStatistics();
    } else {
      alert(t('admin.unflagError'));
    }
  } catch (err) {
    alert(t('common.networkError'));
  }
};

const refreshFlaggedUsers = async () => {
  await fetchFlaggedUsers();
};

const formatDate = (date) => {
  if (!date) return '--';
  const d = new Date(date);
  return d.toLocaleDateString('lv-LV', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

// User Management Functions
const loadUsers = async (page = 1) => {
  try {
    usersLoading.value = true;
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: usersPagination.value.per_page.toString()
    });

    if (userSearch.value) {
      params.append('search', userSearch.value);
    }

    if (userRoleFilter.value) {
      params.append('role', userRoleFilter.value);
    }

    const response = await fetch(`/api/admin/users?${params}`, {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      const data = await response.json();
      users.value = data.data;
      usersPagination.value = data.pagination;
    }
  } catch (err) {
    console.error('Failed to load users:', err);
  } finally {
    usersLoading.value = false;
  }
};

const loadUsersPage = (page) => {
  if (page >= 1 && page <= usersPagination.value.last_page) {
    loadUsers(page);
  }
};

const searchUsers = () => {
  loadUsers(1);
};

const filterUsers = () => {
  loadUsers(1);
};

const makeAdmin = async (userId) => {
  if (!confirm(t('admin.confirmMakeAdmin'))) return;

  try {
    const response = await fetch(`/api/admin/users/${userId}/make-admin`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      await loadUsers(usersPagination.value.current_page);
      await fetchStatistics();
    } else {
      const data = await response.json();
      alert(data.message || t('admin.makeAdminError'));
    }
  } catch (err) {
    alert(t('common.networkError'));
  }
};

const removeAdmin = async (userId) => {
  if (!confirm(t('admin.confirmRemoveAdmin'))) return;

  try {
    const response = await fetch(`/api/admin/users/${userId}/remove-admin`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      await loadUsers(usersPagination.value.current_page);
      await fetchStatistics();
    } else {
      const data = await response.json();
      alert(data.message || t('admin.removeAdminError'));
    }
  } catch (err) {
    alert(t('common.networkError'));
  }
};

onMounted(async () => {
  await fetchStatistics();
  await fetchFlaggedUsers();
  await loadUsers();
});
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background-color: #f9fafb;
}
</style>
