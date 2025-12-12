import { defineStore } from 'pinia'
import api from '../services/api'
import router from '../router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setAuth({ user, token }) {
      this.user = user;
      this.token = token;

      localStorage.setItem('auth_token', token);
      localStorage.setItem('auth_user', JSON.stringify(user));
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },

    loadToken() {
      const token = localStorage.getItem('auth_token');
      const user = localStorage.getItem('auth_user');
      if (token) {
        this.token = token;
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      }
      if (user) this.user = JSON.parse(user);
    },

    logout() {
      this.user = null;
      this.token = null;
      localStorage.removeItem('auth_token');
      delete api.defaults.headers.common['Authorization'];
      router.push({ name: 'login' });
    },
  },
});