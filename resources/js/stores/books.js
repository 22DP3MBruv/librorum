import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useBooksStore = defineStore('books', () => {
  const books = ref([]);
  const loading = ref(false);
  const error = ref(null);

  // Mock initial data
  const initializeBooks = () => {
    books.value = [
      {
        id: 1,
        title: "Harijs Poters un Filozofa akmens",
        author: "J.K. Rowling",
        status: "read",
        isbn: "9780747532699",
        cover: null,
        rating: 5,
        pages: 309,
        tag: "fantastika",
        dateAdded: "2025-01-15",
        dateStarted: "2025-02-01",
        dateFinished: "2025-02-15"
      },
      {
        id: 2,
        title: "1984",
        author: "George Orwell",
        status: "reading",
        isbn: "9780451524935",
        cover: null,
        rating: null,
        pages: 328,
        tag: "dystopija",
        dateAdded: "2025-02-10",
        dateStarted: "2025-02-16",
        dateFinished: null
      },
      {
        id: 3,
        title: "Dievkods",
        author: "Dan Brown",
        status: "want-to-read",
        isbn: "9780385504201",
        cover: null,
        rating: null,
        pages: 689,
        tag: "mistērija",
        dateAdded: "2025-02-20",
        dateStarted: null,
        dateFinished: null
      }
    ];
  };

  // Computed properties
  const booksByStatus = computed(() => {
    return {
      'want-to-read': books.value.filter(book => book.status === 'want-to-read'),
      'reading': books.value.filter(book => book.status === 'reading'),
      'read': books.value.filter(book => book.status === 'read')
    };
  });

  const booksCount = computed(() => ({
    total: books.value.length,
    booksWantToRead: booksByStatus.value['want-to-read'].length,
    booksReading: booksByStatus.value.reading.length,
    booksRead: booksByStatus.value.read.length
  }));

  // Actions
  const fetchBooks = async () => {
    loading.value = true;
    try {
      // Mock API call - TODO: Replace with real Laravel API endpoint
      const response = await fetch('/api/books', {
        headers: {
          'Accept': 'application/json',
        }
      });

      if (response.ok) {
        const data = await response.json();
        books.value = data;
      } else {
        // Fallback to mock data
        initializeBooks();
      }
    } catch (err) {
      error.value = err.message;
      // Fallback to mock data
      initializeBooks();
    } finally {
      loading.value = false;
    }
  };

  const addBook = async (bookData) => {
    try {
      // Mock API call
      const response = await fetch('/api/books', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(bookData)
      });

      if (response.ok) {
        const newBook = await response.json();
        books.value.push(newBook);
        return { success: true, book: newBook };
      } else {
        return { success: false, message: 'Failed to add book' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const updateBookStatus = async (bookId, newStatus) => {
    try {
      const response = await fetch(`/api/books/${bookId}/status`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ status: newStatus })
      });

      if (response.ok) {
        const bookIndex = books.value.findIndex(book => book.id === bookId);
        if (bookIndex !== -1) {
          books.value[bookIndex].status = newStatus;
          
          // Update dates based on status
          const now = new Date().toISOString().split('T')[0];
          if (newStatus === 'reading' && !books.value[bookIndex].dateStarted) {
            books.value[bookIndex].dateStarted = now;
          } else if (newStatus === 'read') {
            books.value[bookIndex].dateFinished = now;
          }
        }
        return { success: true };
      } else {
        return { success: false, message: 'Failed to update book status' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const searchBooks = async (query) => {
    try {
      // Mock ISBNdb API integration
      const response = await fetch(`/api/books/search?q=${encodeURIComponent(query)}`, {
        headers: {
          'Accept': 'application/json',
        }
      });

      if (response.ok) {
        return await response.json();
      } else {
        return { success: false, message: 'Search failed' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  // Initialize with mock data if no API available
  if (books.value.length === 0) {
    initializeBooks();
  }

  return {
    books,
    loading,
    error,
    booksByStatus,
    booksCount,
    fetchBooks,
    addBook,
    updateBookStatus,
    searchBooks
  };
});