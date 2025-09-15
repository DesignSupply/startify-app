import { createApp } from 'vue';
import { createPinia, defineStore } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import App from '../vue/App.vue';
import Home from '../vue/pages/Home.vue';
import { useStoreKey } from '@/types/vue-store';

if (document.querySelector('#app-vue')) {
  const pinia = createPinia();

  // store (Astro準拠: provide/inject で型付きキーを提供)
  const store = defineStore('useStore', {
    state: () => ({
      message: 'Hello World'
    }),
    actions: {
      updateMessage(payload: string) {
        this.message = payload;
      }
    },
    getters: {
      getMessage: (s) => s.message
    }
  });

  // router
  const router = createRouter({
    history: createWebHistory(),
    routes: [
      {
        path: '/',
        name: 'Home',
        component: Home
      }
    ]
  });

  const app = createApp(App).use(pinia).use(router);
  app.provide(useStoreKey, store(pinia));
  app.mount('#app-vue');
}
