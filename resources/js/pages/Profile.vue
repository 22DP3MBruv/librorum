<template>
  <div class="container mx-auto px-4 sm:px-6 py-6">
    <!-- Error Notification -->
    <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg flex items-start gap-3">
      <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
      </svg>
      <div class="flex-1">
        <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
      </div>
      <button @click="errorMessage = ''" class="text-red-400 hover:text-red-600">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
      </button>
    </div>

    <!-- Success Notification -->
    <div v-if="successMessage" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-start gap-3">
      <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
      </svg>
      <div class="flex-1">
        <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
      </div>
      <button @click="successMessage = ''" class="text-green-400 hover:text-green-600">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">{{ t('common.loading') }}</p>
    </div>

    <!-- User Not Found -->
    <div v-else-if="userNotFound" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
      <p class="text-gray-500 text-lg">{{ t('profile.userNotFound') }}</p>
    </div>

    <template v-else>
      <!-- User Section -->
      <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="p-4 sm:p-6 lg:p-8">
          <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-6">
            <!-- User Avatar -->
            <div class="flex-shrink-0">
              <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-2xl sm:text-3xl font-bold text-white">{{ userInitial }}</span>
              </div>
            </div>

            <!-- User Information -->
            <div class="flex-1 text-center sm:text-left">
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">
                {{ profileUser?.name || profileUser?.username || 'User' }}
              </h1>
              <p class="text-sm sm:text-base text-gray-600 mb-4">
                <span>{{ t('profile.joined') }}: {{ formatDate(profileUser?.created_at) }}</span>
              </p>

              <!-- Reading Statistics -->
              <div class="flex flex-wrap gap-3 sm:gap-4 lg:gap-6 items-center justify-center sm:justify-start text-sm sm:text-base">
                <button
                  @click="toggleFollowersSection"
                  class="flex items-center gap-1.5 sm:gap-2 hover:text-blue-600 transition-colors group"
                >
                  <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ followers.length }}</span>
                  <span class="text-xs sm:text-sm text-gray-600">{{ t('profile.followers') }}</span>
                  <svg 
                    class="w-4 h-4 text-gray-500 group-hover:text-blue-600 transition-transform"
                    :class="{ 'rotate-180': showFollowers }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                  </svg>
                </button>
                <button
                  @click="toggleFollowingSection"
                  class="flex items-center gap-1.5 sm:gap-2 hover:text-blue-600 transition-colors group"
                >
                  <span class="text-xl sm:text-2xl font-bold text-gray-900">{{ following.length }}</span>
                  <span class="text-xs sm:text-sm text-gray-600">{{ t('profile.following') }}</span>
                  <svg 
                    class="w-4 h-4 text-gray-500 group-hover:text-blue-600 transition-transform"
                    :class="{ 'rotate-180': showFollowing }"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                  </svg>
                </button>
                
                <!-- Reading Statistics -->
                <div class="flex items-center gap-1.5 sm:gap-2">
                  <span class="text-xl sm:text-2xl font-bold text-purple-600">{{ displayProgressCount.read }}</span>
                  <span class="text-xs sm:text-sm text-gray-600">{{ t('profile.booksRead') }}</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                  <span class="text-xl sm:text-2xl font-bold text-green-600">{{ displayProgressCount.reading }}</span>
                  <span class="text-xs sm:text-sm text-gray-600">{{ t('profile.currentlyReading') }}</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                  <span class="text-xl sm:text-2xl font-bold text-blue-600">{{ displayProgressCount.wantToRead }}</span>
                  <span class="text-xs sm:text-sm text-gray-600">{{ t('profile.planToRead') }}</span>
                </div>
              </div>
            </div>

            <!-- Follow/Unfollow button -->
            <div v-if="!isOwnProfile" class="flex-shrink-0 flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
              <button
                v-if="profileUser?.can_be_followed"
                @click="toggleFollow"
                :disabled="followLoading"
                class="px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-medium transition-all whitespace-nowrap text-sm sm:text-base w-full sm:w-auto"
                :class="{
                  'bg-gray-200 text-gray-700 hover:bg-gray-300': isFollowing,
                  'bg-yellow-500 text-white hover:bg-yellow-600': hasPendingRequest,
                  'bg-blue-600 text-white hover:bg-blue-700': !isFollowing && !hasPendingRequest
                }"
              >
                <span v-if="followLoading">...</span>
                <span v-else-if="hasPendingRequest">{{ t('profile.cancelRequest') }}</span>
                <span v-else>{{ isFollowing ? t('profile.unfollow') : t('profile.follow') }}</span>
              </button>
              <div v-else class="text-xs sm:text-sm text-gray-500 text-center">
                {{ t('profile.notAcceptingFollowers') }}
              </div>
              
              <!-- User Flagging Button -->
              <button
                v-if="authStore.user && (authStore.user.role === 'admin' || authStore.user.role === 'moderator') && !profileUser?.is_flagged"
                @click="showFlagModal = true"
                class="px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-medium transition-all whitespace-nowrap bg-red-600 text-white hover:bg-red-700 text-sm sm:text-base w-full sm:w-auto"
              >
                {{ t('profile.flagUser') }}
              </button>
            </div>
          </div>
          
          <!-- Privacy Settings (Own Profile) -->
          <div v-if="isOwnProfile" class="mt-4 flex justify-end gap-3">
            <router-link
              to="/privacy-settings"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
              {{ t('privacySettings.title') }}
            </router-link>
            <router-link
              to="/account-settings"
              class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              {{ t('accountSettings.title') }}
            </router-link>
          </div>
        </div>
      </div>

      <!-- Recent Reading Activity -->
      <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b">
          <h2 class="text-xl font-semibold text-gray-900">{{ t('profile.recentReadingActivity') }}</h2>
        </div>
        
        <!-- Privacy Message -->
        <div v-if="!isOwnProfile && profileUser && !profileUser.can_view_reading_progress" class="p-6">
          <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            <p class="mt-2 text-gray-500">{{ t('profile.readingProgressPrivate') }}</p>
          </div>
        </div>
        
        <div v-else-if="recentProgress.length > 0" class="divide-y">
          <div
            v-for="progress in recentProgress"
            :key="progress.id"
            class="px-4 sm:px-6 py-3 sm:py-4 hover:bg-gray-50 transition-colors relative group"
          >
            <!-- Bookmark Button -->
            <button
              v-if="authStore.isAuthenticated"
              @click.stop="toggleBookmark(progress.book)"
              class="absolute top-3 right-3 sm:top-4 sm:right-4 z-10 bg-white rounded-full p-1.5 sm:p-2 shadow-md hover:shadow-lg transition-all"
              :class="isBookmarked(progress.book?.id) ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600'"
            >
              <svg class="w-5 h-5" :fill="isBookmarked(progress.book?.id) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
              </svg>
            </button>
            
            <div class="flex items-start gap-3 sm:gap-4 cursor-pointer" @click="goToBook(progress.book)">
              <div class="flex-shrink-0 w-10 h-14 sm:w-12 sm:h-16 bg-gray-200 rounded overflow-hidden">
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
              v-if="isOwnProfile"
              to="/books"
              class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
            >
              {{ t('reading.browseBooks') }}
            </router-link>
          </div>
        </div>
      </div>

      <!-- Expanded Followers List -->
      <div v-if="showFollowers && followers.length > 0" class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b">
          <h3 class="text-lg font-semibold text-gray-900">{{ t('profile.followersList') }}</h3>
        </div>
        <div class="divide-y">
          <div
            v-for="follower in followers"
            :key="follower.user_id"
            class="px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer"
            @click="goToUserProfile(follower.user_id)"
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

      <!-- Expanded Following List -->
      <div v-if="showFollowing && following.length > 0" class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="px-6 py-4 border-b">
          <h3 class="text-lg font-semibold text-gray-900">{{ t('profile.followingList') }}</h3>
        </div>
        <div class="divide-y">
          <div
            v-for="user in following"
            :key="user.user_id"
            class="px-6 py-4 hover:bg-gray-50 transition-colors cursor-pointer"
            @click="goToUserProfile(user.user_id)"
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
    </template>
    
    <!-- User Flagging Modal -->
    <div v-if="showFlagModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ t('profile.flagUserTitle') }}</h3>
        <p class="text-gray-600 mb-4">{{ t('profile.flagUserDescription') }}</p>
        <textarea
          v-model="flagReason"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          rows="4"
          :placeholder="t('profile.flagReasonPlaceholder')"
        ></textarea>
        <div class="flex gap-3 mt-4">
          <button
            @click="flagUser"
            :disabled="!flagReason.trim() || flaggingUser"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            {{ flaggingUser ? t('common.saving') : t('profile.flagUser') }}
          </button>
          <button
            @click="showFlagModal = false; flagReason = ''"
            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
          >
            {{ t('common.cancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';
import { useReadingProgressStore } from '../stores/readingProgress.js';

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const progressStore = useReadingProgressStore();

const loading = ref(true);
const followers = ref([]);
const following = ref([]);
const showFollowers = ref(false);
const showFollowing = ref(false);
const profileUser = ref(null);
const userNotFound = ref(false);
const isFollowing = ref(false);
const hasPendingRequest = ref(false);
const followLoading = ref(false);
const profileProgress = ref([]);
const profileProgressCount = ref({ read: 0, reading: 0, wantToRead: 0 });
const showFlagModal = ref(false);
const flagReason = ref('');
const flaggingUser = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// computed properties
const isOwnProfile = computed(() => {
  if (!route.params.userId) return true;
  return parseInt(route.params.userId) === authStore.user?.id;
});

const userInitial = computed(() => {
  const name = profileUser.value?.name || profileUser.value?.username || authStore.user?.name;
  return name?.charAt(0).toUpperCase() || '?';
});

const recentProgress = computed(() => {
  const progressList = isOwnProfile.value ? progressStore.progressList : profileProgress.value;
  return [...progressList]
    .sort((a, b) => new Date(b.updated_at) - new Date(a.updated_at))
    .slice(0, 5);
});

const displayProgressCount = computed(() => {
  return isOwnProfile.value ? progressStore.progressCount : profileProgressCount.value;
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

const goToUserProfile = (userId) => {
  if (!userId) return;
  if (parseInt(userId) === authStore.user?.id) {
    router.push('/profile');
  } else {
    router.push(`/profile/${userId}`);
  }
};

const fetchUserProfile = async (userId) => {
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/user/profile/${userId}`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      profileUser.value = data.data;
      isFollowing.value = data.data.is_following;
      // Forces hasPendingRequest to be true only if the user is not currently following and there is a pending request
      hasPendingRequest.value = !data.data.is_following && (data.data.has_pending_request || false);
      userNotFound.value = false;
    } else {
      userNotFound.value = true;
    }
  } catch (err) {
    console.error('Failed to fetch user profile:', err);
    userNotFound.value = true;
  }
};

const fetchFollowers = async (userId = null) => {
  try {
    const token = localStorage.getItem('auth_token');
    const url = userId ? `/api/user/${userId}/followers` : '/api/user/followers';
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      followers.value = data.data || data || [];
      console.log('Fetched followers:', followers.value);
    }
  } catch (err) {
    console.error('Failed to fetch followers:', err);
    followers.value = [];
  }
};

const fetchFollowing = async (userId = null) => {
  try {
    const token = localStorage.getItem('auth_token');
    const url = userId ? `/api/user/${userId}/following` : '/api/user/following';
    const response = await fetch(url, {
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      following.value = data.data || data || [];
      console.log('Fetched following:', following.value);
    }
  } catch (err) {
    console.error('Failed to fetch following:', err);
    following.value = [];
  }
};

const fetchUserProgress = async (userId) => {
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/reading-progress?user_id=${userId}`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      profileProgress.value = data.data || data || [];
      
      // Calculate counts for each status
      profileProgressCount.value = {
        read: profileProgress.value.filter(p => p.status === 'completed').length,
        reading: profileProgress.value.filter(p => p.status === 'reading').length,
        wantToRead: profileProgress.value.filter(p => p.status === 'want_to_read').length
      };
      console.log('Fetched user progress:', profileProgress.value, profileProgressCount.value);
    }
  } catch (err) {
    console.error('Failed to fetch user progress:', err);
    profileProgress.value = [];
    profileProgressCount.value = { read: 0, reading: 0, wantToRead: 0 };
  }
};

const toggleFollow = async () => {
  if (followLoading.value || !route.params.userId) return;
  
  followLoading.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    let url, method;
    
    if (hasPendingRequest.value) {
      // Cancel follow request
      url = `/api/user/follow-request/${route.params.userId}/cancel`;
      method = 'DELETE';
    } else if (isFollowing.value) {
      // Stop following
      url = `/api/user/unfollow/${route.params.userId}`;
      method = 'DELETE';
    } else {
      // Follow or send follow request
      url = `/api/user/follow/${route.params.userId}`;
      method = 'POST';
    }
    
    const response = await fetch(url, {
      method: method,
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });

    if (response.ok) {
      const data = await response.json();
      
      if (hasPendingRequest.value) {
        // Follow request cancelled
        hasPendingRequest.value = false;
      } else if (isFollowing.value) {
        // Follow stopped
        isFollowing.value = false;
      } else {
        // Check if follow request was sent or if user is now following immediately
        if (data.has_pending_request) {
          hasPendingRequest.value = true;
        } else {
          isFollowing.value = true;
        }
      }
      
      // Refresh followers/following lists if they are currently visible
      const targetUserId = !isOwnProfile.value ? route.params.userId : null;
      await Promise.all([
        fetchFollowers(targetUserId), 
        fetchFollowing(targetUserId)
      ]);
    }
  } catch (err) {
    console.error('Failed to toggle follow:', err);
  } finally {
    followLoading.value = false;
  }
};

const flagUser = async () => {
  if (!flagReason.value.trim() || !route.params.userId) return;
  
  flaggingUser.value = true;
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/moderation/flag-user/${route.params.userId}`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : '',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ reason: flagReason.value })
    });

    if (response.ok) {
      showFlagModal.value = false;
      flagReason.value = '';
      successMessage.value = t('profile.flagSuccess');
      setTimeout(() => { successMessage.value = ''; }, 5000);
      await loadProfile();
    } else {
      const data = await response.json();
      errorMessage.value = data.message || t('profile.flagError');
    }
  } catch (err) {
    console.error('Failed to flag user:', err);
    errorMessage.value = t('common.networkError');
  } finally {
    flaggingUser.value = false;
  }
};

const isBookmarked = (bookId) => {
  return progressStore.isBookInReadingList(bookId);
};

const toggleBookmark = async (book) => {
  if (!book?.id) {
    console.error('Book ID is missing:', book);
    errorMessage.value = 'Error: Book ID is missing';
    return;
  }
  
  const bookProgress = progressStore.getBookProgress(book.id);
  
  if (bookProgress) {
    // Remove from reading list
    const result = await progressStore.removeFromReadingList(bookProgress.id);
    if (!result.success) {
      errorMessage.value = result.message || t('books.removeBookmarkFailed');
    }
  } else {
    // Add to reading list with default status "want_to_read"
    const result = await progressStore.addToReadingList(book.id, 'want_to_read');
    if (!result.success) {
      errorMessage.value = result.message || t('books.addBookmarkFailed');
    }
  }
};

const loadProfile = async () => {
  loading.value = true;
  userNotFound.value = false;
  
  if (isOwnProfile.value) {
    // Own profile - use authenticated user data and fetch progress
    profileUser.value = authStore.user;
    await Promise.all([
      progressStore.fetchProgress()
    ]);
  } else {
    // Different user's profile - fetch data based on route param
    const userId = route.params.userId;
    await Promise.all([
      fetchUserProfile(userId),
      fetchUserProgress(userId)
    ]);
  }
  
  loading.value = false;
};

const toggleFollowersSection = async () => {
  showFollowers.value = !showFollowers.value;
  
  // Lazy-load followers data if not already loaded
  if (showFollowers.value && followers.value.length === 0) {
    const userId = !isOwnProfile.value ? route.params.userId : null;
    await fetchFollowers(userId);
  }
};

const toggleFollowingSection = async () => {
  showFollowing.value = !showFollowing.value;
  
  // Lazy-load following data if not already loaded
  if (showFollowing.value && following.value.length === 0) {
    const userId = !isOwnProfile.value ? route.params.userId : null;
    await fetchFollowing(userId);
  }
};

// React to route changes (e.g., navigating to another user's profile)
watch(() => route.params.userId, () => {
  if (route.name === 'Profile' || route.name === 'UserProfile') {
    loadProfile();
  }
});

// Lifecycle
onMounted(() => {
  loadProfile();
});
</script>
