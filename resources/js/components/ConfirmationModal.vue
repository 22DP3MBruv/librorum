<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Backdrop -->
    <div
      class="absolute inset-0 bg-black bg-opacity-50 transition-opacity"
      @click="cancel"
    ></div>

    <!-- Modal -->
    <div class="relative bg-white rounded-lg shadow-lg max-w-sm w-full mx-4 overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      </div>

      <!-- Body -->
      <div class="px-6 py-4">
        <p class="text-gray-700">{{ message }}</p>
      </div>

      <!-- Footer -->
      <div class="px-6 py-3 border-t border-gray-200 bg-gray-50 flex gap-3 justify-end">
        <button
          @click="cancel"
          :disabled="isLoading"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          {{ cancelLabel }}
        </button>
        <button
          @click="confirm"
          :disabled="isLoading"
          :class="[
            'px-4 py-2 text-sm font-medium text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors',
            isDangerous
              ? 'bg-red-600 hover:bg-red-700'
              : 'bg-blue-600 hover:bg-blue-700'
          ]"
        >
          {{ isLoading ? $t('common.loading') : confirmLabel }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const isOpen = ref(false);
const isLoading = ref(false);
const title = ref('');
const message = ref('');
const confirmLabel = ref('Confirm');
const cancelLabel = ref('Cancel');
const isDangerous = ref(false);
let resolveCallback = null;

const show = (options = {}) => {
  return new Promise((resolve) => {
    title.value = options.title || 'Confirmation';
    message.value = options.message || '';
    confirmLabel.value = options.confirmLabel || 'Confirm';
    cancelLabel.value = options.cancelLabel || 'Cancel';
    isDangerous.value = options.isDangerous || false;
    isLoading.value = false;
    isOpen.value = true;
    resolveCallback = resolve;
  });
};

const confirm = async () => {
  if (resolveCallback) {
    isLoading.value = true;
    resolveCallback(true);
  }
};

const cancel = () => {
  if (isOpen.value && !isLoading.value && resolveCallback) {
    isOpen.value = false;
    resolveCallback(false);
  }
};

const setLoading = (loading) => {
  isLoading.value = loading;
};

const close = () => {
  isOpen.value = false;
};

defineExpose({
  show,
  setLoading,
  close,
});
</script>

<style scoped>
/* Modal entrance animation */
.fixed {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
