import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useDiscussionsStore = defineStore('discussions', () => {
  const discussions = ref([]);
  const loading = ref(false);
  const error = ref(null);

  // Mock initial data
  const initializeDiscussions = () => {
    discussions.value = [
      {
        id: 1,
        title: "Kā jums patika Harija Potera sērija?",
        content: "Nupat pabeidzu lasīt visu sēriju no jauna un gribētu dzirdēt jūsu viedokļus par rakstnieces darbu. Man šķiet, ka katrai grāmatai ir savs unikāls raksturs un atmosfēra.",
        excerpt: "Nupat pabeidzu lasīt visu sēriju no jauna un gribētu dzirdēt jūsu viedokļus par rakstnieces darbu...",
        author: "Anna_K",
        authorId: 1,
        bookId: 1,
        book: "Harijs Poters",
        category: "book-discussion",
        createdAt: "2025-11-01T10:30:00Z",
        updatedAt: "2025-11-01T10:30:00Z",
        commentsCount: 12,
        likesCount: 8,
        isLiked: false,
        isPinned: false,
        status: "active",
        comments: [
          {
            id: 1,
            content: "Man visvairāk patika trešā grāmata, jo tur sākās īstā darbība un raksturi kļuva sarežģītāki.",
            author: "Marks_B",
            authorId: 2,
            createdAt: "2025-11-01T14:15:00Z",
            likesCount: 3,
            isLiked: true
          }
        ]
      },
      {
        id: 2,
        title: "1984 - Dystopijas aktualitāte mūsdienās",
        content: "Pēc tam, kad izlasīju 1984, mani pārsteidza, cik aktuāla šī grāmata ir mūsdienās. Kādi ir jūsu viedokļi par Orwella vīziju un tās saistību ar mūsdienu pasauli?",
        excerpt: "Pēc tam, kad izlasīju 1984, mani pārsteidza, cik aktuāla šī grāmata ir mūsdienās...",
        author: "ReadingFan",
        authorId: 3,
        bookId: 2,
        book: "1984",
        category: "book-discussion",
        createdAt: "2025-10-28T15:45:00Z",
        updatedAt: "2025-10-30T09:20:00Z",
        commentsCount: 8,
        likesCount: 15,
        isLiked: false,
        isPinned: true,
        status: "active",
        comments: [
          {
            id: 2,
            content: "Noteikti ieteiktu 'Fahrenheit 451' - klasika žanrā! Arī 'The Handmaid's Tale' ir ļoti spēcīga.",
            author: "BookLover23",
            authorId: 4,
            createdAt: "2025-10-29T11:30:00Z",
            likesCount: 5,
            isLiked: false
          }
        ]
      }
    ];
  };

  // Computed properties - all discussions are now book-specific
  const discussionsByBook = computed(() => {
    const books = {};
    discussions.value.forEach(discussion => {
      const bookId = discussion.bookId;
      if (bookId && !books[bookId]) {
        books[bookId] = [];
      }
      if (bookId) {
        books[bookId].push(discussion);
      }
    });
    return books;
  });

  const pinnedDiscussions = computed(() => 
    discussions.value.filter(d => d.isPinned)
  );

  const recentDiscussions = computed(() => 
    discussions.value
      .filter(d => !d.isPinned)
      .sort((a, b) => new Date(b.updatedAt) - new Date(a.updatedAt))
  );

  // Actions
  const fetchDiscussions = async () => {
    loading.value = true;
    try {
      const response = await fetch('/api/discussions', {
        headers: {
          'Accept': 'application/json',
        }
      });

      if (response.ok) {
        const data = await response.json();
        discussions.value = data;
      } else {
        // Fallback to mock data
        initializeDiscussions();
      }
    } catch (err) {
      error.value = err.message;
      initializeDiscussions();
    } finally {
      loading.value = false;
    }
  };

  const createDiscussion = async (discussionData) => {
    try {
      const response = await fetch('/api/discussions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(discussionData)
      });

      if (response.ok) {
        const newDiscussion = await response.json();
        discussions.value.unshift(newDiscussion);
        return { success: true, discussion: newDiscussion };
      } else {
        return { success: false, message: 'Failed to create discussion' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const addComment = async (discussionId, commentData) => {
    try {
      const response = await fetch(`/api/discussions/${discussionId}/comments`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(commentData)
      });

      if (response.ok) {
        const newComment = await response.json();
        const discussion = discussions.value.find(d => d.id === discussionId);
        if (discussion) {
          discussion.comments.push(newComment);
          discussion.commentsCount++;
          discussion.updatedAt = new Date().toISOString();
        }
        return { success: true, comment: newComment };
      } else {
        return { success: false, message: 'Failed to add comment' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const toggleLike = async (discussionId) => {
    try {
      const discussion = discussions.value.find(d => d.id === discussionId);
      if (!discussion) return { success: false };

      const response = await fetch(`/api/discussions/${discussionId}/like`, {
        method: discussion.isLiked ? 'DELETE' : 'POST',
        headers: {
          'Accept': 'application/json',
        }
      });

      if (response.ok) {
        discussion.isLiked = !discussion.isLiked;
        discussion.likesCount += discussion.isLiked ? 1 : -1;
        return { success: true };
      } else {
        return { success: false, message: 'Failed to toggle like' };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const getDiscussion = (id) => {
    return discussions.value.find(d => d.id === parseInt(id));
  };

  // Initialize with mock data
  if (discussions.value.length === 0) {
    initializeDiscussions();
  }

  return {
    discussions,
    loading,
    error,
    discussionsByBook,
    pinnedDiscussions,
    recentDiscussions,
    fetchDiscussions,
    createDiscussion,
    addComment,
    toggleLike,
    getDiscussion
  };
});