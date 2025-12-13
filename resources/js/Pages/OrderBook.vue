<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/services/api';

const route = useRoute();
const symbol = ref(route.params.symbol);

const orders = ref([]);
const meta = ref(null);
const loading = ref(false);
const blocking = ref(false);
const error = ref(null);
const page = ref(1);
const profile = ref({
  balance: 0,
  assets: [],
});

const fetchOrders = async () => {
  loading.value = true;
  error.value = null;
  orders.value = [];

  try {
    const response = await api.get('/orders', {
      params: {
        orderbook: true,
        symbol: symbol.value,
        status: 1,
        page: page.value,
      },
    });

    orders.value = response.data.data;
    meta.value = response.data.extra?.meta;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to fetch orderbook';
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const confirmAndPlaceOrder = async (order) => {
  const sideToPlace = order.side === 'buy' ? 'sell' : 'buy';

  const confirmed = confirm(
    `You are about to ${sideToPlace.toUpperCase()} ${order.amount} worth of ${order.symbol} at $${order.price}\n\n` +
    `Do you wish to proceed?`
  );

  if (!confirmed) return;
  blocking.value = true;

  try {
    await api.post('/orders', {
      symbol: order.symbol,
      side: sideToPlace,
      price: order.price,
      amount: order.amount,
    });

    alert('Order placed successfully');
    fetchOrders();
  } catch (err) {
    console.error(err);
    alert(err.response?.data?.message || 'Failed to place order');
  } finally {
    blocking.value = false;
  }
};

const changePage = (newPage) => {
  if (newPage < 1 || newPage > meta.value.last_page) return;
  page.value = newPage;
};
const fetchProfile = async () => {
  try {
    const { data } = await api.get('/profile');
    profile.value = data.data;
  } catch (err) {}
};

onMounted(fetchOrders);
onMounted(fetchProfile);
watch(page, fetchOrders);
watch(
  () => route.params.symbol,
  (newSymbol) => {
    symbol.value = newSymbol;
    page.value = 1;
    fetchOrders();
  }
);
</script>

<template>
  <div class="max-w-screen-xl mx-auto px-4 py-6">
    <!-- Blocking Overlay -->
    <div v-if="blocking"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg px-6 py-5 flex items-center space-x-3 shadow-lg">
        <svg
          class="animate-spin h-6 w-6 text-emerald-600"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24">
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
          />
        </svg>
        <span class="text-gray-700 font-medium">
          Processing order…
        </span>
      </div>
    </div>

    <h1 class="text-2xl font-bold mb-6">
      Orderbook — {{ symbol }}
    </h1>

    <div class="flex flex-wrap gap-2 mb-5">
        <div v-for="asset in profile.assets" 
            :key="asset.symbol" 
            class="border rounded-xl shadow-sm p-5 w-full sm:w-1/3 md:w-1/4 lg:w-1/5 transition"
            :class="asset.symbol === symbol
            ? 'bg-gray-900 border-gray-800 text-white'
            : 'bg-white border-gray-200 text-gray-800'">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold"
                :class="asset.symbol === symbol
                ? 'text-white'
                : 'text-gray-800'">
                    {{ asset.symbol }}
                </h3>
                <router-link :key="$route.fullPath" :to="{ name: 'orderbook', params: { symbol: asset.symbol } }"
                    class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                    Asset Orderbook
                </router-link>
            </div>
        </div>
    </div>

    <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
      {{ error }}
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="w-full table-auto text-sm">
        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 py-3 text-left">Buy/Sell</th>
            <th class="px-4 py-3 text-right">Price</th>
            <th class="px-4 py-3 text-right">Amount</th>
            <th class="px-4 py-3 text-right">Date</th>
            <th class="px-4 py-3">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="order in orders"
            :key="order.id"
            class="border-b hover:bg-gray-50">
            <td class="px-4 py-3 font-semibold"
              :class="order.side === 'buy' ? 'text-green-600' : 'text-red-600'">
              {{ order.side.toUpperCase() }}
            </td>
            <td class="px-4 py-3 text-right">
              ${{ Number(order.price).toLocaleString() }}
            </td>
            <td class="px-4 py-3 text-right">
              {{ order.amount }}
            </td>
            <td class="px-4 py-3 text-right text-gray-500">
              {{ new Date(order.created_at).toLocaleString() }}
            </td>

            <td class="px-4 py-3 text-center">
              <button class="px-3 py-1 rounded text-white text-xs"
                :class="order.side === 'buy'
                  ? 'bg-red-600 hover:bg-red-700'
                  : 'bg-green-600 hover:bg-green-700'"
                @click="confirmAndPlaceOrder(order)">
                {{ order.side === 'buy' ? 'Sell' : 'Buy' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="loading" class="p-4 text-center text-gray-500">
        Loading orderbook...
      </div>

      <div v-if="orders.length === 0 && !loading" class="p-4 text-center text-gray-500">
        No open orders available.
      </div>
    </div>

    <div v-if="meta"
      class="flex justify-between items-center mt-6">
      <span class="text-sm text-gray-600">
        Page {{ meta.current_page }} of {{ meta.last_page }}
      </span>

      <div class="space-x-2">
        <button
          class="px-3 py-1 border rounded disabled:opacity-50"
          :disabled="meta.current_page === 1"
          @click="changePage(meta.current_page - 1)">
          Prev
        </button>
        <button
          class="px-3 py-1 border rounded disabled:opacity-50"
          :disabled="meta.current_page === meta.last_page"
          @click="changePage(meta.current_page + 1)">
          Next
        </button>
      </div>
    </div>
  </div>
</template>
