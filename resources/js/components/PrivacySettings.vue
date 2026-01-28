<template>
  <div class="privacy-settings">
    <h2>{{ $t('privacySettings.title') }}</h2>
    
    <div v-if="loading" class="loading">
      {{ $t('common.loading') }}
    </div>
    
    <div v-else-if="error" class="error">
      {{ error }}
    </div>
    
    <form v-else @submit.prevent="saveSettings" class="privacy-form">
      <!-- Profile Visibility -->
      <div class="form-group">
        <label for="profile_visibility">{{ $t('privacySettings.profileVisibility') }}</label>
        <select 
          id="profile_visibility" 
          v-model="settings.profile_visibility"
          class="form-control"
        >
          <option value="public">{{ $t('privacySettings.public') }}</option>
          <option value="followers">{{ $t('privacySettings.followersOnly') }}</option>
          <option value="private">{{ $t('privacySettings.private') }}</option>
        </select>
        <small class="form-text">{{ $t('privacySettings.profileVisibilityHelp') }}</small>
      </div>

      <!-- Reading Progress Visibility -->
      <div class="form-group">
        <label for="reading_progress_visibility">{{ $t('privacySettings.readingProgressVisibility') }}</label>
        <select 
          id="reading_progress_visibility" 
          v-model="settings.reading_progress_visibility"
          class="form-control"
        >
          <option value="public">{{ $t('privacySettings.public') }}</option>
          <option value="followers">{{ $t('privacySettings.followersOnly') }}</option>
          <option value="private">{{ $t('privacySettings.private') }}</option>
        </select>
        <small class="form-text">{{ $t('privacySettings.readingProgressVisibilityHelp') }}</small>
      </div>

      <!-- Activity Visibility -->
      <div class="form-group">
        <label for="activity_visibility">{{ $t('privacySettings.activityVisibility') }}</label>
        <select 
          id="activity_visibility" 
          v-model="settings.activity_visibility"
          class="form-control"
        >
          <option value="public">{{ $t('privacySettings.public') }}</option>
          <option value="followers">{{ $t('privacySettings.followersOnly') }}</option>
          <option value="private">{{ $t('privacySettings.private') }}</option>
        </select>
        <small class="form-text">{{ $t('privacySettings.activityVisibilityHelp') }}</small>
      </div>

      <!-- Allow Follows -->
      <div class="form-group">
        <div class="form-check">
          <input 
            id="allow_follows" 
            type="checkbox" 
            v-model="settings.allow_follows"
            class="form-check-input"
          >
          <label class="form-check-label" for="allow_follows">
            {{ $t('privacySettings.allowFollows') }}
          </label>
        </div>
        <small class="form-text">{{ $t('privacySettings.allowFollowsHelp') }}</small>
      </div>

      <!-- Require Follow Approval -->
      <div class="form-group" v-if="settings.allow_follows">
        <div class="form-check">
          <input 
            id="require_follow_approval" 
            type="checkbox" 
            v-model="settings.require_follow_approval"
            class="form-check-input"
          >
          <label class="form-check-label" for="require_follow_approval">
            {{ $t('privacySettings.requireFollowApproval') }}
          </label>
        </div>
        <small class="form-text">{{ $t('privacySettings.requireFollowApprovalHelp') }}</small>
      </div>

      <!-- Submit Button -->
      <div class="form-actions">
        <button 
          type="submit" 
          class="btn btn-primary" 
          :disabled="saving"
        >
          {{ saving ? $t('common.saving') : $t('common.save') }}
        </button>
      </div>

      <!-- Success Message -->
      <div v-if="successMessage" class="success-message">
        {{ successMessage }}
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const authStore = useAuthStore();

const settings = ref({
  profile_visibility: 'public',
  reading_progress_visibility: 'public',
  activity_visibility: 'public',
  allow_follows: true,
  require_follow_approval: false
});

const loading = ref(true);
const saving = ref(false);
const error = ref(null);
const successMessage = ref(null);

const fetchSettings = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await fetch('/api/user/privacy', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (response.ok) {
      const data = await response.json();
      settings.value = data.data;
    } else {
      error.value = t('privacySettings.fetchError');
    }
  } catch (err) {
    error.value = t('common.networkError');
  } finally {
    loading.value = false;
  }
};

const saveSettings = async () => {
  try {
    saving.value = true;
    error.value = null;
    successMessage.value = null;
    
    const response = await fetch('/api/user/privacy', {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(settings.value)
    });

    if (response.ok) {
      const data = await response.json();
      settings.value = data.data;
      successMessage.value = t('privacySettings.saveSuccess');
      
      // Redirect to profile after 2 seconds
      setTimeout(() => {
        router.push({ name: 'Profile', params: { username: authStore.user.username } });
      }, 2000);
    } else {
      const errorData = await response.json();
      error.value = errorData.message || t('privacySettings.saveError');
    }
  } catch (err) {
    error.value = t('common.networkError');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>

<style scoped>
.privacy-settings {
  max-width: 600px;
  margin: 0 auto;
  padding: 2rem;
}

.privacy-settings h2 {
  margin-bottom: 2rem;
  color: #333;
}

.privacy-form {
  background: #fff;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 1rem;
}

.form-control:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-text {
  display: block;
  margin-top: 0.25rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.form-check {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
}

.form-check-input {
  margin-right: 0.5rem;
  width: 1.25rem;
  height: 1.25rem;
  cursor: pointer;
}

.form-check-label {
  cursor: pointer;
  font-weight: 600;
  color: #333;
  margin-bottom: 0;
}

.form-actions {
  margin-top: 2rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-primary {
  background-color: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0056b3;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading,
.error,
.success-message {
  padding: 1rem;
  border-radius: 4px;
  margin-bottom: 1rem;
  text-align: center;
}

.loading {
  background-color: #e7f3ff;
  color: #004085;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.success-message {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
  margin-top: 1rem;
}
</style>
