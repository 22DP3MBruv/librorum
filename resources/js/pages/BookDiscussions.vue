<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Breadcrumb Navigation -->
    <nav class="flex mb-6 text-sm text-gray-600">
      <router-link to="/" class="hover:text-blue-600">Sākums</router-link>
      <span class="mx-2">›</span>
      <router-link to="/books" class="hover:text-blue-600">Grāmatas</router-link>
      <span class="mx-2">›</span>
      <span class="text-gray-900">Grāmatas diskusijas</span>
    </nav>

    <!-- Book Header -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
      <div class="flex items-start space-x-4">
        <div class="w-16 h-24 bg-gray-300 rounded flex items-center justify-center">
          <svg class="w-8 h-8 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
          </svg>
        </div>
        <div class="flex-1">
          <h1 class="text-2xl font-bold text-gray-900 mb-2">Grāmatas nosaukums</h1>
          <p class="text-gray-600 mb-2">Autora vārds</p>
          <p class="text-sm text-gray-500">ISBN: --</p>
        </div>
        <div>
          <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
            Pievienot lasīšanas sarakstam
          </button>
        </div>
      </div>
    </div>

    <!-- Discussions Section -->
    <div class="bg-white rounded-lg shadow-sm border">
      <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-900">Diskusijas par šo grāmatu</h2>
        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
          Izveidot diskusiju
        </button>
      </div>

      <!-- Empty State -->
      <div class="p-6">
        <div class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
          <h3 class="mt-4 text-lg font-medium text-gray-900">Nav diskusiju par šo grāmatu</h3>
          <p class="mt-2 text-gray-500">Esiet pirmais, kurš sāk sarunu par šo grāmatu.</p>
          <div class="mt-6">
            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
              Sākt diskusiju
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router'

const route = useRoute()
// BookDiscussions page logic will be implemented here
console.log('BookDiscussions page loaded for ISBN:', route.params.isbn)
</script>
