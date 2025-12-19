import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { getLocalizedErrorMessage } from '../utils/errorHandler.js';

export const useBooksStore = defineStore('books', () => {
  const books = ref([]);
  const loading = ref(false);
  const error = ref(null);
  const searchResults = ref([]);
  const searchLoading = ref(false);

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
  const booksByTag = computed(() => {
    const grouped = {};
    books.value.forEach(book => {
      const tag = book.tag || 'Uncategorized';
      if (!grouped[tag]) {
        grouped[tag] = [];
      }
      grouped[tag].push(book);
    });
    return grouped;
  });

  const booksCount = computed(() => ({
    total: books.value.length
  }));

  // Actions
  const fetchBooks = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await fetch('/api/books', {
        headers: getAuthHeaders()
      });

      if (response.ok) {
        const data = await response.json();
        books.value = data.data || data;
      } else {
        const errorData = await response.json();
        error.value = getLocalizedErrorMessage(errorData) || 'Failed to fetch books';
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
        return { success: false, message: getLocalizedErrorMessage(errorData) || 'Failed to add book' };
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
        return { success: false, message: getLocalizedErrorMessage(errorData) || 'Failed to update book' };
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
        return { success: false, message: getLocalizedErrorMessage(errorData) || 'Failed to delete book' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const searchBooks = async (query) => {
    searchLoading.value = true;
    try {
      const response = await fetch(`/api/books/external-search?query=${encodeURIComponent(query)}`, {
        headers: getAuthHeaders()
      });

      if (response.ok) {
        const data = await response.json();
        searchResults.value = data.data || data;
        return { success: true, results: searchResults.value };
      } else {
        const errorData = await response.json();
        return { success: false, message: getLocalizedErrorMessage(errorData) || 'Search failed' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    } finally {
      searchLoading.value = false;
    }
  };

  const importByISBN = async (isbn) => {
    loading.value = true;
    try {
      const response = await fetch(`/api/books/import-isbn`, {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({ isbn })
      });

      if (response.ok) {
        const data = await response.json();
        const newBook = data.data || data;
        books.value.push(newBook);
        return { success: true, book: newBook };
      } else {
        const errorData = await response.json();
        return { success: false, message: getLocalizedErrorMessage(errorData) || 'Failed to import book' };
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
        return { success: false, message: 'Book not found' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  return {
    books,
    loading,
    error,
    searchResults,
    searchLoading,
    booksByTag,
    booksCount,
    fetchBooks,
    addBook,
    updateBook,
    deleteBook,
    searchBooks,
    importByISBN,
    getBookByISBN
  };
});