<script setup>
import { ref, onMounted, watch } from 'vue';
import api from '@/services/api';

const orders = ref([]);
const loading = ref(false);
const error = ref(null);

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
});

const fetchOrders = async (page = 1) => {
  loading.value = true;
  error.value = null;

  try {
    const { data } = await api.get('/orders', {
      params: { page },
    });

    orders.value = data.data;
    pagination.value = data.extra.meta;
  } catch (err) {
    error.value = 'Failed to load orders.';
  } finally {
    loading.value = false;
  }
};

const statusLabel = (status) => {
  return {
    1: 'Open',
    2: 'Filled',
    3: 'Cancelled',
  }[status] || 'Unknown';
};

const statusClasses = (status) => {
  return {
    1: 'bg-blue-100 text-blue-700',
    2: 'bg-green-100 text-green-700',
    3: 'bg-red-100 text-red-700',
  }[status];
};

const sideClasses = (side) =>
  side === 'buy'
    ? 'text-emerald-600 font-semibold'
    : 'text-red-600 font-semibold';

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchOrders(page);
  }
};

onMounted(() => fetchOrders());
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h2 class="text-xl font-bold text-gray-800 mb-5">
      Order History
    </h2>

    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
      {{ error }}
    </div>

    <div v-if="!loading" class="overflow-x-auto">
      <table class="w-full text-sm text-left">
        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 py-3">Date</th>
            <th class="px-4 py-3">Symbol</th>
            <th class="px-4 py-3">Side</th>
            <th class="px-4 py-3">Price</th>
            <th class="px-4 py-3">Amount</th>
            <th class="px-4 py-3">Status</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="order in orders"
            :key="order.id"
            class="border-t hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-500">
              {{ new Date(order.created_at).toLocaleString() }}
            </td>

            <td class="px-4 py-3 font-semibold">
              {{ order.symbol }}
            </td>

            <td class="px-4 py-3">
              <span :class="sideClasses(order.side)">
                {{ order.side.toUpperCase() }}
              </span>
            </td>

            <td class="px-4 py-3">
              ${{ Number(order.price).toLocaleString() }}
            </td>

            <td class="px-4 py-3">
              {{ order.amount }}
            </td>

            <td class="px-4 py-3">
              <span class="px-3 py-1 rounded-full text-xs font-medium"
                :class="statusClasses(order.status)">
                {{ statusLabel(order.status) }}
              </span>
            </td>
          </tr>

          <tr v-if="orders.length === 0">
            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
              No orders found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="loading" class="text-gray-500 py-6 text-center">
      Loading orders...
    </div>

    <div v-if="pagination.last_page > 1"
      class="flex justify-between items-center mt-6">
      <p class="text-sm text-gray-500">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </p>

      <div class="flex space-x-2">
        <button class="px-4 py-2 border rounded-lg text-sm"
          :disabled="pagination.current_page === 1"
          @click="changePage(pagination.current_page - 1)">
          Prev
        </button>

        <button class="px-4 py-2 border rounded-lg text-sm"
          :disabled="pagination.current_page === pagination.last_page"
          @click="changePage(pagination.current_page + 1)">
          Next
        </button>
      </div>
    </div>
  </div>
</template>
