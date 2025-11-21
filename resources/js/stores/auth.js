import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null);
  const isAuthenticated = ref(false);
  const token = ref(localStorage.getItem('auth_token') || null);

  const login = async (credentials) => {
    try {
      const response = await fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(credentials)
      });

      if (response.ok) {
        const data = await response.json();
        user.value = data.user;
        token.value = data.token;
        isAuthenticated.value = true;
        localStorage.setItem('auth_token', data.token);
        return { success: true };
      } else {
        const errorData = await response.json();
        let errorMessage = errorData.message || 'Invalid credentials';
        
        // Handle validation errors
        if (errorData.errors) {
          const errors = Object.values(errorData.errors).flat();
          errorMessage = errors.join(', ');
        }
        
        return { success: false, message: errorMessage };
      }
    } catch (error) {
      return { success: false, message: 'Network error' };
    }
  };

  const register = async (userData) => {
    try {
      const response = await fetch('/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(userData)
      });

      if (response.ok) {
        const data = await response.json();
        user.value = data.user;
        token.value = data.token;
        isAuthenticated.value = true;
        localStorage.setItem('auth_token', data.token);
        return { success: true };
      } else {
        const errorData = await response.json();
        let errorMessage = errorData.message || 'Registration failed';
        
        // Handle validation errors
        if (errorData.errors) {
          const errors = Object.values(errorData.errors).flat();
          errorMessage = errors.join(', ');
        }
        
        return { success: false, message: errorMessage };
      }
    } catch (error) {
      return { success: false, message: 'Network error' };
    }
  };

  const logout = () => {
    user.value = null;
    token.value = null;
    isAuthenticated.value = false;
    localStorage.removeItem('auth_token');
  };

  const checkAuth = async () => {
    if (token.value) {
      try {
        const response = await fetch('/api/user', {
          headers: {
            'Authorization': `Bearer ${token.value}`,
            'Accept': 'application/json',
          },
        });

        if (response.ok) {
          const userData = await response.json();
          user.value = userData;
          isAuthenticated.value = true;
        } else {
          logout();
        }
      } catch (error) {
        logout();
      }
    }
  };

  return {
    user,
    isAuthenticated,
    token,
    login,
    register,
    logout,
    checkAuth
  };
});