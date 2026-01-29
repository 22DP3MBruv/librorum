<template>
  <div class="follow-requests-page">
    <div class="container">
      <FollowRequests />
    </div>
  </div>
</template>

<script setup>
import FollowRequests from '../components/FollowRequests.vue';
</script>

<style scoped>
.follow-requests-page {
  min-height: calc(100vh - 64px);
  background-color: #f9fafb;
  padding-top: 2rem;
  padding-bottom: 2rem;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1rem;
}

@media (min-width: 640px) {
  .container {
    padding: 0 1.5rem;
  }
}

@media (min-width: 1024px) {
  .container {
    padding: 0 2rem;
  }
}
</style>
