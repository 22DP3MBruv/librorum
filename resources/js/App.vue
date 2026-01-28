<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <router-link to="/" class="text-xl font-bold text-gray-900">
              Librorum
            </router-link>
          </div>
          
          <!-- Mobile menu button -->
          <div class="flex items-center md:hidden">
            <button
              @click="mobileMenuOpen = !mobileMenuOpen"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <!-- Desktop navigation -->
          <div class="hidden md:flex items-center space-x-4">
            <router-link 
              to="/books" 
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              {{ t('nav.books') }}
            </router-link>
            <router-link 
              v-if="authStore.isAuthenticated"
              to="/my-reading" 
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              {{ t('nav.myReading') }}
            </router-link>
            
            
            <!-- Authentication buttons -->
            <div v-if="!authStore.isAuthenticated" class="flex items-center space-x-2">
              <button 
                @click="openLoginModal"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                {{ t('nav.login') }}
              </button>
              <button 
                @click="openRegisterModal"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                {{ t('nav.register') }}
              </button>
            </div>
            
            <!-- User menu when authenticated -->
            <div v-else class="flex items-center space-x-4">
              <router-link 
                to="/profile" 
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                {{ t('nav.profile') }}
              </router-link>
              <router-link 
                v-if="authStore.user && (authStore.user.role === 'admin' || authStore.user.role === 'moderator')"
                to="/admin" 
                class="text-blue-600 hover:text-blue-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                {{ t('nav.admin') }}
              </router-link>
              <button 
                @click="handleLogout"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                {{ t('nav.logout') }}
              </button>
            </div>
            <!-- Language Switcher -->
            <div class="flex items-center space-x-1 border-l pl-4 ml-2">
              <button
                @click="setLocale('lv')"
                :class="[
                  'px-2 py-1 text-sm font-medium rounded transition-colors',
                  locale === 'lv' 
                    ? 'bg-blue-600 text-white' 
                    : 'text-gray-600 hover:bg-gray-100'
                ]"
              >
                LV
              </button>
              <button
                @click="setLocale('en')"
                :class="[
                  'px-2 py-1 text-sm font-medium rounded transition-colors',
                  locale === 'en' 
                    ? 'bg-blue-600 text-white' 
                    : 'text-gray-600 hover:bg-gray-100'
                ]"
              >
                EN
              </button>
            </div>
          </div>
        </div>
        
        <!-- Mobile menu -->
        <div v-if="mobileMenuOpen" class="md:hidden border-t border-gray-200 pb-3 pt-3">
          <div class="space-y-1">
            <router-link 
              to="/" 
              @click="mobileMenuOpen = false"
              class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium"
            >
              {{ t('nav.home') }}
            </router-link>

            <router-link 
              to="/books"
              @click="mobileMenuOpen = false"
              class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium"
            >
              {{ t('nav.books') }}
            </router-link>
            <router-link 
              v-if="authStore.isAuthenticated"
              to="/my-reading"
              @click="mobileMenuOpen = false"
              class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium"
            >
              {{ t('nav.myReading') }}
            </router-link>
            
            <!-- Auth buttons mobile -->
            <div v-if="!authStore.isAuthenticated" class="space-y-1 px-3 py-2">
              <button 
                @click="openLoginModal(); mobileMenuOpen = false"
                class="w-full text-left text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium"
              >
                {{ t('nav.login') }}
              </button>
              <button 
                @click="openRegisterModal(); mobileMenuOpen = false"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-md text-base font-medium"
              >
                {{ t('nav.register') }}
              </button>
            </div>
            
            <!-- User menu mobile -->
            <div v-else class="space-y-1">
              <router-link 
                to="/profile"
                @click="mobileMenuOpen = false"
                class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium"
              >
                {{ t('nav.profile') }}
              </router-link>
              <router-link 
                v-if="authStore.user && (authStore.user.role === 'admin' || authStore.user.role === 'moderator')"
                to="/admin"
                @click="mobileMenuOpen = false"
                class="block text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium"
              >
                {{ t('nav.admin') }}
              </router-link>
              <button 
                @click="handleLogout(); mobileMenuOpen = false"
                class="w-full text-left text-gray-600 hover:text-gray-900 hover:bg-gray-50 px-3 py-2 rounded-md text-base font-medium"
              >
                {{ t('nav.logout') }}
              </button>
            </div>
            
            <!-- Language switcher mobile -->
            <div class="border-t border-gray-200 pt-3 mt-3">
              <div class="px-3 py-2 text-xs font-medium text-gray-500 uppercase tracking-wider">
                Language / Valoda
              </div>
              <div class="flex items-center space-x-2 px-3 py-2">
                <button
                  @click="setLocale('lv')"
                  :class="[
                    'flex-1 px-3 py-2 text-sm font-medium rounded transition-colors',
                    locale === 'lv' 
                      ? 'bg-blue-600 text-white' 
                      : 'text-gray-600 hover:bg-gray-100 border border-gray-300'
                  ]"
                >
                  Latviešu
                </button>
                <button
                  @click="setLocale('en')"
                  :class="[
                    'flex-1 px-3 py-2 text-sm font-medium rounded transition-colors',
                    locale === 'en' 
                      ? 'bg-blue-600 text-white' 
                      : 'text-gray-600 hover:bg-gray-100 border border-gray-300'
                  ]"
                >
                  English
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <router-view />
    </main>

    <!-- Login Modal -->
    <div v-if="showLoginModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ t('auth.login') }}</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <span class="sr-only">{{ t('common.close') }}</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="handleLogin" class="space-y-4">
            <div v-if="loginError" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
              {{ loginError }}
            </div>
            
            <div>
              <label for="login-email" class="block text-sm font-medium text-gray-700">{{ t('auth.email') }}</label>
              <input 
                id="login-email"
                v-model="loginForm.email" 
                type="email" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="login-password" class="block text-sm font-medium text-gray-700">{{ t('auth.password') }}</label>
              <input 
                id="login-password"
                v-model="loginForm.password" 
                type="password" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
              <button 
                type="button" 
                @click="closeModals"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
              >
                {{ t('auth.cancel') }}
              </button>
              <button 
                type="submit" 
                :disabled="loginLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
              >
                {{ loginLoading ? t('auth.loggingIn') : t('nav.login') }}
              </button>
            </div>
          </form>
          
          <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">{{ t('auth.noAccount') }} </span>
            <button @click="showRegisterModal = true; showLoginModal = false" class="text-sm text-blue-600 hover:text-blue-500">
              {{ t('auth.registerHere') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Register Modal -->
    <div v-if="showRegisterModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ t('auth.register') }}</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <span class="sr-only">{{ t('common.close') }}</span>
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="handleRegister" class="space-y-4">
            <div v-if="registerError" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
              {{ registerError }}
            </div>
            
            <div>
              <label for="register-name" class="block text-sm font-medium text-gray-700">{{ t('auth.name') }}</label>
              <input 
                id="register-name"
                v-model="registerForm.name" 
                type="text" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="register-email" class="block text-sm font-medium text-gray-700">{{ t('auth.email') }}</label>
              <input 
                id="register-email"
                v-model="registerForm.email" 
                type="email" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="register-password" class="block text-sm font-medium text-gray-700">{{ t('auth.password') }}</label>
              <input 
                id="register-password"
                v-model="registerForm.password" 
                type="password" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="register-password-confirm" class="block text-sm font-medium text-gray-700">{{ t('auth.confirmPassword') }}</label>
              <input 
                id="register-password-confirm"
                v-model="registerForm.password_confirmation" 
                type="password" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
              <button 
                type="button" 
                @click="closeModals"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md"
              >
                {{ t('auth.cancel') }}
              </button>
              <button 
                type="submit" 
                :disabled="registerLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
              >
                {{ registerLoading ? t('auth.registering') : t('nav.register') }}
              </button>
            </div>
          </form>
          
          <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">{{ t('auth.haveAccount') }} </span>
            <button @click="showLoginModal = true; showRegisterModal = false" class="text-sm text-blue-600 hover:text-blue-500">
              {{ t('auth.loginHere') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-600">
          © 2025 Librorum - Marks Gerhards Brūveris
        </p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from './stores/auth.js';

const authStore = useAuthStore();
const { t, locale } = useI18n();

// Mobile menu state
const mobileMenuOpen = ref(false);

// Language switching
const setLocale = (lang) => {
  locale.value = lang;
  localStorage.setItem('locale', lang);
};

// Modal states
const showLoginModal = ref(false);
const showRegisterModal = ref(false);

// Form data
const loginForm = ref({
  email: '',
  password: ''
});

const registerForm = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
});

// Form states
const loginLoading = ref(false);
const registerLoading = ref(false);
const loginError = ref('');
const registerError = ref('');

// Methods
const openLoginModal = () => {
  showLoginModal.value = true;
  loginError.value = '';
  loginForm.value = { email: '', password: '' };
};

const openRegisterModal = () => {
  showRegisterModal.value = true;
  registerError.value = '';
  registerForm.value = { name: '', email: '', password: '', password_confirmation: '' };
};

const closeModals = () => {
  showLoginModal.value = false;
  showRegisterModal.value = false;
};

const handleLogin = async () => {
  loginLoading.value = true;
  loginError.value = '';
  
  const result = await authStore.login(loginForm.value);
  
  if (result.success) {
    closeModals();
  } else {
    loginError.value = result.message || 'Pieslēgšanās neizdevās';
  }
  
  loginLoading.value = false;
};

const handleRegister = async () => {
  registerLoading.value = true;
  registerError.value = '';
  
  if (registerForm.value.password !== registerForm.value.password_confirmation) {
    registerError.value = 'Paroles nesakrīt';
    registerLoading.value = false;
    return;
  }
  
  const result = await authStore.register(registerForm.value);
  
  if (result.success) {
    closeModals();
  } else {
    registerError.value = result.message || 'Reģistrācija neizdevās';
  }
  
  registerLoading.value = false;
};

const handleLogout = () => {
  authStore.logout();
};

// Check authentication on mount
onMounted(() => {
  authStore.checkAuth();
});
</script>

<style scoped>
/* Component-specific styles */
</style>