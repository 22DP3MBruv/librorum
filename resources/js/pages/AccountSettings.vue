<template>
  <div class="container mx-auto px-4 sm:px-6 py-6 max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ t('accountSettings.title') }}</h1>

    <!-- Change Username Section -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-900">{{ t('accountSettings.changeUsername') }}</h2>
      </div>
      <div class="p-6">
        <form @submit.prevent="updateUsername" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('accountSettings.currentUsername') }}
            </label>
            <input
              type="text"
              :value="authStore.user?.username"
              disabled
              class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('accountSettings.newUsername') }}
            </label>
            <input
              v-model="newUsername"
              type="text"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="t('accountSettings.newUsername')"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('accountSettings.passwordForConfirmation') }}
            </label>
            <input
              v-model="usernamePassword"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="t('accountSettings.currentPassword')"
            />
            <p v-if="usernameError" class="mt-1 text-sm text-red-600">{{ usernameError }}</p>
          </div>
          <button
            type="submit"
            :disabled="updatingUsername || !newUsername || !usernamePassword"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ updatingUsername ? t('accountSettings.updating') : t('accountSettings.updateUsername') }}
          </button>
        </form>
      </div>
    </div>

    <!-- Change Password Section -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-900">{{ t('accountSettings.changePassword') }}</h2>
      </div>
      <div class="p-6">
        <form @submit.prevent="updatePassword" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('accountSettings.currentPassword') }}
            </label>
            <input
              v-model="currentPassword"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="t('accountSettings.currentPassword')"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('accountSettings.newPassword') }}
            </label>
            <input
              v-model="newPassword"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="t('accountSettings.newPassword')"
            />
            <p class="mt-1 text-xs text-gray-500">{{ t('accountSettings.newPasswordRequirements') }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ t('accountSettings.confirmNewPassword') }}
            </label>
            <input
              v-model="confirmPassword"
              type="password"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :placeholder="t('accountSettings.confirmNewPassword')"
            />
          </div>
          <p v-if="passwordError" class="text-sm text-red-600">{{ passwordError }}</p>
          <button
            type="submit"
            :disabled="updatingPassword || !currentPassword || !newPassword || !confirmPassword"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ updatingPassword ? t('accountSettings.updating') : t('accountSettings.updatePassword') }}
          </button>
        </form>
      </div>
    </div>

    <!-- Delete Content Section -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-900">{{ t('accountSettings.deleteContent') }}</h2>
      </div>
      <div class="p-6">
        <p class="text-sm text-gray-600 mb-4">{{ t('accountSettings.deleteContentDescription') }}</p>
        <button
          @click="showDeleteContentModal = true"
          class="px-6 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700"
        >
          {{ t('accountSettings.deleteContentButton') }}
        </button>
      </div>
    </div>

    <!-- Delete Account Section -->
    <div class="bg-white rounded-lg shadow-sm border mb-6">
      <div class="px-6 py-4 border-b">
        <h2 class="text-xl font-semibold text-gray-900">{{ t('accountSettings.deleteAccount') }}</h2>
      </div>
      <div class="p-6">
        <p class="text-sm text-gray-600 mb-4">{{ t('accountSettings.deleteAccountDescription') }}</p>
        <button
          @click="showDeleteAccountModal = true"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          {{ t('accountSettings.deleteAccountButton') }}
        </button>
      </div>
    </div>

    <!-- Delete Content Modal -->
    <div v-if="showDeleteContentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ t('accountSettings.deleteContent') }}</h3>
        <p class="text-gray-600 mb-4">{{ t('accountSettings.deleteContentDescription') }}</p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ t('accountSettings.passwordForConfirmation') }}
          </label>
          <input
            v-model="deleteContentPassword"
            type="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            :placeholder="t('accountSettings.currentPassword')"
          />
          <p v-if="deleteContentError" class="mt-1 text-sm text-red-600">{{ deleteContentError }}</p>
        </div>
        <div class="flex gap-3">
          <button
            @click="deleteUserContent"
            :disabled="!deleteContentPassword || deletingContent"
            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 disabled:opacity-50"
          >
            {{ deletingContent ? t('accountSettings.deleting') : t('accountSettings.deleteContentButton') }}
          </button>
          <button
            @click="showDeleteContentModal = false; deleteContentPassword = ''; deleteContentError = ''"
            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
          >
            {{ t('common.cancel') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Account Modal -->
    <div v-if="showDeleteAccountModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-semibold text-red-900 mb-4">{{ t('accountSettings.deleteAccount') }}</h3>
        <p class="text-gray-600 mb-4">{{ t('accountSettings.deleteAccountDescription') }}</p>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ t('accountSettings.typeDeleteToConfirm') }}
          </label>
          <input
            v-model="deleteAccountConfirmation"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            placeholder="DELETE"
          />
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            {{ t('accountSettings.passwordForConfirmation') }}
          </label>
          <input
            v-model="deleteAccountPassword"
            type="password"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
            :placeholder="t('accountSettings.currentPassword')"
          />
          <p v-if="deleteAccountError" class="mt-1 text-sm text-red-600">{{ deleteAccountError }}</p>
        </div>
        <div class="flex gap-3">
          <button
            @click="deleteAccount"
            :disabled="deleteAccountConfirmation !== 'DELETE' || !deleteAccountPassword || deletingAccount"
            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
          >
            {{ deletingAccount ? t('accountSettings.deleting') : t('accountSettings.deleteAccountButton') }}
          </button>
          <button
            @click="showDeleteAccountModal = false; deleteAccountConfirmation = ''; deleteAccountPassword = ''; deleteAccountError = ''"
            class="flex-1 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
          >
            {{ t('common.cancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth.js';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();

// Username change
const newUsername = ref('');const usernamePassword = ref('');const usernameError = ref('');
const updatingUsername = ref(false);

// Password change
const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const passwordError = ref('');
const updatingPassword = ref(false);

// Delete content
const showDeleteContentModal = ref(false);
const deleteContentPassword = ref('');
const deleteContentError = ref('');
const deletingContent = ref(false);

// Delete account
const showDeleteAccountModal = ref(false);
const deleteAccountConfirmation = ref('');
const deleteAccountPassword = ref('');
const deleteAccountError = ref('');
const deletingAccount = ref(false);

const updateUsername = async () => {
  usernameError.value = '';
  updatingUsername.value = true;

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user/account/username', {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ 
        username: newUsername.value,
        password: usernamePassword.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      // Update auth store
      authStore.user.username = newUsername.value;
      newUsername.value = '';
      usernamePassword.value = '';
      alert(t('accountSettings.usernameUpdated'));
    } else {
      usernameError.value = data.errors?.username?.[0] || 
                           data.errors?.password?.[0] || 
                           data.message || 
                           t('accountSettings.usernameUpdateError');
    }
  } catch (err) {
    console.error('Failed to update username:', err);
    usernameError.value = t('accountSettings.usernameUpdateError');
  } finally {
    updatingUsername.value = false;
  }
};

const updatePassword = async () => {
  passwordError.value = '';

  // Validate passwords match
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = t('accountSettings.passwordsDoNotMatch');
    return;
  }

  updatingPassword.value = true;

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user/account/password', {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        current_password: currentPassword.value,
        new_password: newPassword.value,
        new_password_confirmation: confirmPassword.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      alert(t('accountSettings.passwordUpdated'));
      // Clear form
      currentPassword.value = '';
      newPassword.value = '';
      confirmPassword.value = '';
      // Logout and redirect to login
      await authStore.logout();
      router.push('/');
    } else {
      passwordError.value = data.errors?.current_password?.[0] || 
                           data.errors?.new_password?.[0] || 
                           data.message || 
                           t('accountSettings.passwordUpdateError');
    }
  } catch (err) {
    console.error('Failed to update password:', err);
    passwordError.value = t('accountSettings.passwordUpdateError');
  } finally {
    updatingPassword.value = false;
  }
};

const deleteUserContent = async () => {
  deleteContentError.value = '';
  deletingContent.value = true;

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user/account/content', {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({ password: deleteContentPassword.value })
    });

    const data = await response.json();

    if (response.ok) {
      showDeleteContentModal.value = false;
      deleteContentPassword.value = '';
      const threadsMsg = t('accountSettings.threadsDeleted', { count: data.data.threads_deleted });
      const commentsMsg = t('accountSettings.commentsDeleted', { count: data.data.comments_deleted });
      alert(`${t('accountSettings.contentDeleted')}\n${threadsMsg}\n${commentsMsg}`);
    } else {
      deleteContentError.value = data.errors?.password?.[0] || data.message || t('accountSettings.contentDeleteError');
    }
  } catch (err) {
    console.error('Failed to delete content:', err);
    deleteContentError.value = t('accountSettings.contentDeleteError');
  } finally {
    deletingContent.value = false;
  }
};

const deleteAccount = async () => {
  deleteAccountError.value = '';

  if (deleteAccountConfirmation.value !== 'DELETE') {
    deleteAccountError.value = t('accountSettings.confirmationInvalid');
    return;
  }

  deletingAccount.value = true;

  try {
    const token = localStorage.getItem('auth_token');
    const response = await fetch('/api/user/account', {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        password: deleteAccountPassword.value,
        confirmation: deleteAccountConfirmation.value
      })
    });

    const data = await response.json();

    if (response.ok) {
      alert(t('accountSettings.accountDeleted'));
      // Logout and redirect to home
      await authStore.logout();
      router.push('/');
    } else {
      deleteAccountError.value = data.errors?.password?.[0] || 
                                 data.errors?.confirmation?.[0] || 
                                 data.message || 
                                 t('accountSettings.accountDeleteError');
    }
  } catch (err) {
    console.error('Failed to delete account:', err);
    deleteAccountError.value = t('accountSettings.accountDeleteError');
  } finally {
    deletingAccount.value = false;
  }
};
</script>
