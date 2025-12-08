<template>
  <div class="container mx-auto px-4 py-6">
    <!-- Page Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ t('profile.title') }}</h1>
      <p class="text-gray-600">{{ t('profile.subtitle') }}</p>
    </div>

    <!-- Profile Cards Grid -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
      <!-- User Info Card -->
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <div class="text-center">
          <div class="w-20 h-20 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center">
            <span class="text-2xl font-bold text-gray-600">?</span>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ t('profile.username') }}</h3>
          <p class="text-gray-500 text-sm">{{ t('profile.joined') }}: --</p>
        </div>
      </div>

      <!-- Reading Stats Card -->
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('profile.readingStats') }}</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">{{ t('profile.booksRead') }}:</span>
            <span class="font-medium">0</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ t('profile.currentlyReading') }}:</span>
            <span class="font-medium">0</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ t('profile.planToRead') }}:</span>
            <span class="font-medium">0</span>
          </div>
        </div>
      </div>

      <!-- Activity Card -->
      <div class="bg-white p-6 rounded-lg shadow-sm border">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ t('profile.activity') }}</h3>
        <div class="space-y-3">
          <div class="flex justify-between">
            <span class="text-gray-600">{{ t('profile.discussions') }}:</span>
            <span class="font-medium">0</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ t('profile.comments') }}:</span>
            <span class="font-medium">0</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-600">{{ t('profile.followers') }}:</span>
            <span class="font-medium">0</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-sm border">
      <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-semibold text-gray-900">{{ t('profile.recentActivity') }}</h2>
      </div>
      <div class="p-6">
        <div class="text-center py-8">
          <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
          <p class="mt-2 text-gray-500">{{ t('profile.noRecentActivity') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Profile page logic will be implemented here
console.log('Profile page loaded')
</script>
