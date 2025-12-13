<script setup>
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

function logout() {
  auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
    <div>
        <header class="bg-indigo-500 text-white">
            <nav class="flex items-center justify-between p-4 max-w-screen-xl mx-auto">
                <!-- Left Nav -->
                <div class="space-x-6">
                    <router-link to="/" class="hover:underline">Home</router-link>
                </div>

                <!-- Right Auth Area -->
                <div class="space-x-4">
                    <template v-if="!auth.isAuthenticated">
                        <router-link to="/login" class="hover:underline">Login</router-link>
                    </template>
                    <template v-else>
                        <router-link to="/orders" class="hover:underline">Place Order</router-link>
                        <button @click="logout" class="hover:underline">Logout</button>
                    </template>
                </div>
            </nav>
        </header>

        <main class="p-0">
            <router-view />
        </main>
    </div>
</template>