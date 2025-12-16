import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useReadingProgressStore = defineStore('readingProgress', () => {
  const progressList = ref([]);
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
  const progressByStatus = computed(() => {
    return {
      'want-to-read': progressList.value.filter(p => p.status === 'want-to-read'),
      'reading': progressList.value.filter(p => p.status === 'reading'),
      'read': progressList.value.filter(p => p.status === 'read')
    };
  });

  const progressCount = computed(() => ({
    total: progressList.value.length,
    wantToRead: progressByStatus.value['want-to-read'].length,
    reading: progressByStatus.value.reading.length,
    read: progressByStatus.value.read.length
  }));

  // Actions
  const fetchProgress = async () => {
    loading.value = true;
    error.value = null;
    try {
      const response = await fetch('/api/reading-progress', {
        headers: getAuthHeaders()
      });

      if (response.ok) {
        const data = await response.json();
        progressList.value = data.data || data;
      } else {
        const errorData = await response.json();
        error.value = errorData.message || 'Failed to fetch reading progress';
        progressList.value = [];
      }
    } catch (err) {
      error.value = err.message;
      progressList.value = [];
    } finally {
      loading.value = false;
    }
  };

  const addToReadingList = async (bookId, status = 'want_to_read') => {
    try {
      const response = await fetch('/api/reading-progress', {
        method: 'POST',
        headers: getAuthHeaders(),
        body: JSON.stringify({
          book_id: bookId,
          status: status,
          current_page: 0
        })
      });

      if (response.ok) {
        const data = await response.json();
        const newProgress = data.data || data;
        progressList.value.push(newProgress);
        return { success: true, progress: newProgress };
      } else {
        const errorData = await response.json();
        return { success: false, message: errorData.message || 'Failed to add to reading list' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const updateProgress = async (progressId, updates) => {
    try {
      const response = await fetch(`/api/reading-progress/${progressId}`, {
        method: 'PUT',
        headers: getAuthHeaders(),
        body: JSON.stringify(updates)
      });

      if (response.ok) {
        const data = await response.json();
        const updatedProgress = data.data || data;
        const index = progressList.value.findIndex(p => p.id === progressId);
        if (index !== -1) {
          progressList.value[index] = updatedProgress;
        }
        return { success: true, progress: updatedProgress };
      } else {
        const errorData = await response.json();
        return { success: false, message: errorData.message || 'Failed to update progress' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const removeFromReadingList = async (progressId) => {
    try {
      const response = await fetch(`/api/reading-progress/${progressId}`, {
        method: 'DELETE',
        headers: getAuthHeaders()
      });

      if (response.ok) {
        progressList.value = progressList.value.filter(p => p.id !== progressId);
        return { success: true };
      } else {
        const errorData = await response.json();
        return { success: false, message: errorData.message || 'Failed to remove from reading list' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const getProgressForBook = async (bookId) => {
    try {
      const response = await fetch(`/api/reading-progress/book/${bookId}`, {
        headers: getAuthHeaders()
      });

      if (response.ok) {
        const data = await response.json();
        return { success: true, progress: data.data || data };
      } else {
        return { success: false, message: 'No progress found' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const isBookInReadingList = (bookId) => {
    return progressList.value.some(p => p.book_id === bookId);
  };

  const getBookProgress = (bookId) => {
    return progressList.value.find(p => p.book_id === bookId);
  };

  return {
    progressList,
    loading,
    error,
    progressByStatus,
    progressCount,
    fetchProgress,
    addToReadingList,
    updateProgress,
    removeFromReadingList,
    getProgressForBook,
    isBookInReadingList,
    getBookProgress
  };
});
