import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { getLocalizedMessage } from '../utils/errorHandler.js';
import i18n from '../i18n.js';

export const useBooksStore = defineStore('books', () => {
  const books = ref([]);
  const loading = ref(false);
  const error = ref(null);

  // Helper to get auth headers
  const getAuthHeaders = () => {
    const token = localStorage.getItem('auth_token');
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': token ? `Bearer ${token}` : ''
    };
  };

  // Computed properties
  const booksCount = computed(() => ({
    total: books.value.length
  }));

  // Actions
  const fetchBooks = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await fetch('/api/books?per_page=999', {
        headers: getAuthHeaders()
      });

      if (response.ok) {
        const data = await response.json();
        books.value = data.data || data;
      } else {
        const errorData = await response.json();
        error.value = getLocalizedMessage(errorData) || i18n.global.t('errors.fetch_books_failed');
        books.value = [];
      }
    } catch (err) {
      error.value = err.message;
      books.value = [];
    } finally {
      loading.value = false;
    }
  };

  const addBook = async (bookData) => {
    try {
      const response = await fetch('/api/books', {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify(bookData)
      });

      if (response.ok) {
        const data = await response.json();
        const newBook = data.data || data;
        books.value.push(newBook);
        return { success: true, book: newBook };
      } else {
        const errorData = await response.json();
        return { success: false, message: getLocalizedMessage(errorData) || i18n.global.t('errors.add_book_failed') };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const updateBook = async (bookId, bookData) => {
    try {
      const response = await fetch(`/api/books/${bookId}`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(bookData)
      });

      if (response.ok) {
        const data = await response.json();
        const updatedBook = data.data || data;
        const bookIndex = books.value.findIndex(book => book.id === bookId);
        if (bookIndex !== -1) {
          books.value[bookIndex] = updatedBook;
        }
        return { success: true, book: updatedBook };
      } else {
        const errorData = await response.json();
        return { success: false, message: getLocalizedMessage(errorData) || i18n.global.t('errors.update_book_failed') };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const deleteBook = async (bookId) => {
    try {
      const response = await fetch(`/api/books/${bookId}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });

      if (response.ok) {
        books.value = books.value.filter(book => book.id !== bookId);
        return { success: true };
      } else {
        const errorData = await response.json();
        return { success: false, message: getLocalizedMessage(errorData) || i18n.global.t('errors.delete_book_failed') };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const importByISBN = async (isbn) => {
    loading.value = true;
    try {
      const response = await fetch(`/api/books/import-isbn`, {
        method: 'POST',
        headers: {
          ...getAuthHeaders(),
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ isbn })
      });

      if (response.ok) {
        const data = await response.json();
        const newBook = data.book || data.data || data;
        books.value.push(newBook);
        return { success: true, book: newBook };
      } else {
        const errorData = await response.json();
        return { success: false, message: getLocalizedMessage(errorData) || i18n.global.t('errors.import_book_failed') };
      }
    } catch (err) {
      return { success: false, message: err.message };
    } finally {
      loading.value = false;
    }
  };

  const batchImportByGenre = async (genre, limit) => {
    loading.value = true;
    try {
      const response = await fetch(`/api/books/import-by-genre`, {
        method: 'POST',
        headers: {
          ...getAuthHeaders(),
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ genre, limit })
      });

      if (response.ok) {
        const data = await response.json();
        // Add newly imported books to the store
        if (data.imported && Array.isArray(data.imported)) {
          // Refresh books list to get all new books with full data
          await fetchBooks();
        }
        return { 
          success: true, 
          summary: data.summary,
          imported: data.imported,
          skipped: data.skipped,
          failed: data.failed
        };
      } else {
        const errorData = await response.json();
        return { success: false, message: getLocalizedMessage(errorData) || i18n.global.t('errors.import_books_by_genre_failed') };
      }
    } catch (err) {
      return { success: false, message: err.message };
    } finally {
      loading.value = false;
    }
  };

  const getBookByISBN = async (isbn) => {
    try {
      const response = await fetch(`/api/books?isbn=${isbn}`, {
        headers: getAuthHeaders()
      });

      if (response.ok) {
        const data = await response.json();
        return { success: true, book: data.data || data };
      } else {
        return { success: false, message: i18n.global.t('errors.book_not_found') };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  return {
    books,
    loading,
    error,
    booksCount,
    fetchBooks,
    addBook,
    updateBook,
    deleteBook,
    importByISBN,
    batchImportByGenre,
    getBookByISBN
  };
});