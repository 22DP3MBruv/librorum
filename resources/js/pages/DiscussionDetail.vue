<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-6 text-sm text-gray-600">
      <router-link to="/" class="hover:text-blue-600">Sākums</router-link>
      <span class="mx-2">›</span>
      <router-link to="/discussions" class="hover:text-blue-600">Diskusijas</router-link>
      <span class="mx-2">›</span>
      <span class="text-gray-900">Diskusijas detaļas</span>
    </nav>

    <!-- Discussion Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
      <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Diskusijas nosaukums</h1>
          <div class="flex items-center text-sm text-gray-600 space-x-4">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
              </svg>
              Lietotājvārds
            </span>
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
              </svg>
              --
            </span>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <button class="flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
            </svg>
            0
          </button>
        </div>
      </div>

      <!-- Discussion Content -->
      <div class="prose max-w-none">
        <p class="text-gray-700 leading-relaxed">Diskusijas saturs tiks parādīts šeit...</p>
      </div>
    </div>

    <!-- Comments Section -->
    <div class="bg-white rounded-lg shadow-sm border">
      <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold text-gray-900">Komentāri (0)</h2>
      </div>

      <!-- Add Comment Form -->
      <div class="px-6 py-4 border-b bg-gray-50">
        <form class="space-y-3">
          <textarea
            placeholder="Pievienot komentāru..."
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
          ></textarea>
          <div class="flex justify-end">
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Pievienot komentāru
            </button>
          </div>
        </form>
      </div>

      <!-- Empty Comments State -->
      <div class="p-6">
        <div class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-900">Nav komentāru</h3>
          <p class="mt-2 text-gray-500">Esiet pirmais, kurš komentē šo diskusiju!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
// DiscussionDetail page logic will be implemented here
console.log('DiscussionDetail page loaded for discussion:', route.params.discussionId)
</script>
