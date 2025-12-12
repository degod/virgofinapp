<script setup>
import { ref } from 'vue';
import api from '../services/api';
import Input from '../Components/Input.vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const route = useRoute();
const auth = useAuthStore();

const form = ref({
  email: '',
  password: '',
});

let message = ref('');
const messageColor = ref('');
const loading = ref(false);

const loginUser = async () => {
  loading.value = true;
  message = null;
  messageColor.value = '';

  try {
    const response = await api.post('/auth/login', form.value);

    const user = response.data.data;
    const token = user.token;
    auth.setAuth({ user, token });

    form.value = { email: '', password: '' };
    
    const redirectPath = route.query.redirect || { name: 'home' };
    router.push(redirectPath);
  } catch (error) {
    message = error?.response?.data?.errors;
    console.error(error);
  } finally {
    loading.value = false;
  }
};
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white shadow-lg rounded-2xl p-8 w-full max-w-md">
      <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">Login to your account</h1>

      <form @submit.prevent="loginUser">
        <Input
          v-model="form.email"
          label="Email"
          type="email"
          :message="message?.email || ''"
        />
        <Input
          v-model="form.password"
          label="Password"
          type="password"
          :message="message?.password || ''"
        />

        <button
          type="submit"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none transition"
          :disabled="loading"
        >
          {{ loading ? "Logging into your account..." : "Login" }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
body {
  font-family: 'Inter', sans-serif;
}
</style>
