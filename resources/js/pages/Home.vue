<template>
  <div class="px-4 py-8">
    <!-- Hero Section -->
    <div class="text-center mb-12">
      <h1 class="text-4xl font-bold text-gray-900 mb-4">
        {{ t('home.welcome') }}
      </h1>
      <p class="text-xl text-gray-600 max-w-2xl mx-auto">
        {{ t('home.heroDescription') }}
      </p>
    </div>

    <!-- Features Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">{{ t('home.bookManagementTitle') }}</h3>
        <p class="text-gray-600 text-sm">
          {{ t('home.bookManagementDesc') }}
        </p>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">{{ t('home.readingProgressTitle') }}</h3>
        <p class="text-gray-600 text-sm">
          {{ t('home.readingProgressDesc') }}
        </p>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">{{ t('home.discussionsTitle') }}</h3>
        <p class="text-gray-600 text-sm">
          {{ t('home.discussionsDesc') }}
        </p>
      </div>
      
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="font-semibold text-gray-900 mb-2">{{ t('home.socialTitle') }}</h3>
        <p class="text-gray-600 text-sm">
          {{ t('home.socialDesc') }}
        </p>
      </div>
    </div>

    <!-- Call to Action -->
    <div class="text-center bg-blue-50 p-8 rounded-lg">
      <h2 class="text-2xl font-bold text-gray-900 mb-4">
        {{ t('home.ctaTitle') }}
      </h2>
      <div class="space-x-4">
        <router-link 
          to="/books" 
          class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors"
        >
          {{ t('home.browseBooks') }}
        </router-link>
        <router-link 
          to="/profile" 
          class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors"
        >
          {{ t('home.viewProfile') }}
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>