<template>
  <div class="px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    <!-- Hero Section -->
    <div class="text-center mb-8 sm:mb-12">
      <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 sm:mb-4">
        {{ t('home.welcome') }}
      </h1>
      <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto px-2">
        {{ t('home.heroDescription') }}
      </p>
    </div>

    <!-- Features Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 sm:mb-12">
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
    <div class="text-center bg-blue-50 p-6 sm:p-8 rounded-lg">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4">
        {{ t('home.ctaTitle') }}
      </h2>
      <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
        <router-link 
          to="/books" 
          class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors inline-block"
        >
          {{ t('home.browseBooks') }}
        </router-link>
        <router-link 
          to="/profile" 
          class="border border-blue-600 text-blue-600 px-6 py-3 rounded-lg font-medium hover:bg-blue-50 transition-colors inline-block"
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