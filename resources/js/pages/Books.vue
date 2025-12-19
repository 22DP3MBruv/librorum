<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ t('books.title') }}</h1>
        <p class="text-gray-600">{{ t('books.subtitle') }}</p>
      </div>
      <button 
        v-if="authStore.isAdmin"
        @click="showImportModal = true" 
        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center"
      >
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        {{ t('books.addBook') }}
      </button>
    </div>

    <!-- Search and Filter Bar -->
    <div class="mb-6 flex gap-4">
      <div class="relative flex-1">
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="t('books.searchPlaceholder')"
          @input="handleSearch"
          class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
        <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
      </div>
      <div class="relative">
        <input
          v-model="genreFilter"
          type="text"
          :placeholder="t('books.filterByGenre')"
          @input="handleGenreInput"
          @focus="showGenreSuggestions = true"
          @blur="hideGenreSuggestions"
          class="px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-48"
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
    <div v-else-if="filteredBooks.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div 
        v-for="book in filteredBooks" 
        :key="book.id"
        class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow relative group"
      >
        <!-- Bookmark Button -->
        <button
          v-if="authStore.isAuthenticated"
          @click.stop="toggleBookmark(book)"
          class="absolute top-2 right-2 z-10 bg-white rounded-full p-2 shadow-md hover:shadow-lg transition-all"
          :class="isBookmarked(book.id) ? 'text-blue-600' : 'text-gray-400 hover:text-blue-600'"
        >
          <svg class="w-6 h-6" :fill="isBookmarked(book.id) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
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
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-1 line-clamp-2">{{ book.title }}</h3>
            <p class="text-sm text-gray-600 mb-2">{{ book.author }}</p>
            <div class="flex justify-between items-center text-xs text-gray-500 gap-2">
              <span v-if="book.genre" class="bg-blue-100 text-blue-800 px-2 py-1 rounded whitespace-nowrap truncate max-w-[120px]" :title="book.genre">{{ book.genre }}</span>
              <span v-if="book.page_count" class="whitespace-nowrap">{{ book.page_count }} {{ t('books.pages') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-24 w-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
      </svg>
      <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('books.noBooksFound') }}</h3>
      <p class="mt-2 text-gray-500">{{ t('books.startSearching') }}</p>
      <button 
        v-if="authStore.isAdmin"
        @click="showImportModal = true"
        class="mt-6 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors"
      >
        {{ t('books.addBook') }}
      </button>
    </div>

    <!-- Import Book Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">{{ t('books.importByISBN') }}</h3>
          <button @click="closeImportModal" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
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
const isbnInput = ref('');
const importLoading = ref(false);
const importError = ref('');
const importSuccess = ref(false);

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
      book.author?.toLowerCase().includes(query) ||
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

// Methods
const handleSearch = () => {
  // Real-time filtering handled by computed property
};

const handleGenreInput = () => {
  showGenreSuggestions.value = true;
};

const selectGenre = (genre) => {
  genreFilter.value = genre;
  showGenreSuggestions.value = false;
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
