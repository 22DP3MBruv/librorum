<template>
  <div class="follow-requests">
    <h2>{{ $t('followRequests.title') }}</h2>
    
    <div v-if="loading" class="loading">
      {{ $t('common.loading') }}
    </div>
    
    <div v-else-if="error" class="error">
      {{ error }}
    </div>
    
    <div v-else-if="requests.length === 0" class="no-requests">
      <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
      </svg>
      <p>{{ $t('followRequests.noRequests') }}</p>
    </div>
    
    <div v-else class="requests-list">
      <div
        v-for="request in requests"
        :key="request.request_id"
        class="request-item"
      >
        <div class="request-content">
          <div class="user-avatar">
            <span>{{ request.follower.name.charAt(0).toUpperCase() }}</span>
          </div>
          <div class="user-info">
            <h4 class="user-name">{{ request.follower.name }}</h4>
            <p class="request-time">{{ formatDate(request.created_at) }}</p>
          </div>
        </div>
        <div class="request-actions">
          <button
            @click="acceptRequest(request.request_id)"
            :disabled="processing === request.request_id"
            class="btn btn-accept"
          >
            {{ processing === request.request_id ? $t('common.loading') : $t('followRequests.accept') }}
          </button>
          <button
            @click="rejectRequest(request.request_id)"
            :disabled="processing === request.request_id"
            class="btn btn-reject"
          >
            {{ processing === request.request_id ? $t('common.loading') : $t('followRequests.reject') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const authStore = useAuthStore();

const requests = ref([]);
const loading = ref(true);
const error = ref(null);
const processing = ref(null);

const formatDate = (date) => {
  if (!date) return '';
  const d = new Date(date);
  const now = new Date();
  const diffMs = now - d;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return t('time.justNow');
  if (diffMins < 60) return t('time.minutesAgo', { count: diffMins });
  if (diffHours < 24) return t('time.hoursAgo', { count: diffHours });
  if (diffDays < 7) return t('time.daysAgo', { count: diffDays });
  
  return d.toLocaleDateString('lv-LV', { year: 'numeric', month: 'long', day: 'numeric' });
};

const fetchRequests = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await fetch('/api/user/follow-requests', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      const data = await response.json();
      requests.value = data.data;
    } else {
      error.value = t('followRequests.fetchError');
    }
  } catch (err) {
    error.value = t('common.networkError');
  } finally {
    loading.value = false;
  }
};

const acceptRequest = async (requestId) => {
  try {
    processing.value = requestId;
    
    const response = await fetch(`/api/user/follow-requests/${requestId}/accept`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      // Remove the request from the list
      requests.value = requests.value.filter(r => r.request_id !== requestId);
    } else {
      const errorData = await response.json();
      alert(errorData.message || t('followRequests.acceptError'));
    }
  } catch (err) {
    alert(t('common.networkError'));
  } finally {
    processing.value = null;
  }
};

const rejectRequest = async (requestId) => {
  try {
    processing.value = requestId;
    
    const response = await fetch(`/api/user/follow-requests/${requestId}/reject`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      // Remove the request from the list
      requests.value = requests.value.filter(r => r.request_id !== requestId);
    } else {
      const errorData = await response.json();
      alert(errorData.message || t('followRequests.rejectError'));
    }
  } catch (err) {
    alert(t('common.networkError'));
  } finally {
    processing.value = null;
  }
};

onMounted(() => {
  fetchRequests();
});
</script>

<style scoped>
.follow-requests {
  max-width: 800px;
  margin: 0 auto;
  padding: 2rem;
}

.follow-requests h2 {
  font-size: 1.875rem;
  font-weight: bold;
  color: #111827;
  margin-bottom: 1.5rem;
}

.loading,
.error,
.no-requests {
  text-align: center;
  padding: 3rem 1rem;
}

.loading {
  color: #6b7280;
}

.error {
  color: #dc2626;
  background-color: #fee;
  border: 1px solid #fecaca;
  border-radius: 0.5rem;
  padding: 1rem;
}

.no-requests {
  color: #6b7280;
}

.no-requests .icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 1rem;
  color: #d1d5db;
}

.requests-list {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.request-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  gap: 1rem;
}

.request-item:last-child {
  border-bottom: none;
}

.request-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex: 1;
}

.user-avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-avatar span {
  color: white;
  font-size: 1.25rem;
  font-weight: bold;
}

.user-info {
  flex: 1;
}

.user-name {
  font-size: 1rem;
  font-weight: 600;
  color: #111827;
  margin: 0 0 0.25rem 0;
}

.request-time {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.request-actions {
  display: flex;
  gap: 0.75rem;
  flex-shrink: 0;
}

.btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-accept {
  background-color: #10b981;
  color: white;
}

.btn-accept:hover:not(:disabled) {
  background-color: #059669;
}

.btn-reject {
  background-color: #ef4444;
  color: white;
}

.btn-reject:hover:not(:disabled) {
  background-color: #dc2626;
}

@media (max-width: 640px) {
  .follow-requests {
    padding: 1rem;
  }

  .request-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .request-actions {
    width: 100%;
  }

  .btn {
    flex: 1;
  }
}
</style>
