<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">{{ t('common.loading') }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6">
      {{ error }}
    </div>

    <template v-else-if="discussion">
      <!-- Breadcrumb Navigation -->
      <nav class="flex mb-6 text-sm text-gray-600">
        <router-link to="/" class="hover:text-blue-600">{{ t('nav.home') }}</router-link>
        <span class="mx-2">›</span>
        <router-link to="/books" class="hover:text-blue-600">{{ t('nav.books') }}</router-link>
        <span class="mx-2">›</span>
        <router-link :to="`/books/${book?.isbn || book?.isbn13 || book?.isbn10}`" class="hover:text-blue-600">
          {{ book?.title || 'Book' }}
        </router-link>
        <span class="mx-2">›</span>
        <span class="text-gray-900">{{ discussion.title }}</span>
      </nav>

      <!-- Discussion Header -->
      <div class="bg-white rounded-lg shadow-sm border p-6 mb-6 flex items-start gap-4">
        <!-- Upvote Section -->
        <div class="flex flex-col items-center gap-1 flex-shrink-0">
          <button
            v-if="authStore.isAuthenticated"
            @click="toggleThreadLike"
            class="p-1 rounded hover:bg-gray-200 transition-colors"
            :class="discussion.user_liked ? 'text-orange-500' : 'text-gray-400'"
          >
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 4l3.09 6.26L22 11.27l-5 4.87 1.18 6.88L12 19.77l-6.18 3.25L7 16.14 2 11.27l6.91-1.01L12 4z"></path>
            </svg>
          </button>
          <span class="text-sm font-medium" :class="discussion.user_liked ? 'text-orange-500' : 'text-gray-700'">
            {{ discussion.likes_count || 0 }}
          </span>
        </div>

        <!-- Discussion Content -->
        <div class="flex-1">
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ discussion.title }}</h1>
              <div class="flex items-center text-sm text-gray-600 space-x-4">
                <button 
                  @click="goToUserProfile(discussion.author?.id)" 
                  class="flex items-center hover:text-blue-600 transition-colors"
                >
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                  </svg>
                  {{ discussion.author?.name || 'Unknown' }}
                </button>
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                  </svg>
                  {{ formatDate(discussion.created_at) }}
                </span>
                <span v-if="discussion.scope === 'page' && discussion.page_number" class="flex items-center text-blue-600">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                  </svg>
                  {{ t('discussions.pageNumber') }}: {{ discussion.page_number }}
                </span>
              </div>
            </div>
          </div>

          <!-- Discussion Content -->
          <div class="prose max-w-none">
            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ discussion.content }}</p>
          </div>
        </div>
      </div>

      <!-- Comments Section -->
      <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b">
          <h2 class="text-lg font-semibold text-gray-900">
            {{ t('discussions.replies') }} ({{ comments.length }})
          </h2>
        </div>

        <!-- Add Comment Form -->
        <div v-if="authStore.isAuthenticated" class="px-6 py-4 border-b bg-gray-50">
          <form @submit.prevent="handleAddComment" class="space-y-3">
            <textarea
              v-model="newComment"
              :placeholder="t('discussions.addComment')"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
            ></textarea>
            <div class="flex justify-end">
              <button
                type="submit"
                :disabled="commentLoading || !newComment.trim()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ commentLoading ? t('common.loading') : t('discussions.reply') }}
              </button>
            </div>
          </form>
        </div>

        <!-- Comments List -->
        <div v-if="comments.length > 0" class="divide-y">
          <div
            v-for="comment in comments"
            :key="comment.id"
            class="p-6 hover:bg-gray-50 transition-colors flex items-start gap-4"
          >
            <!-- Upvote Section for Comment -->
            <div class="flex flex-col items-center gap-1 flex-shrink-0">
              <button
                v-if="authStore.isAuthenticated"
                @click="toggleCommentLike(comment.id)"
                class="p-1 rounded hover:bg-gray-200 transition-colors"
                :class="comment.user_liked ? 'text-orange-500' : 'text-gray-400'"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 4l3.09 6.26L22 11.27l-5 4.87 1.18 6.88L12 19.77l-6.18 3.25L7 16.14 2 11.27l6.91-1.01L12 4z"></path>
                </svg>
              </button>
              <span class="text-xs font-medium" :class="comment.user_liked ? 'text-orange-500' : 'text-gray-600'">
                {{ comment.likes_count || 0 }}
              </span>
            </div>

            <!-- Comment Content -->
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <button 
                  @click="goToUserProfile(comment.author?.id)" 
                  class="font-medium text-gray-900 hover:text-blue-600 transition-colors"
                >
                  {{ comment.author?.name || 'Unknown' }}
                </button>
                <span class="text-xs text-gray-500">{{ formatDate(comment.created_at) }}</span>
              </div>
              <p class="text-gray-700 whitespace-pre-wrap">{{ comment.content }}</p>
            </div>
          </div>
        </div>

        <!-- Empty Comments State -->
        <div v-else class="p-6">
          <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('discussions.noComments') }}</h3>
            <p class="mt-2 text-gray-500">{{ t('discussions.beFirstToComment') }}</p>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../stores/auth.js';
import { useDiscussionsStore } from '../stores/discussions.js';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();
const discussionsStore = useDiscussionsStore();

const discussion = ref(null);
const book = ref(null);
const comments = ref([]);
const newComment = ref('');
const loading = ref(true);
const error = ref(null);
const commentLoading = ref(false);

const fetchDiscussion = async () => {
  loading.value = true;
  error.value = null;
  
  const result = await discussionsStore.fetchDiscussion(route.params.discussionId);
  
  if (result.success) {
    discussion.value = result.data;
    book.value = discussion.value.book;
    
    // Fetch like status for thread
    if (authStore.isAuthenticated) {
      await fetchThreadLikeStatus();
    }
    
    await fetchComments();
  } else {
    error.value = t('discussions.notFound') || 'Discussion not found';
  }
  
  loading.value = false;
};

const fetchComments = async () => {
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/threads/${route.params.discussionId}/comments`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': token ? `Bearer ${token}` : ''
      }
    });
    
    if (response.ok) {
      const data = await response.json();
      comments.value = data.data || [];
      
      // Fetch like status for each comment
      if (authStore.isAuthenticated) {
        await fetchCommentsLikeStatuses();
      }
    }
  } catch (err) {
    console.error('Failed to fetch comments:', err);
  }
};

const fetchThreadLikeStatus = async () => {
  const token = localStorage.getItem('auth_token');
  try {
    const response = await fetch(`/api/likes/status?target_type=thread&target_id=${discussion.value.id}`, {
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });
    if (response.ok) {
      const data = await response.json();
      discussion.value.user_liked = data.user_liked;
      discussion.value.likes_count = data.like_count;
    }
  } catch (err) {
    console.error('Failed to fetch like status', err);
  }
};

const fetchCommentsLikeStatuses = async () => {
  const token = localStorage.getItem('auth_token');
  for (const comment of comments.value) {
    try {
      const response = await fetch(`/api/likes/status?target_type=comment&target_id=${comment.id}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      if (response.ok) {
        const data = await response.json();
        comment.user_liked = data.user_liked;
        comment.likes_count = data.like_count;
      }
    } catch (err) {
      console.error('Failed to fetch comment like status', err);
    }
  }
};

const toggleThreadLike = async () => {
  if (!authStore.isAuthenticated) return;
  
  const token = localStorage.getItem('auth_token');
  try {
    const response = await fetch('/api/likes/toggle', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        target_type: 'thread',
        target_id: discussion.value.id
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      discussion.value.user_liked = data.liked;
      discussion.value.likes_count = (discussion.value.likes_count || 0) + (data.liked ? 1 : -1);
    }
  } catch (err) {
    console.error('Failed to toggle like', err);
  }
};

const toggleCommentLike = async (commentId) => {
  if (!authStore.isAuthenticated) return;
  
  const token = localStorage.getItem('auth_token');
  try {
    const response = await fetch('/api/likes/toggle', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        target_type: 'comment',
        target_id: commentId
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      const comment = comments.value.find(c => c.id === commentId);
      if (comment) {
        comment.user_liked = data.liked;
        comment.likes_count = (comment.likes_count || 0) + (data.liked ? 1 : -1);
      }
    }
  } catch (err) {
    console.error('Failed to toggle comment like', err);
  }
};

const handleAddComment = async () => {
  if (!newComment.value.trim()) return;
  
  commentLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/threads/${route.params.discussionId}/comments`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        content: newComment.value
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      const comment = data.data || data;
      comment.user_liked = false;
      comment.likes_count = 0;
      comments.value.push(comment);
      newComment.value = '';
    }
  } catch (err) {
    console.error('Failed to add comment:', err);
  } finally {
    commentLoading.value = false;
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

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleDateString('lv-LV', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

onMounted(() => {
  fetchDiscussion();
});
</script>
