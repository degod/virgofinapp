import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

import Home from '../Pages/Home.vue';
import Layout from '../Layouts/Layout.vue';
import Login from '../Pages/Login.vue';
import LimitOrder from '../Pages/LimitOrder.vue';
import OrderBook from '../Pages/OrderBook.vue';

const routes = [
  {
    path: '/',
    component: Layout,
    children: [
      { path: '', name: 'home', component: Home, meta: { requiresAuth: false } },
      { path: 'login', name: 'login', component: Login, meta: { requiresAuth: false } },

      {
        path: 'orders',
        children: [
          { path: 'create', name: 'orders', component: LimitOrder, meta: { requiresAuth: true } },
          { path: 'book/:symbol', name: 'orderbook', component: OrderBook, props: true, meta: { requiresAuth: false } },
        ],
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
  const auth = useAuthStore();
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);

  if (requiresAuth && !auth.isAuthenticated) {
    return next({ 
      name: 'login', 
      query: { redirect: to.fullPath } 
    });
  }

  if (auth.isAuthenticated && (to.name === 'login')) {
    return next({ name: 'home' });
  }

  next();
});

export default router;