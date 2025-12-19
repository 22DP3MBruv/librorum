<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-6 text-sm text-gray-600">
      <router-link to="/" class="hover:text-blue-600">{{ t('nav.home') }}</router-link>
      <span class="mx-2">›</span>
      <router-link to="/books" class="hover:text-blue-600">{{ t('nav.books') }}</router-link>
      <span class="mx-2">›</span>
      <span class="text-gray-900">{{ book?.title || t('discussions.bookDiscussions') }}</span>
    </nav>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">{{ t('common.loading') }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6">
      {{ error }}
    </div>

    <template v-else-if="book">
      <!-- Book Header -->
      <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex items-start gap-6">
          <!-- Left side: Book Cover -->
          <div class="w-60 h-90 bg-gray-200 rounded flex-shrink-0 overflow-hidden">
            <img
              v-if="book.cover_image_url"
              :src="book.cover_image_url"
              :alt="book.title"
              class="w-full h-full object-cover"
            >
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
          </div>

          <!-- Center: Book Details -->
          <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ book.title }}</h1>
            <p class="text-gray-600 mb-2">{{ book.author }}</p>
            <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 mb-3">
              <span v-if="book.isbn">ISBN: {{ book.isbn }}</span>
              <span v-if="book.page_count">{{ book.page_count }} {{ t('books.pages') }}</span>
              <span v-if="book.publication_year">{{ book.publication_year }}</span>
              <span v-if="book.genre" class="bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ book.genre }}</span>
            </div>
            <div v-if="book.description" class="text-sm text-gray-700">
              <p :class="descriptionExpanded ? '' : 'line-clamp-3'">{{ book.description }}</p>
              <button 
                @click="descriptionExpanded = !descriptionExpanded"
                class="text-blue-600 hover:text-blue-700 mt-2 text-sm font-medium"
              >
                {{ descriptionExpanded ? t('common.showLess') : t('common.showMore') }}
              </button>
            </div>
            <div v-if="authStore.isAuthenticated" class="mt-4">
              <button 
                @click="toggleBookmark"
                :class="[
                  'px-4 py-2 rounded-lg transition-colors flex items-center whitespace-nowrap',
                  isBookmarked ? 'bg-blue-600 text-white hover:bg-blue-700' : 'border border-blue-600 text-blue-600 hover:bg-blue-50'
                ]"
              >
                <svg class="w-5 h-5 mr-2 flex-shrink-0" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
                <span class="hidden sm:inline">{{ isBookmarked ? t('discussions.inReadingList') : t('discussions.addToReadingList') }}</span>
                <span class="sm:hidden">{{ isBookmarked ? t('discussions.inList') : t('discussions.addToList') }}</span>
              </button>
            </div>
          </div>

          <!-- Right side: Admin Panel -->
          <div v-if="authStore.isAdmin" class="w-72 flex-shrink-0 bg-gray-50 rounded-lg p-4 border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              {{ t('books.adminInfo') }}
            </h3>
            
            <div class="space-y-3 text-xs">
              <div>
                <span class="text-gray-500">{{ t('books.bookId') }}:</span>
                <span class="ml-1 font-mono text-gray-700">{{ book.book_id }}</span>
              </div>
              
              <div v-if="book.isbn10">
                <span class="text-gray-500">ISBN-10:</span>
                <span class="ml-1 font-mono text-gray-700">{{ book.isbn10 }}</span>
              </div>
              
              <div v-if="book.isbn13">
                <span class="text-gray-500">ISBN-13:</span>
                <span class="ml-1 font-mono text-gray-700">{{ book.isbn13 }}</span>
              </div>
              
              <div v-if="book.language">
                <span class="text-gray-500">{{ t('books.language') }}:</span>
                <span class="ml-1 text-gray-700">{{ book.language.toUpperCase() }}</span>
              </div>
              
              <div v-if="book.publisher">
                <span class="text-gray-500">{{ t('books.publisher') }}:</span>
                <span class="ml-1 text-gray-700">{{ book.publisher }}</span>
              </div>
              
              <div v-if="book.publish_date">
                <span class="text-gray-500">{{ t('books.publishDate') }}:</span>
                <span class="ml-1 text-gray-700">{{ formatDate(book.publish_date) }}</span>
              </div>
              
              <div v-if="book.external_ids?.google_books">
                <span class="text-gray-500">Google Books ID:</span>
                <span class="ml-1 font-mono text-gray-700 text-[10px]">{{ book.external_ids.google_books }}</span>
              </div>
              
              <div v-if="book.last_api_sync">
                <span class="text-gray-500">{{ t('books.lastSync') }}:</span>
                <span class="ml-1 text-gray-700">{{ formatDate(book.last_api_sync) }}</span>
              </div>
              
              <div class="pt-3 border-t border-gray-300 space-y-2">
                <button class="w-full px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                  {{ t('books.editBook') }}
                </button>
                <button class="w-full px-3 py-1.5 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors">
                  {{ t('books.syncWithApi') }}
                </button>
                <button class="w-full px-3 py-1.5 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                  {{ t('books.deleteBook') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Discussions Section -->
      <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-900">{{ t('discussions.discussions') }}</h2>
          <button 
            v-if="authStore.isAuthenticated"
            @click="showNewDiscussionModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
          >
            {{ t('discussions.newDiscussion') }}
          </button>
        </div>

        <!-- Discussions List -->
        <div v-if="discussions.length > 0" class="divide-y">
          <div
            v-for="discussion in discussions"
            :key="discussion.id"
            @click="goToDiscussion(discussion.id)"
            class="p-6 hover:bg-gray-50 cursor-pointer transition-colors"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ discussion.title }}</h3>
                <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ discussion.content }}</p>
                <div class="flex items-center gap-4 text-xs text-gray-500">
                  <span>{{ t('discussions.by') }} {{ discussion.author?.name || 'Unknown' }}</span>
                  <span>{{ formatDate(discussion.created_at) }}</span>
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    {{ discussion.comments_count || 0 }} {{ t('discussions.replies') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="p-6">
          <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('discussions.noDiscussions') }}</h3>
            <p class="mt-2 text-gray-500">{{ t('discussions.beFirstToDiscuss') }}</p>
            <div v-if="authStore.isAuthenticated" class="mt-6">
              <button 
                @click="showNewDiscussionModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
              >
                {{ t('discussions.startDiscussion') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- New Discussion Modal -->
    <div v-if="showNewDiscussionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">{{ t('discussions.newDiscussion') }}</h3>
          <button @click="closeDiscussionModal" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div v-if="discussionError" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
          {{ discussionError }}
        </div>
        
        <form @submit.prevent="handleCreateDiscussion" class="space-y-4">
          <div>
            <label for="discussion-title" class="block text-sm font-medium text-gray-700">{{ t('discussions.title') }}</label>
            <input 
              id="discussion-title"
              v-model="newDiscussion.title" 
              type="text" 
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label for="discussion-content" class="block text-sm font-medium text-gray-700">{{ t('discussions.content') }}</label>
            <textarea 
              id="discussion-content"
              v-model="newDiscussion.content" 
              rows="6"
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>

          <div>
            <label for="discussion-scope" class="block text-sm font-medium text-gray-700">{{ t('discussions.scope') }}</label>
            <select
              id="discussion-scope"
              v-model="newDiscussion.scope"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="general">{{ t('discussions.scopeGeneral') }}</option>
              <option value="page">{{ t('discussions.scopePage') }}</option>
            </select>
          </div>

          <div v-if="newDiscussion.scope === 'page'">
            <label for="page-number" class="block text-sm font-medium text-gray-700">{{ t('discussions.pageNumber') }}</label>
            <input 
              id="page-number"
              v-model.number="newDiscussion.page_number" 
              type="number" 
              min="1"
              :max="book?.page_count"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              :placeholder="t('discussions.pageNumberPlaceholder')"
            />
          </div>
          
          <div class="flex justify-end space-x-3">
            <button 
              type="button" 
              @click="closeDiscussionModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
            >
              {{ t('common.cancel') }}
            </button>
            <button 
              type="submit" 
              :disabled="discussionLoading"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
            >
              {{ discussionLoading ? t('discussions.creating') : t('discussions.create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../stores/auth.js';
import { useReadingProgressStore } from '../stores/readingProgress.js';
import { useDiscussionsStore } from '../stores/discussions.js';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const authStore = useAuthStore();
const progressStore = useReadingProgressStore();
const discussionsStore = useDiscussionsStore();

const book = ref(null);
const discussions = ref([]);
const loading = ref(true);
const error = ref(null);
const showNewDiscussionModal = ref(false);
const newDiscussion = ref({ 
  title: '', 
  content: '', 
  scope: 'general', 
  page_number: null
});
const discussionLoading = ref(false);
const discussionError = ref('');
const descriptionExpanded = ref(false);

const isBookmarked = computed(() => {
  return book.value && progressStore.isBookInReadingList(book.value.book_id);
});

const fetchBook = async () => {
  try {
    const response = await fetch(`/api/books?isbn=${route.params.isbn}`, {
      headers: {
        'Accept': 'application/json'
      }
    });

    if (response.ok) {
      const data = await response.json();
      const isbn = route.params.isbn;
      
      // Handle both single book and array response
      if (Array.isArray(data.data)) {
        // Find the book that matches the ISBN
        book.value = data.data.find(b => 
          b.isbn === isbn || 
          b.isbn10 === isbn || 
          b.isbn13 === isbn
        ) || null;
      } else {
        book.value = data.data || data;
      }
      
      if (!book.value) {
        error.value = t('books.bookNotFound');
      }
    } else {
      error.value = t('books.bookNotFound');
    }
  } catch (err) {
    error.value = err.message;
  }
};

const fetchDiscussions = async () => {
  if (!book.value) return;
  
  await discussionsStore.fetchDiscussionsForBook(book.value.book_id);
  discussions.value = discussionsStore.discussionsByBook[book.value.book_id] || [];
};

const toggleBookmark = async () => {
  if (!book.value) return;
  
  const bookProgress = progressStore.getBookProgress(book.value.book_id);
  
  if (bookProgress) {
    await progressStore.removeFromReadingList(bookProgress.id);
  } else {
    await progressStore.addToReadingList(book.value.book_id, 'want_to_read');
  }
};

const goToDiscussion = (discussionId) => {
  router.push(`/books/${route.params.isbn}/${discussionId}`);
};

const handleCreateDiscussion = async () => {
  if (!book.value) return;
  
  discussionLoading.value = true;
  discussionError.value = '';
  
  const discussionData = {
    book_id: book.value.book_id,
    title: newDiscussion.value.title,
    content: newDiscussion.value.content,
    scope: newDiscussion.value.scope,
  };

  // Add page_number if scope is 'page'
  if (newDiscussion.value.scope === 'page' && newDiscussion.value.page_number) {
    discussionData.page_number = newDiscussion.value.page_number;
  }
  
  const result = await discussionsStore.createDiscussion(discussionData);
  
  if (result.success) {
    await fetchDiscussions();
    closeDiscussionModal();
  } else {
    discussionError.value = result.message;
  }
  
  discussionLoading.value = false;
};

const closeDiscussionModal = () => {
  showNewDiscussionModal.value = false;
  newDiscussion.value = { 
    title: '', 
    content: '', 
    scope: 'general', 
    page_number: null
  };
  discussionError.value = '';
};

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  return d.toLocaleDateString('lv-LV', { year: 'numeric', month: 'long', day: 'numeric' });
};

onMounted(async () => {
  loading.value = true;
  await fetchBook();
  if (book.value) {
    await fetchDiscussions();
    if (authStore.isAuthenticated) {
      await progressStore.fetchProgress();
    }
  }
  loading.value = false;
});
</script>
