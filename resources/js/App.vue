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
          <div class="flex items-center space-x-4">
            <router-link 
              to="/" 
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              Sākums
            </router-link>
            <router-link 
              to="/books" 
              class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            >
              Grāmatas
            </router-link>
            
            <!-- Authentication buttons -->
            <div v-if="!authStore.isAuthenticated" class="flex items-center space-x-2">
              <button 
                @click="openLoginModal"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Pieslēgties
              </button>
              <button 
                @click="openRegisterModal"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                Reģistrēties
              </button>
            </div>
            
            <!-- User menu when authenticated -->
            <div v-else class="flex items-center space-x-4">
              <router-link 
                to="/profile" 
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Profils
              </router-link>
              <span class="text-sm text-gray-600">Sveiki, {{ authStore.user?.name || 'Lietotāj' }}!</span>
              <button 
                @click="handleLogout"
                class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
              >
                Iziet
              </button>
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
            <h3 class="text-lg font-medium text-gray-900">Pieslēgšanās</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <span class="sr-only">Aizvērt</span>
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
              <label for="login-email" class="block text-sm font-medium text-gray-700">E-pasts</label>
              <input 
                id="login-email"
                v-model="loginForm.email" 
                type="email" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="login-password" class="block text-sm font-medium text-gray-700">Parole</label>
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
                Atcelt
              </button>
              <button 
                type="submit" 
                :disabled="loginLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
              >
                {{ loginLoading ? 'Pieslēdzas...' : 'Pieslēgties' }}
              </button>
            </div>
          </form>
          
          <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">Nav konta? </span>
            <button @click="showRegisterModal = true; showLoginModal = false" class="text-sm text-blue-600 hover:text-blue-500">
              Reģistrēties šeit
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
            <h3 class="text-lg font-medium text-gray-900">Reģistrācija</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <span class="sr-only">Aizvērt</span>
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
              <label for="register-name" class="block text-sm font-medium text-gray-700">Vārds</label>
              <input 
                id="register-name"
                v-model="registerForm.name" 
                type="text" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="register-email" class="block text-sm font-medium text-gray-700">E-pasts</label>
              <input 
                id="register-email"
                v-model="registerForm.email" 
                type="email" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="register-password" class="block text-sm font-medium text-gray-700">Parole</label>
              <input 
                id="register-password"
                v-model="registerForm.password" 
                type="password" 
                required
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            
            <div>
              <label for="register-password-confirm" class="block text-sm font-medium text-gray-700">Apstiprināt paroli</label>
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
                Atcelt
              </button>
              <button 
                type="submit" 
                :disabled="registerLoading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md disabled:opacity-50"
              >
                {{ registerLoading ? 'Reģistrējas...' : 'Reģistrēties' }}
              </button>
            </div>
          </form>
          
          <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">Jau ir konts? </span>
            <button @click="showLoginModal = true; showRegisterModal = false" class="text-sm text-blue-600 hover:text-blue-500">
              Pieslēgties šeit
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
import { useAuthStore } from './stores/auth.js';

const authStore = useAuthStore();

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
  
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(registerForm.value)
    });
    
    if (response.ok) {
      const data = await response.json();
      // Auto login after successful registration
      const loginResult = await authStore.login({
        email: registerForm.value.email,
        password: registerForm.value.password
      });
      
      if (loginResult.success) {
        closeModals();
      } else {
        registerError.value = 'Reģistrācija izdevās, bet pieslēgšanās neizdevās';
      }
    } else {
      const errorData = await response.json();
      registerError.value = errorData.message || 'Reģistrācija neizdevās';
    }
  } catch (error) {
    registerError.value = 'Tīkla kļūda';
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