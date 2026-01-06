import { defineStore } from 'pinia';

export const useDiscussionsStore = defineStore('discussions', {
  state: () => ({
    discussionsByBook: {},
    currentDiscussion: null,
    loading: false,
    error: null,
  }),

  getters: {
    getDiscussions: (state) => (bookId) => {
      return state.discussionsByBook[bookId] || [];
    },
  },

  actions: {
    async fetchDiscussionsForBook(bookId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await fetch(`/api/books/${bookId}/threads`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          }
        });

        if (response.ok) {
          const data = await response.json();
          this.discussionsByBook[bookId] = data.data || [];
        } else {
          this.error = 'Failed to fetch discussions';
        }
      } catch (err) {
        this.error = err.message;
      } finally {
        this.loading = false;
      }
    },

    async createDiscussion(discussionData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await fetch('/api/threads', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          },
          body: JSON.stringify(discussionData)
        });

        if (response.ok) {
          const data = await response.json();
          return { success: true, data: data.data };
        } else {
          const errorData = await response.json();
          return { success: false, message: errorData.message || 'Failed to create discussion' };
        }
      } catch (err) {
        return { success: false, message: err.message };
      } finally {
        this.loading = false;
      }
    },

    async fetchDiscussion(threadId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await fetch(`/api/threads/${threadId}`, {
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          }
        });

        if (response.ok) {
          const data = await response.json();
          this.currentDiscussion = data.data;
          return { success: true, data: data.data };
        } else {
          this.error = 'Discussion not found';
          return { success: false };
        }
      } catch (err) {
        this.error = err.message;
        return { success: false };
      } finally {
        this.loading = false;
      }
    },

    async deleteDiscussion(threadId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await fetch(`/api/threads/${threadId}`, {
          method: 'DELETE',
          headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
          }
        });

        if (response.ok) {
          return { success: true };
        } else {
          const errorData = await response.json();
          return { success: false, message: errorData.message || 'Failed to delete discussion' };
        }
      } catch (err) {
        return { success: false, message: err.message };
      } finally {
        this.loading = false;
      }
    },
  },
});