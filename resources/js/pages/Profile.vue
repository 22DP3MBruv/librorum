<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">{{ t('common.loading') }}</p>
    </div>

    <template v-else>
      <!-- Page Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ t('profile.title') }}</h1>
        <p class="text-gray-600">{{ t('profile.subtitle') }}</p>
      </div>

      <!-- Profile Cards Grid -->
      <div class="grid md:grid-cols-3 gap-6 mb-8">
        <!-- User Info Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <div class="text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full mx-auto mb-4 flex items-center justify-center">
              <span class="text-2xl font-bold text-white">{{ userInitial }}</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ authStore.user?.name || 'User' }}</h3>
            <p class="text-gray-500 text-sm">{{ t('profile.joined') }}: {{ formatDate(authStore.user?.created_at) }}</p>
          </div>
        </div>

        <!-- Reading Stats Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('profile.readingStats') }}</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600">{{ t('profile.booksRead') }}:</span>
              <span class="font-medium text-purple-600">{{ progressStore.progressCount.read }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">{{ t('profile.currentlyReading') }}:</span>
              <span class="font-medium text-green-600">{{ progressStore.progressCount.reading }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">{{ t('profile.planToRead') }}:</span>
              <span class="font-medium text-blue-600">{{ progressStore.progressCount.wantToRead }}</span>
            </div>
          </div>
        </div>

        <!-- Social Stats Card -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('profile.social') }}</h3>
          <div class="space-y-3">
            <button
              @click="showFollowers = !showFollowers"
              class="w-full flex justify-between items-center hover:bg-gray-50 p-2 rounded transition-colors"
            >
              <span class="text-gray-600">{{ t('profile.followers') }}:</span>
              <div class="flex items-center gap-2">
                <span class="font-medium">{{ followers.length }}</span>
                <svg 
                  class="w-4 h-4 text-gray-400 transition-transform" 
                  :class="{ 'rotate-180': showFollowers }"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </button>
            <button
              @click="showFollowing = !showFollowing"
              class="w-full flex justify-between items-center hover:bg-gray-50 p-2 rounded transition-colors"
            >
              <span class="text-gray-600">{{ t('profile.following') }}:</span>
              <div class="flex items-center gap-2">
                <span class="font-medium">{{ following.length }}</span>
                <svg 
                  class="w-4 h-4 text-gray-400 transition-transform" 
                  :class="{ 'rotate-180': showFollowing }"
                  fill="none" 
                  stroke="currentColor" 
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Followers List -->
      <div v-if="showFollowers && followers.length > 0" class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b">
          <h3 class="text-lg font-semibold text-gray-900">{{ t('profile.followersList') }}</h3>
        </div>
        <div class="divide-y">
          <div
            v-for="follower in followers"
            :key="follower.user_id"
            class="px-6 py-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                  <span class="text-sm font-bold text-white">{{ follower.name?.charAt(0).toUpperCase() }}</span>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ follower.name || follower.username }}</p>
                  <p class="text-sm text-gray-500">{{ t('profile.followedOn') }} {{ formatDate(follower.pivot?.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Following List -->
      <div v-if="showFollowing && following.length > 0" class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b">
          <h3 class="text-lg font-semibold text-gray-900">{{ t('profile.followingList') }}</h3>
        </div>
        <div class="divide-y">
          <div
            v-for="user in following"
            :key="user.user_id"
            class="px-6 py-4 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-teal-600 rounded-full flex items-center justify-center">
                  <span class="text-sm font-bold text-white">{{ user.name?.charAt(0).toUpperCase() }}</span>
                </div>
                <div>
                  <p class="font-medium text-gray-900">{{ user.name || user.username }}</p>
                  <p class="text-sm text-gray-500">{{ t('profile.followingSince') }} {{ formatDate(user.pivot?.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Reading Activity -->
      <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-semibold text-gray-900">{{ t('profile.recentReadingActivity') }}</h2>
        </div>
        <div v-if="recentProgress.length > 0" class="divide-y">
          <div
            v-for="progress in recentProgress"
            :key="progress.id"
            class="px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer"
            @click="goToBook(progress.book)"
          >
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 w-12 h-16 bg-gray-200 rounded overflow-hidden">
                <img
                  v-if="progress.book?.cover_image_url"
                  :src="progress.book.cover_image_url"
                  :alt="progress.book.title"
                  class="w-full h-full object-cover"
                >
                <div v-else class="w-full h-full flex items-center justify-center">
                  <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                  </svg>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-medium text-gray-900 mb-1">{{ progress.book?.title }}</h4>
                <p class="text-sm text-gray-600 mb-2">{{ progress.book?.author }}</p>
                <div class="flex items-center gap-3 text-sm">
                  <span 
                    class="px-2 py-1 rounded text-xs font-medium"
                    :class="{
                      'bg-blue-100 text-blue-800': progress.status === 'want_to_read',
                      'bg-green-100 text-green-800': progress.status === 'reading',
                      'bg-purple-100 text-purple-800': progress.status === 'completed',
                      'bg-gray-100 text-gray-800': progress.status === 'dropped'
                    }"
                  >
                    {{ getStatusLabel(progress.status) }}
                  </span>
                  <span class="text-gray-500">{{ formatDate(progress.updated_at) }}</span>
                  <span v-if="progress.status === 'reading' && progress.book?.page_count" class="text-gray-500">
                    {{ Math.round((progress.current_page / progress.book.page_count) * 100) }}% {{ t('reading.complete') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="p-6">
          <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <p class="mt-2 text-gray-500">{{ t('profile.noRecentActivity') }}</p>
            <router-link
              to="/books"
              class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              {{ t('reading.browseBooks') }}
            </router-link>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';
import { useReadingProgressStore } from '../stores/readingProgress.js';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const progressStore = useReadingProgressStore();

const loading = ref(true);
const followers = ref([]);
const following = ref([]);
const showFollowers = ref(false);
const showFollowing = ref(false);

// Computed
const userInitial = computed(() => {
  return authStore.user?.name?.charAt(0).toUpperCase() || '?';
});

const recentProgress = computed(() => {
  return [...progressStore.progressList]
    .sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at))
    .slice(0, 5);
});

// Methods
const formatDate = (date) => {
  if (!date) return '--';
  const d = new Date(date);
  return d.toLocaleDateString('lv-LV', { year: 'numeric', month: 'long', day: 'numeric' });
};

const getStatusLabel = (status) => {
  const labels = {
    'want_to_read': t('reading.wantToRead'),
    'reading': t('reading.currentlyReading'),
    'completed': t('reading.completed'),
    'dropped': t('reading.dropped')
  };
  return labels[status] || status;
};

const goToBook = (book) => {
  if (book) {
    const isbn = book.isbn || book.isbn13 || book.isbn10;
    if (isbn) {
      router.push(`/books/${isbn}`);
    }
  }
};

const fetchFollowers = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user/followers', {
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      followers.value = data.data || data || [];
    }
  } catch (err) {
    console.error('Failed to fetch followers:', err);
    followers.value = [];
  }
};

const fetchFollowing = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user/following', {
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      following.value = data.data || data || [];
    }
  } catch (err) {
    console.error('Failed to fetch following:', err);
    following.value = [];
  }
};

// Lifecycle
onMounted(async () => {
  loading.value = true;
  await Promise.all([
    progressStore.fetchProgress(),
    fetchFollowers(),
    fetchFollowing()
  ]);
  loading.value = false;
});
</script>
