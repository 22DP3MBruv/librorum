<template>
  <div class="w-full min-w-0 overflow-x-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-6">
    <!-- Page Header -->
    <div class="mb-6 sm:mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 min-w-0">
      <div class="flex-1 min-w-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ t('books.title') }}</h1>
        <p class="text-sm sm:text-base text-gray-600">{{ t('books.subtitle') }}</p>
      </div>
      <button 
        v-if="authStore.isAdmin"
        @click="showImportModal = true" 
        class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center justify-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <span class="whitespace-nowrap">{{ t('books.addBook') }}</span>
      </button>
    </div>

    <!-- Search and Filter Bar -->
    <div class="mb-6 flex flex-col sm:flex-row gap-3 sm:gap-4 min-w-0">
      <div class="relative flex-1 min-w-0">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('books.searchPlaceholder')"
          @input="handleSearch"
          class="w-full px-4 py-2.5 sm:py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base"
        >
        <svg class="absolute left-3 top-2.5 sm:top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
      <div class="relative w-full sm:w-48">
        <input
          v-model="genreFilter"
          type="text"
          :placeholder="t('books.filterByGenre')"
          @input="handleGenreInput"
          @focus="showGenreSuggestions = true"
          @blur="hideGenreSuggestions"
          class="w-full px-4 py-2.5 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base"
        >
        <!-- Genre Suggestions Dropdown -->
        <div 
          v-if="showGenreSuggestions && filteredGenres.length > 0"
          class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
        >
          <button
            v-for="genre in filteredGenres"
            :key="genre"
            @mousedown="selectGenre(genre)"
            class="w-full px-4 py-2 text-left hover:bg-blue-50 transition-colors"
          >
            {{ genre }}
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="booksStore.loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">{{ t('common.loading') }}</p>
    </div>

    <!-- Error State -->
    <div v-else-if="booksStore.error" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-6">
      {{ booksStore.error }}
    </div>

    <!-- Books Grid -->
    <div v-else-if="filteredBooks.length > 0" class="min-w-0 w-full">
      <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 md:gap-6">
        <div 
          v-for="book in paginatedBooks" 
          :key="book.id"
          class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow relative group min-w-0 w-full max-w-full"
        >
        <!-- Bookmark Button -->
        <button
          v-if="authStore.isAuthenticated"
          @click.stop="toggleBookmark(book)"
          class="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 z-0 bg-white rounded-full p-1.5 sm:p-2 shadow-md hover:shadow-lg transition-all pointer-events-auto"
          :class="isBookmarked(book.id) ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600'"
        >
          <svg class="w-5 h-5 sm:w-6 sm:h-6" :fill="isBookmarked(book.id) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
          </svg>
        </button>

        <div @click="goToBookDiscussion(book)" class="cursor-pointer">
          <div class="aspect-[2/3] bg-gray-200 rounded-t-lg overflow-hidden">
            <img 
              v-if="book.cover_image_url" 
              :src="book.cover_image_url" 
              :alt="book.title"
              class="w-full h-full object-cover"
            >
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
          </div>
          <div class="p-2 sm:p-4">
            <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2 text-xs sm:text-base break-words overflow-hidden">{{ book.title }}</h3>
            <p class="text-[10px] sm:text-sm text-gray-600 mb-2 line-clamp-1 break-words overflow-hidden">{{ Array.isArray(book.authors) ? book.authors.join(', ') : book.authors }}</p>
            <div class="flex justify-between items-center text-xs text-gray-500 gap-1 sm:gap-2 min-w-0">
              <span v-if="book.genre" class="bg-blue-100 text-blue-800 px-1 sm:px-2 py-0.5 sm:py-1 rounded truncate max-w-[60px] sm:max-w-[120px] text-[9px] sm:text-xs" :title="book.genre">{{ book.genre }}</span>
              <span v-if="book.page_count" class="whitespace-nowrap text-[9px] sm:text-xs">{{ book.page_count }} {{ t('books.pages') }}</span>
            </div>
          </div>
        </div>
      </div>
      </div>

      <!-- Pagination Controls -->
      <div v-if="totalPages > 1" class="mt-6 sm:mt-8 flex flex-col sm:flex-row items-center justify-between gap-4 bg-white rounded-lg shadow-sm border p-4">
        <div class="text-sm text-gray-600 text-center sm:text-left w-full sm:w-auto">
          {{ t('pagination.showing') }} {{ ((currentPage - 1) * itemsPerPage) + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredBooks.length) }} {{ t('pagination.of') }} {{ filteredBooks.length }}
        </div>
        <div class="flex items-center gap-2 overflow-x-auto w-full sm:w-auto pb-2 sm:pb-0">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="flex-shrink-0 px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span class="hidden sm:inline">{{ t('pagination.previous') }}</span>
            <span class="sm:hidden">‹</span>
          </button>
          <button
            v-for="page in paginationRange"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'flex-shrink-0 px-3 py-2 text-sm rounded-lg transition-colors',
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
            class="flex-shrink-0 px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span class="hidden sm:inline">{{ t('pagination.next') }}</span>
            <span class="sm:hidden">›</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
      </svg>
      <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('books.noBooksFound') }}</h3>
      <button 
        v-if="authStore.isAdmin"
        @click="showImportModal = true"
        class="mt-6 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
      >
        {{ t('books.addBook') }}
      </button>
    </div>

    <!-- Import Book Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-lg font-medium text-gray-900">{{ t('books.importBooks') }}</h3>
          <button @click="closeImportModal" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <!-- Import Mode Tabs -->
        <div class="flex gap-0 border-b mb-6">
          <button 
            @click="importMode = 'isbn'"
            :class="[
              'px-4 py-2 font-medium border-b-2 transition-colors',
              importMode === 'isbn' 
                ? 'text-blue-600 border-blue-600' 
                : 'text-gray-600 border-transparent hover:text-gray-900'
            ]"
          >
            {{ t('books.importByISBN') }}
          </button>
          <button 
            @click="importMode = 'genre'"
            :class="[
              'px-4 py-2 font-medium border-b-2 transition-colors',
              importMode === 'genre' 
                ? 'text-blue-600 border-blue-600' 
                : 'text-gray-600 border-transparent hover:text-gray-900'
            ]"
          >
            {{ t('books.importByGenre') }}
          </button>
        </div>

        <!-- ISBN Import Tab -->
        <div v-if="importMode === 'isbn'">
          <div v-if="importError" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
            {{ importError }}
          </div>

          <div v-if="importSuccess" class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded mb-4">
            {{ t('books.bookImported') }}
          </div>
          
          <form @submit.prevent="handleImport" class="space-y-4">
            <div>
              <label for="isbn" class="block text-sm font-medium text-gray-700">{{ t('books.isbn') }}</label>
              <input 
                id="isbn"
                v-model="isbnInput" 
                type="text" 
                required
                placeholder="9780747532699"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="closeImportModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
              >
                {{ t('common.cancel') }}
              </button>
              <button 
                type="submit" 
                :disabled="importLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
              >
                {{ importLoading ? t('books.importing') : t('books.importBook') }}
              </button>
            </div>
          </form>
        </div>

        <!-- Genre Batch Import Tab -->
        <div v-else-if="importMode === 'genre'">
          <div v-if="batchImportError" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
            {{ batchImportError }}
          </div>

          <div v-if="showBatchResults">
            <div class="bg-blue-50 border border-blue-200 text-blue-600 px-4 py-3 rounded mb-4">
              <p class="font-semibold mb-2">{{ t('books.importResults') }}</p>
              <div class="text-sm space-y-1">
                <p>{{ t('books.imported') }}: <span class="font-semibold text-green-600">{{ batchResults.summary?.imported || 0 }}</span></p>
                <p>{{ t('books.skipped') }}: <span class="font-semibold text-yellow-600">{{ batchResults.summary?.skipped || 0 }}</span></p>
                <p>{{ t('books.failed') }}: <span class="font-semibold text-red-600">{{ batchResults.summary?.failed || 0 }}</span></p>
              </div>
            </div>

            <!-- Imported Books -->
            <div v-if="batchResults.imported?.length > 0" class="mb-4">
              <h4 class="font-semibold text-green-700 mb-2">{{ t('books.successfullyImported') }}</h4>
              <div class="space-y-2 max-h-40 overflow-y-auto">
                <div v-for="book in batchResults.imported" :key="book.book_id" class="text-sm p-2 bg-green-50 rounded border border-green-200">
                  <p class="font-medium">{{ book.title }}</p>
                  <p class="text-xs text-gray-600">ISBN: {{ book.isbn }}</p>
                </div>
              </div>
            </div>

            <!-- Skipped Books -->
            <div v-if="batchResults.skipped?.length > 0" class="mb-4">
              <h4 class="font-semibold text-yellow-700 mb-2">{{ t('books.skippedBooks') }}</h4>
              <div class="space-y-2 max-h-40 overflow-y-auto">
                <div v-for="(book, idx) in batchResults.skipped" :key="`skipped-${idx}`" class="text-sm p-2 bg-yellow-50 rounded border border-yellow-200">
                  <p class="font-medium">{{ book.title }}</p>
                  <p class="text-xs text-gray-600">{{ book.reason }}</p>
                </div>
              </div>
            </div>

            <!-- Failed Books -->
            <div v-if="batchResults.failed?.length > 0" class="mb-4">
              <h4 class="font-semibold text-red-700 mb-2">{{ t('books.failedBooks') }}</h4>
              <div class="space-y-2 max-h-40 overflow-y-auto">
                <div v-for="(book, idx) in batchResults.failed" :key="`failed-${idx}`" class="text-sm p-2 bg-red-50 rounded border border-red-200">
                  <p class="font-medium">{{ book.title }}</p>
                  <p class="text-xs text-gray-600">{{ book.reason }}</p>
                </div>
              </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
              <button 
                @click="resetBatchImport"
                class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md border border-blue-200"
              >
                {{ t('books.importMore') }}
              </button>
              <button 
                @click="closeImportModal"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md"
              >
                {{ t('common.done') }}
              </button>
            </div>
          </div>

          <form v-else @submit.prevent="handleBatchImport" class="space-y-4">
            <div>
              <label for="genre" class="block text-sm font-medium text-gray-700">
                {{ t('books.genre') }}
                <span class="text-red-500">*</span>
              </label>
              <input 
                id="genre"
                v-model="batchGenreInput" 
                type="text" 
                required
                placeholder="Science Fiction, Mystery, Fantasy, etc."
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
              <p class="mt-1 text-xs text-gray-500">{{ t('books.genreHint') }}</p>
            </div>

            <div>
              <label for="limit" class="block text-sm font-medium text-gray-700">
                {{ t('books.numberOfBooks') }}
                <span class="text-red-500">*</span>
              </label>
              <div class="mt-1 flex items-center gap-2">
                <input 
                  id="limit"
                  v-model.number="batchLimitInput" 
                  type="number" 
                  min="1" 
                  max="40"
                  required
                  placeholder="10"
                  class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                />
                <span class="text-sm text-gray-600">(max: 40)</span>
              </div>
              <p class="mt-1 text-xs text-gray-500">{{ t('books.limitHint') }}</p>
            </div>

            <div class="bg-blue-50 p-3 rounded-md text-sm text-blue-800">
              <p>{{ t('books.batchImportInfo') }}</p>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button 
                type="button" 
                @click="closeImportModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
              >
                {{ t('common.cancel') }}
              </button>
              <button 
                type="submit" 
                :disabled="importLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50 flex items-center gap-2"
              >
                <svg v-if="importLoading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ importLoading ? t('books.importing') : t('books.startImport') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useBooksStore } from '../stores/books.js';
import { useReadingProgressStore } from '../stores/readingProgress.js';
import { useAuthStore } from '../stores/auth.js';

const { t } = useI18n();
const router = useRouter();
const booksStore = useBooksStore();
const progressStore = useReadingProgressStore();
const authStore = useAuthStore();

const searchQuery = ref('');
const genreFilter = ref('');
const showGenreSuggestions = ref(false);
const showImportModal = ref(false);
const importMode = ref('isbn'); // 'isbn' or 'genre'
const isbnInput = ref('');
const importLoading = ref(false);
const importError = ref('');
const importSuccess = ref(false);

// Batch import by genre state
const batchGenreInput = ref('');
const batchLimitInput = ref(10);
const batchImportError = ref('');
const showBatchResults = ref(false);
const batchResults = ref({
  summary: {},
  imported: [],
  skipped: [],
  failed: []
});

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(20);

// Computed
const availableGenres = computed(() => {
  const genres = new Set();
  booksStore.books.forEach(book => {
    if (book.genre) genres.add(book.genre);
  });
  return Array.from(genres).sort();
});

const filteredGenres = computed(() => {
  if (!genreFilter.value) {
    return availableGenres.value;
  }
  const query = genreFilter.value.toLowerCase();
  return availableGenres.value.filter(genre => 
    genre.toLowerCase().includes(query)
  );
});

const filteredBooks = computed(() => {
  let filtered = booksStore.books;
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(book => 
      book.title?.toLowerCase().includes(query) ||
      (Array.isArray(book.authors) ? book.authors.some(a => a.toLowerCase().includes(query)) : book.authors?.toLowerCase().includes(query)) ||
      book.isbn?.includes(query) ||
      book.isbn10?.includes(query) ||
      book.isbn13?.includes(query)
    );
  }
  
  // Filter by genre
  if (genreFilter.value) {
    const query = genreFilter.value.toLowerCase();
    filtered = filtered.filter(book => 
      book.genre?.toLowerCase().includes(query)
    );
  }
  
  return filtered;
});

const totalPages = computed(() => {
  return Math.ceil(filteredBooks.value.length / itemsPerPage.value);
});

const paginatedBooks = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredBooks.value.slice(start, end);
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

// Methods
const handleSearch = () => {
  // Real-time filtering handled by computed property
  currentPage.value = 1; // Reset to first page on search
};

const handleGenreInput = () => {
  showGenreSuggestions.value = true;
};

const selectGenre = (genre) => {
  genreFilter.value = genre;
  showGenreSuggestions.value = false;
  currentPage.value = 1; // Reset to first page on filter change
};

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

const hideGenreSuggestions = () => {
  setTimeout(() => {
    showGenreSuggestions.value = false;
  }, 200);
};

const goToBookDiscussion = (book) => {
  const isbn = book.isbn || book.isbn13 || book.isbn10;
  if (isbn) {
    router.push(`/books/${isbn}`);
  }
};

const handleImport = async () => {
  importLoading.value = true;
  importError.value = '';
  importSuccess.value = false;
  
  const result = await booksStore.importByISBN(isbnInput.value);
  
  if (result.success) {
    importSuccess.value = true;
    isbnInput.value = '';
    setTimeout(() => {
      closeImportModal();
    }, 2000);
  } else {
    importError.value = result.message;
  }
  
  importLoading.value = false;
};

const closeImportModal = () => {
  showImportModal.value = false;
  isbnInput.value = '';
  importError.value = '';
  importSuccess.value = false;
  importMode.value = 'isbn';
  resetBatchImport();
};

const resetBatchImport = () => {
  batchGenreInput.value = '';
  batchLimitInput.value = 10;
  batchImportError.value = '';
  showBatchResults.value = false;
  batchResults.value = {
    summary: {},
    imported: [],
    skipped: [],
    failed: []
  };
};

const handleBatchImport = async () => {
  if (!batchGenreInput.value.trim()) {
    batchImportError.value = t('books.genreRequired');
    return;
  }

  if (!batchLimitInput.value || batchLimitInput.value < 1 || batchLimitInput.value > 40) {
    batchImportError.value = t('books.limitInvalid');
    return;
  }

  importLoading.value = true;
  batchImportError.value = '';
  
  const result = await booksStore.batchImportByGenre(
    batchGenreInput.value.trim(),
    batchLimitInput.value
  );
  
  if (result.success) {
    batchResults.value = {
      summary: result.summary,
      imported: result.imported || [],
      skipped: result.skipped || [],
      failed: result.failed || []
    };
    showBatchResults.value = true;
  } else {
    batchImportError.value = result.message || t('books.batchImportFailed');
  }
  
  importLoading.value = false;
};

const isBookmarked = (bookId) => {
  return progressStore.isBookInReadingList(bookId);
};

const toggleBookmark = async (book) => {
  if (!book.id) {
    console.error('Book ID is missing:', book);
    alert('Error: Book ID is missing');
    return;
  }
  
  const bookProgress = progressStore.getBookProgress(book.id);
  
  if (bookProgress) {
    // Remove from reading list
    const result = await progressStore.removeFromReadingList(bookProgress.id);
    if (!result.success) {
      alert(result.message || t('books.removeBookmarkFailed'));
    }
  } else {
    // Add to reading list
    const result = await progressStore.addToReadingList(book.id, 'want_to_read');
    if (!result.success) {
      alert(result.message || t('books.addBookmarkFailed'));
    }
  }
};

// Lifecycle
onMounted(() => {
  booksStore.fetchBooks();
  if (authStore.isAuthenticated) {
    progressStore.fetchProgress();
  }
});
</script>
