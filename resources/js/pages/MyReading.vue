<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ t('reading.title') }}</h1>
      <p class="text-gray-600">{{ t('reading.subtitle') }}</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">{{ t('reading.wantToRead') }}</p>
            <p class="text-3xl font-bold text-blue-600">{{ progressStore.progressCount.wantToRead }}</p>
          </div>
          <div class="bg-blue-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">{{ t('reading.currentlyReading') }}</p>
            <p class="text-3xl font-bold text-green-600">{{ progressStore.progressCount.reading }}</p>
          </div>
          <div class="bg-green-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">{{ t('reading.completed') }}</p>
            <p class="text-3xl font-bold text-purple-600">{{ progressStore.progressCount.read }}</p>
          </div>
          <div class="bg-purple-100 p-3 rounded-lg">
            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-gray-200">
      <nav class="-mb-px flex space-x-8">
        <button
          @click="selectedStatus = 'all'"
          :class="[
            selectedStatus === 'all'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ t('reading.all') }} ({{ progressStore.progressCount.total }})
        </button>
        <button
          @click="selectedStatus = 'want_to_read'"
          :class="[
            selectedStatus === 'want_to_read'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ t('reading.wantToRead') }} ({{ progressStore.progressCount.wantToRead }})
        </button>
        <button
          @click="selectedStatus = 'reading'"
          :class="[
            selectedStatus === 'reading'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ t('reading.currentlyReading') }} ({{ progressStore.progressCount.reading }})
        </button>
        <button
          @click="selectedStatus = 'completed'"
          :class="[
            selectedStatus === 'completed'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ t('reading.completed') }} ({{ progressStore.progressCount.completed }})
        </button>
        <button
          @click="selectedStatus = 'dropped'"
          :class="[
            selectedStatus === 'dropped'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ t('reading.dropped') }} ({{ progressStore.progressCount.dropped }})
        </button>
      </nav>
    </div>

    <!-- Loading State -->
    <div v-if="progressStore.loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">{{ t('common.loading') }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="progressStore.error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6">
      {{ progressStore.error }}
    </div>

    <!-- Books List -->
    <div v-else-if="filteredProgress.length > 0" class="space-y-4">
      <div
        v-for="progress in filteredProgress"
        :key="progress.id"
        class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow"
      >
        <div class="flex gap-4">
          <!-- Book Cover -->
          <div class="flex-shrink-0 w-24 h-36 bg-gray-200 rounded overflow-hidden cursor-pointer" @click="goToBook(progress.book)">
            <img
              v-if="progress.book?.cover_image_url"
              :src="progress.book.cover_image_url"
              :alt="progress.book.title"
              class="w-full h-full object-cover"
            >
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
          </div>

          <!-- Book Details -->
          <div class="flex-1">
            <h3 class="font-semibold text-lg text-gray-900 mb-1 cursor-pointer hover:text-blue-600" @click="goToBook(progress.book)">
              {{ progress.book?.title }}
            </h3>
            <p class="text-sm text-gray-600 mb-2">{{ progress.book?.author }}</p>
            
            <!-- Progress Info -->
            <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
              <span v-if="progress.book?.page_count">{{ progress.book.page_count }} {{ t('books.pages') }}</span>
              <span v-if="progress.book?.genre" class="bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ progress.book.genre }}</span>
            </div>

            <!-- Status Selector and Page Progress -->
            <div class="flex items-center gap-4 flex-wrap">
              <select
                v-model="progress.status"
                @change="updateStatus(progress)"
                class="px-3 py-1 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="want_to_read">{{ t('reading.wantToRead') }}</option>
                <option value="reading">{{ t('reading.currentlyReading') }}</option>
                <option value="completed">{{ t('reading.completed') }}</option>
                <option value="dropped">{{ t('reading.dropped') }}</option>
              </select>

              <div v-if="progress.status === 'reading'" class="flex items-center gap-2">
                <label class="text-sm text-gray-600">{{ t('reading.page') }}:</label>
                <input
                  v-model.number="progress.current_page"
                  @blur="updatePage(progress)"
                  type="number"
                  min="0"
                  :max="progress.book?.page_count"
                  class="w-20 px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                <span class="text-sm text-gray-500">/ {{ progress.book?.page_count || '?' }}</span>
              </div>

              <button
                @click="removeBook(progress)"
                class="ml-auto text-red-600 hover:text-red-700 text-sm"
              >
                {{ t('common.remove') }}
              </button>
            </div>

            <!-- Progress Bar -->
            <div v-if="progress.status === 'reading' && progress.book?.page_count" class="mt-3">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-green-600 h-2 rounded-full transition-all"
                  :style="{ width: `${(progress.current_page / progress.book.page_count) * 100}%` }"
                ></div>
              </div>
              <p class="text-xs text-gray-500 mt-1">
                {{ Math.round((progress.current_page / progress.book.page_count) * 100) }}% {{ t('reading.complete') }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
      </svg>
      <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('reading.noBooksYet') }}</h3>
      <p class="mt-2 text-gray-500">{{ t('reading.startAdding') }}</p>
      <router-link
        to="/books"
        class="mt-6 inline-block bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
      >
        {{ t('reading.browseBooks') }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useReadingProgressStore } from '../stores/readingProgress.js';

const { t } = useI18n();
const router = useRouter();
const progressStore = useReadingProgressStore();

const selectedStatus = ref('all');

// Computed
const filteredProgress = computed(() => {
  if (selectedStatus.value === 'all') {
    return progressStore.progressList;
  }
  return progressStore.progressByStatus[selectedStatus.value] || [];
});

// Methods
const goToBook = (book) => {
  if (book) {
    const isbn = book.isbn || book.isbn13 || book.isbn10;
    if (isbn) {
      router.push(`/books/${isbn}`);
    }
  }
};

const updateStatus = async (progress) => {
  const result = await progressStore.updateProgress(progress.id, {
    status: progress.status
  });
  
  if (!result.success) {
    alert(result.message || t('reading.updateFailed'));
    // Revert on failure
    await progressStore.fetchProgress();
  }
};

const updatePage = async (progress) => {
  const result = await progressStore.updateProgress(progress.id, {
    current_page: progress.current_page
  });
  
  if (!result.success) {
    alert(result.message || t('reading.updateFailed'));
    // Revert on failure
    await progressStore.fetchProgress();
  }
};

const removeBook = async (progress) => {
  if (confirm(t('reading.confirmRemove'))) {
    const result = await progressStore.removeFromReadingList(progress.id);
    
    if (!result.success) {
      alert(result.message || t('reading.removeFailed'));
    }
  }
};

// Lifecycle
onMounted(() => {
  progressStore.fetchProgress();
});
</script>
