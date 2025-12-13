<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import api from '@/services/api';
import Input from '../Components/Input.vue';
import Select from '../Components/Select.vue';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  symbol: '',
  side: '',
  price: '',
  amount: '',
});

let message = ref('');
const loading = ref(false);
const error = ref(null);
const success = ref(false);
const symbolOptions = ref([]);
const sideOptions = ref([]);

const fetchSymbols = async () => {
  const { data } = await api.get('/orders/symbols');

  symbolOptions.value = data.data;

  const selected = symbolOptions.value.find(o => o.selected);
  if (selected) {
    form.value.symbol = selected.value;
  }
};
const fetchSides = async () => {
  const { data } = await api.get('/orders/sides');

  sideOptions.value = data.data;

  const selected = sideOptions.value.find(o => o.selected);
  if (selected) {
    form.value.side = selected.value;
  }
};

const submitOrder = async () => {
  const formData = new FormData();
  formData.append('symbol', form.value.symbol);
  formData.append('side', form.value.side);
  formData.append('price', form.value.price);
  formData.append('amount', form.value.amount);

  try {
    loading.value = true;
    error.value = null;
    message = null;

    await api.post('/orders', formData);
    success.value = true;
    setTimeout(() => {
      router.push({ name: 'home' });
    }, 1500);
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to create order.';
    message = error?.response?.data?.errors;
    console.error(error);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login' });
  }

  await Promise.all([
    fetchSymbols(),
    fetchSides(),
  ]);
});
</script>

<template>
  <div class="max-w-screen-xl mx-auto px-4 py-6 md:px-6 md:py-8">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Place New Order</h1>
    </div>

    <div v-if="success" class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
      Order placed successfully! Redirecting...
    </div>

    <div v-if="error" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
      {{ error }}
    </div>

    <form @submit.prevent="submitOrder" class="space-y-6">
        <Select
        v-model="form.symbol"
        label="Asset Symbol"
        is_required="true"
        :options="symbolOptions"
        :message="message?.symbol || ''"
        />

        <Select
        v-model="form.side"
        label="Buy/Sell (Side)"
        is_required="true"
        :options="sideOptions"
        :message="message?.side || ''"
        />
        
        <Input
        v-model="form.price"
        label="Price"
        type="number"
        is_required="true"
        placeholder="950 (price in USD)"
        :message="message?.price || ''"
        />
        
        <Input
        v-model="form.amount"
        label="Amount"
        type="text"
        is_required="true"
        placeholder="0.0132 (BTC/BNB/USDT value)"
        :message="message?.amount || ''"
        />
        
        <div class="flex justify-end space-x-3">
          <button type="button"
            @click="router.push({ name: 'home' })"
            class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancel
          </button>
          <button type="submit" :disabled="loading"
            class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition disabled:opacity-50">
            <span v-if="loading">Placing order...</span>
            <span v-else>Place Order</span>
          </button>
        </div>
    </form>
  </div>
</template>

<style scoped>
input,
textarea,
button {
  transition: all 0.2s ease;
}
input:focus,
textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}
</style>