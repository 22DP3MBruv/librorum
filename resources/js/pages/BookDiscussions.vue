<template>
  <div class="container mx-auto px-4 sm:px-6 py-6">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-4 sm:mb-6 text-xs sm:text-sm text-gray-600 overflow-x-auto whitespace-nowrap pb-2">
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
      <div class="bg-white rounded-lg shadow-sm border p-4 sm:p-6 mb-6">
        <div class="flex flex-col lg:flex-row items-start gap-4 sm:gap-6">
          <!-- Left side: Book Cover -->
          <div class="w-full sm:w-48 lg:w-60 h-64 sm:h-72 lg:h-90 bg-gray-200 rounded flex-shrink-0 overflow-hidden mx-auto lg:mx-0">
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
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">{{ book.title }}</h1>
            <p class="text-sm sm:text-base text-gray-600 mb-2">{{ book.author }}</p>
            <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-500 mb-3">
              <span v-if="book.isbn" class="truncate">ISBN: {{ book.isbn }}</span>
              <span v-if="book.page_count">{{ book.page_count }} {{ t('books.pages') }}</span>
              <span v-if="book.publication_year">{{ book.publication_year }}</span>
              <span v-if="book.genre" class="bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ book.genre }}</span>
            </div>
            <div v-if="book.description" class="text-xs sm:text-sm text-gray-700">
              <p :class="descriptionExpanded ? '' : 'line-clamp-3'">{{ book.description }}</p>
              <button 
                @click="descriptionExpanded = !descriptionExpanded"
                class="text-blue-600 hover:text-blue-700 mt-2 text-xs sm:text-sm font-medium"
              >
                {{ descriptionExpanded ? t('common.showLess') : t('common.showMore') }}
              </button>
            </div>
            <div v-if="authStore.isAuthenticated" class="mt-4">
              <button 
                @click="toggleBookmark"
                :class="[
                  'px-3 sm:px-4 py-2 rounded-lg transition-colors flex items-center whitespace-nowrap text-sm',
                  isBookmarked ? 'bg-blue-600 text-white hover:bg-blue-700' : 'border border-blue-600 text-blue-600 hover:bg-blue-50'
                ]"
              >
                <svg class="w-4 sm:w-5 h-4 sm:h-5 mr-1.5 sm:mr-2 flex-shrink-0" :fill="isBookmarked ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                </svg>
                <span class="hidden sm:inline">{{ isBookmarked ? t('discussions.inReadingList') : t('discussions.addToReadingList') }}</span>
                <span class="sm:hidden">{{ isBookmarked ? t('discussions.inList') : t('discussions.addToList') }}</span>
              </button>
            </div>
          </div>

          <!-- Right side: Admin Panel -->
          <div v-if="authStore.isAdmin" class="w-full lg:w-72 flex-shrink-0 bg-gray-50 rounded-lg p-3 sm:p-4 border border-gray-200">
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
                <span class="ml-1 font-mono text-gray-700">{{ book.id }}</span>
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
                <span class="text-gray-500">{{ t('books.publishYear') }}:</span>
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
                <button 
                  @click="openEditBookModal"
                  class="w-full px-3 py-1.5 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                >
                  {{ t('books.editBook') }}
                </button>
                <button 
                  @click="syncBookWithApi"
                  :disabled="syncLoading"
                  class="w-full px-3 py-1.5 text-xs bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ syncLoading ? t('common.loading') : t('books.syncWithApi') }}
                </button>
                <button 
                  @click="showDeleteConfirm = true"
                  :disabled="deleteLoading"
                  class="w-full px-3 py-1.5 text-xs bg-red-600 text-white rounded hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  {{ deleteLoading ? t('common.loading') : t('books.deleteBook') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Discussions Section -->
      <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <h2 class="text-base sm:text-lg font-semibold text-gray-900">{{ t('discussions.discussions') }}</h2>
            <button 
              v-if="authStore.isAuthenticated"
              @click="showNewDiscussionModal = true"
              class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm"
            >
              {{ t('discussions.newDiscussion') }}
            </button>
          </div>
          
          <!-- Filters and Sort -->
          <div class="flex flex-col sm:flex-row gap-3">
            <!-- Scope Filter -->
            <select
              v-model="scopeFilter"
              @change="currentPage = 1"
              class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="all">{{ t('discussions.allScopes') }}</option>
              <option value="general">{{ t('discussions.generalOnly') }}</option>
              <option value="page">{{ t('discussions.pageSpecificOnly') }}</option>
            </select>
            
            <!-- Sort Options -->
            <select
              v-model="sortBy"
              @change="currentPage = 1"
              class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="newest">{{ t('discussions.sortNewest') }}</option>
              <option value="oldest">{{ t('discussions.sortOldest') }}</option>
              <option value="mostLiked">{{ t('discussions.sortMostLiked') }}</option>
              <option value="mostReplies">{{ t('discussions.sortMostReplies') }}</option>
            </select>
          </div>
        </div>

        <!-- Discussions List -->
        <div v-if="filteredAndSortedDiscussions.length > 0">
          <div class="divide-y">
            <div
              v-for="discussion in paginatedDiscussions"
              :key="discussion.id"
              class="p-4 sm:p-6 hover:bg-gray-50 transition-colors flex items-start gap-3 sm:gap-4"
            >
            <!-- Upvote Section -->
            <div class="flex flex-col items-center gap-1 flex-shrink-0">
              <button
                v-if="authStore.isAuthenticated"
                @click.stop="toggleLike(discussion.id)"
                class="p-1 rounded hover:bg-gray-200 transition-colors"
                :class="discussion.user_liked ? 'text-orange-500' : 'text-gray-400'"
              >
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 4l3.09 6.26L22 11.27l-5 4.87 1.18 6.88L12 19.77l-6.18 3.25L7 16.14 2 11.27l6.91-1.01L12 4z"></path>
                </svg>
              </button>
              <span class="text-sm font-medium" :class="discussion.user_liked ? 'text-orange-500' : 'text-gray-700'">
                {{ discussion.likes_count || 0 }}
              </span>
            </div>

            <!-- Discussion Content -->
            <div class="cursor-pointer flex-1 relative">
              <!-- Spoiler Overlay -->
              <div 
                v-if="shouldShowSpoiler(discussion)"
                @click.stop="revealSpoiler(discussion.id)"
                class="absolute inset-0 bg-gray-400 bg-opacity-95 backdrop-blur-sm rounded-lg flex items-center justify-center z-10 transition-opacity hover:bg-opacity-100 cursor-pointer overflow-hidden"
              >
                <div class="flex items-center gap-3 px-6">
                  <p class="text-gray-900 font-semibold text-base">{{ t('discussions.spoilerWarning') }}:</p>
                  <p class="text-gray-700 text-sm">
                    {{ discussion.scope === 'page' && discussion.page_number 
                      ? t('discussions.spoilerPage', { page: discussion.page_number })
                      : t('discussions.spoilerGeneral') 
                    }}
                  </p>
                  <p class="text-blue-600 text-sm font-medium">{{ t('discussions.clickToReveal') }}</p>
                </div>
              </div>

              <!-- Actual Discussion Content -->
              <div @click="goToDiscussion(discussion.id)" class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">{{ discussion.title }}</h3>
                  <p class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-3 line-clamp-2">{{ discussion.content }}</p>
                  <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs text-gray-500">
                    <span>
                      {{ t('discussions.by') }} 
                      <button
                        @click.stop="goToUserProfile(discussion.author?.user_id)"
                        class="font-medium hover:text-blue-600 hover:underline transition-colors"
                      >
                        {{ discussion.author?.name || 'Unknown' }}
                      </button>
                    </span>
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
          </div>
          
          <!-- Pagination Controls -->
          <div v-if="totalPages > 1" class="px-4 sm:px-6 py-4 border-t flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-sm text-gray-600">
              {{ t('pagination.showing') }} {{ ((currentPage - 1) * itemsPerPage) + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredAndSortedDiscussions.length) }} {{ t('pagination.of') }} {{ filteredAndSortedDiscussions.length }}
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="prevPage"
                :disabled="currentPage === 1"
                class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span class="hidden sm:inline">{{ t('pagination.previous') }}</span>
                <span class="sm:hidden">‹</span>
              </button>
              <button
                v-for="page in paginationRange"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-2 text-sm rounded-lg transition-colors',
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
                class="px-3 py-2 text-sm border rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <span class="hidden sm:inline">{{ t('pagination.next') }}</span>
                <span class="sm:hidden">›</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="discussions.length === 0" class="p-6">
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
        
        <!-- No Results State (when filter returns no results) -->
        <div v-else class="p-6">
          <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('discussions.noResults') }}</h3>
            <p class="mt-2 text-gray-500">{{ t('discussions.noResultsDescription') }}</p>
            <button 
              @click="resetFilters"
              class="mt-6 text-blue-600 hover:text-blue-700 font-medium"
            >
              {{ t('discussions.clearFilters') }}
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- New Discussion Modal -->
    <div v-if="showNewDiscussionModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
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

    <!-- Edit Book Modal -->
    <div v-if="showEditBookModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">{{ t('books.editBook') }}</h3>
          <button @click="closeEditBookModal" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div v-if="editBookError" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded mb-4">
          {{ editBookError }}
        </div>
        
        <form @submit.prevent="handleEditBook" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ t('books.title') }}</label>
              <input 
                v-model="editBookData.title" 
                type="text" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ t('books.author') }}</label>
              <input 
                v-model="editBookData.author" 
                type="text" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">ISBN</label>
              <input 
                v-model="editBookData.isbn" 
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">ISBN-10</label>
              <input 
                v-model="editBookData.isbn10" 
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">ISBN-13</label>
              <input 
                v-model="editBookData.isbn13" 
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ t('books.genre') }}</label>
              <input 
                v-model="editBookData.genre" 
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ t('books.pages') }}</label>
              <input 
                v-model.number="editBookData.page_count" 
                type="number"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ t('books.publishYear') }}</label>
              <input 
                v-model.number="editBookData.publication_year" 
                type="number"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ t('books.publisher') }}</label>
              <input 
                v-model="editBookData.publisher" 
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">{{ t('books.language') }}</label>
              <input 
                v-model="editBookData.language" 
                type="text"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ t('books.coverImageUrl') }}</label>
            <input 
              v-model="editBookData.cover_image_url" 
              type="url"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ t('books.description') }}</label>
            <textarea 
              v-model="editBookData.description" 
              rows="4"
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            ></textarea>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4">
            <button 
              type="button" 
              @click="closeEditBookModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
            >
              {{ t('common.cancel') }}
            </button>
            <button 
              type="submit" 
              :disabled="editBookLoading"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
            >
              {{ editBookLoading ? t('common.saving') : t('common.save') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">{{ t('books.confirmDelete') || 'Delete Book?' }}</h3>
          </div>
        </div>
        
        <p class="text-sm text-gray-500 mb-6">
          {{ t('books.deleteWarning') || 'Are you sure you want to delete this book? This action cannot be undone and will also delete all associated discussions and comments.' }}
        </p>
        
        <div class="flex justify-end space-x-3">
          <button 
            @click="showDeleteConfirm = false"
            :disabled="deleteLoading"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md disabled:opacity-50"
          >
            {{ t('common.cancel') }}
          </button>
          <button 
            @click="handleDeleteBook"
            :disabled="deleteLoading"
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md disabled:opacity-50"
          >
            {{ deleteLoading ? t('common.deleting') : t('common.delete') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">{{ t('common.success') }}</h3>
          </div>
        </div>
        
        <p class="text-sm text-gray-700 mb-6">
          {{ successMessage }}
        </p>
        
        <div class="flex justify-end">
          <button 
            @click="closeSuccessModal"
            class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md"
          >
            {{ t('common.close') }}
          </button>
        </div>
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
const showEditBookModal = ref(false);
const editBookData = ref({});
const editBookLoading = ref(false);
const editBookError = ref('');
const syncLoading = ref(false);
const deleteLoading = ref(false);
const showDeleteConfirm = ref(false);
const showSuccessModal = ref(false);
const successMessage = ref('');
const revealedSpoilers = ref(new Set());
const scopeFilter = ref('all');
const sortBy = ref('newest');

// Pagination
const currentPage = ref(1);
const itemsPerPage = ref(10);

const isBookmarked = computed(() => {
  return book.value && progressStore.isBookInReadingList(book.value.id);
});

const filteredAndSortedDiscussions = computed(() => {
  let filtered = [...discussions.value];
  
  // Apply scope filter
  if (scopeFilter.value !== 'all') {
    filtered = filtered.filter(d => d.scope === scopeFilter.value);
  }
  
  // Apply sorting
  switch (sortBy.value) {
    case 'newest':
      filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    case 'oldest':
      filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      break;
    case 'mostLiked':
      filtered.sort((a, b) => (b.likes_count || 0) - (a.likes_count || 0));
      break;
    case 'mostReplies':
      filtered.sort((a, b) => (b.comments_count || 0) - (a.comments_count || 0));
      break;
  }
  
  return filtered;
});

const totalPages = computed(() => {
  return Math.ceil(filteredAndSortedDiscussions.value.length / itemsPerPage.value);
});

const paginatedDiscussions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredAndSortedDiscussions.value.slice(start, end);
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
  
  await discussionsStore.fetchDiscussionsForBook(book.value.id);
  discussions.value = discussionsStore.discussionsByBook[book.value.id] || [];
  
  // Fetch like status for each discussion if authenticated
  if (authStore.isAuthenticated) {
    await fetchLikeStatuses();
  }
};

const fetchLikeStatuses = async () => {
  const token = localStorage.getItem('auth_token');
  for (const discussion of discussions.value) {
    try {
      const response = await fetch(`/api/likes/status?target_type=thread&target_id=${discussion.id}`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      if (response.ok) {
        const data = await response.json();
        discussion.user_liked = data.user_liked;
        discussion.likes_count = data.like_count;
      }
    } catch (err) {
      console.error('Failed to fetch like status', err);
    }
  }
};

const toggleLike = async (discussionId) => {
  if (!authStore.isAuthenticated) return;
  
  const token = localStorage.getItem('auth_token');
  try {
    const response = await fetch('/api/likes/toggle', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        target_type: 'thread',
        target_id: discussionId
      })
    });
    
    if (response.ok) {
      const data = await response.json();
      const discussion = discussions.value.find(d => d.id === discussionId);
      if (discussion) {
        discussion.user_liked = data.liked;
        discussion.likes_count = (discussion.likes_count || 0) + (data.liked ? 1 : -1);
      }
    }
  } catch (err) {
    console.error('Failed to toggle like', err);
  }
};

const toggleBookmark = async () => {
  if (!book.value) return;
  
  if (!book.value.id) {
    console.error('Book ID is missing:', book.value);
    alert('Error: Book ID is missing');
    return;
  }
  
  const bookProgress = progressStore.getBookProgress(book.value.id);
  
  if (bookProgress) {
    await progressStore.removeFromReadingList(bookProgress.id);
  } else {
    await progressStore.addToReadingList(book.value.id, 'want_to_read');
  }
};

const goToDiscussion = (discussionId) => {
  router.push(`/books/${route.params.isbn}/${discussionId}`);
};

const goToUserProfile = (userId) => {
  if (!userId) return;
  if (parseInt(userId) === authStore.user?.id) { // Checks if the profile is the current user
    router.push('/profile');
  } else {
    router.push(`/profile/${userId}`);
  }
};

const shouldShowSpoiler = (discussion) => {
  // Don't show spoiler if already manually revealed
  if (revealedSpoilers.value.has(discussion.id)) {
    return false;
  }

  // Don't show spoiler for general discussions
  if (discussion.scope === 'general') {
    return false;
  }

  // Check if user has the book in their reading list
  if (!book.value || !authStore.isAuthenticated) {
    // Show spoiler if not authenticated or book not loaded
    return discussion.scope === 'page';
  }

  const bookProgress = progressStore.getBookProgress(book.value.id);
  
  // If book is not in reading list, show spoiler for page-specific discussions
  if (!bookProgress) {
    return discussion.scope === 'page';
  }

  // If book is in reading list and it's a page-specific discussion
  if (discussion.scope === 'page' && discussion.page_number) {
    // Auto-reveal if user's progress is beyond the discussion's page
    if (bookProgress.current_page && bookProgress.current_page >= discussion.page_number) {
      return false; // Don't show spoiler - user has read past this point
    }
    return true; // Show spoiler - user hasn't reached this page yet
  }

  return false;
};

const revealSpoiler = (discussionId) => {
  revealedSpoilers.value.add(discussionId);
};

const handleCreateDiscussion = async () => {
  if (!book.value) return;
  
  discussionLoading.value = true;
  discussionError.value = '';
  
  const discussionData = {
    book_id: book.value.id,
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

const openEditBookModal = () => {
  if (!book.value) return;
  
  editBookData.value = {
    title: book.value.title || '',
    author: book.value.author || '',
    isbn: book.value.isbn || '',
    isbn10: book.value.isbn10 || '',
    isbn13: book.value.isbn13 || '',
    description: book.value.description || '',
    genre: book.value.genre || '',
    page_count: book.value.page_count || '',
    publication_year: book.value.publication_year || '',
    publisher: book.value.publisher || '',
    language: book.value.language || '',
    cover_image_url: book.value.cover_image_url || ''
  };
  
  showEditBookModal.value = true;
  editBookError.value = '';
};

const closeEditBookModal = () => {
  showEditBookModal.value = false;
  editBookData.value = {};
  editBookError.value = '';
};

const handleEditBook = async () => {
  if (!book.value) return;
  
  editBookLoading.value = true;
  editBookError.value = '';
  
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/books/${book.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify(editBookData.value)
    });
    
    const data = await response.json();
    
    if (response.ok) {
      book.value = data.data || data;
      closeEditBookModal();
      successMessage.value = t('books.bookUpdated');
      showSuccessModal.value = true;
    } else {
      editBookError.value = data.message || t('books.updateFailed') || 'Failed to update book';
    }
  } catch (err) {
    editBookError.value = err.message || t('books.updateFailed') || 'Failed to update book';
  } finally {
    editBookLoading.value = false;
  }
};

const syncBookWithApi = async () => {
  if (!book.value) return;
  
  syncLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/books/${book.value.id}/sync`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });
    
    const data = await response.json();
    
    if (response.ok) {
      book.value = data.data || data;
      successMessage.value = t('books.syncSuccess');
      showSuccessModal.value = true;
    } else {
      alert(data.message || t('books.syncFailed'));
    }
  } catch (err) {
    alert(err.message || t('books.syncFailed') || 'Failed to sync book');
  } finally {
    syncLoading.value = false;
  }
};

const handleDeleteBook = async () => {
  if (!book.value) return;
  
  deleteLoading.value = true;
  
  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch(`/api/books/${book.value.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Authorization': `Bearer ${token}`
      }
    });
    
    if (response.ok) {
      successMessage.value = t('books.bookDeleted');
      showSuccessModal.value = true;
      setTimeout(() => {
        router.push('/books');
      }, 1500);
    } else {
      const data = await response.json();
      alert(data.message || t('books.deleteFailed') || 'Failed to delete book');
    }
  } catch (err) {
    alert(err.message || t('books.deleteFailed') || 'Failed to delete book');
  } finally {
    deleteLoading.value = false;
    showDeleteConfirm.value = false;
  }
};

const closeSuccessModal = () => {
  showSuccessModal.value = false;
  successMessage.value = '';
};

const resetFilters = () => {
  scopeFilter.value = 'all';
  sortBy.value = 'newest';
  currentPage.value = 1;
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
