<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '@/services/api';

const loading = ref(true);
const error = ref(null);
const profile = ref({
  balance: 0,
  assets: [],
});

const fetchProfile = async () => {
  try {
    loading.value = true;
    const { data } = await api.get('/profile');
    profile.value = data.data;
  } catch (err) {
    error.value = 'Failed to load wallet balances.';
  } finally {
    loading.value = false;
  }
};

const totalAsset = (asset) =>
  Number(asset.amount) + Number(asset.locked_amount);

onMounted(fetchProfile);
</script>

<template>
  <div class="w-full mb-8">
    <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
      {{ error }}
    </div>

    <div v-if="loading" class="text-gray-500">Loading wallet...</div>

    <div v-else>
        <div class="flex justify-end mb-5">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 w-52 text-right">
                <div class="text-sm text-gray-500 mb-1">
                    USD Balance
                </div>
                <div class="text-3xl font-bold text-emerald-600">
                    ${{ profile.balance.toLocaleString() }}
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-6">
            <div v-for="asset in profile.assets" 
                :key="asset.symbol" 
                class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 w-full sm:w-1/3 md:w-1/4 lg:w-1/4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ asset.symbol }}
                    </h3>
                    <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600">
                        Asset
                    </span>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Available</span>
                        <span class="font-medium">
                            {{ asset.amount }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Locked</span>
                        <span class="font-medium text-amber-600">
                            {{ asset.locked_amount }}
                        </span>
                    </div>
                    <div class="border-t pt-2 flex justify-between font-semibold">
                        <span>Total</span>
                        <span>
                            {{ totalAsset(asset) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>
