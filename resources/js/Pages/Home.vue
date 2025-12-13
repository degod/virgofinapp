<script setup>
import WalletOverview from '../Components/WalletOverview.vue';
import { useAuthStore } from '../stores/auth';
import _ from 'lodash';

const auth = useAuthStore();
</script>

<template>
  <div class="min-h-screen bg-gray-100 px-4 py-6 md:px-8">
    <div class="max-w-screen-xl mx-auto">
      <div v-if="auth">
        <h1 class="text-2xl font-bold mb-5">Welcome, {{ (auth.user && auth.user.name) ? auth.user.name: "Guest" }}!</h1>
      </div>

      <WalletOverview v-if="auth.isAuthenticated"/>
    </div>
  </div>
</template>

<style scoped>
input {
  transition: all 0.2s ease;
}
input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
</style>
