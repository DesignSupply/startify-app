const vueRoot = document.querySelector('#app-vue');
if (vueRoot) {
  (async () => {
    const { createApp } = await import('vue');
    const { createPinia, defineStore } = await import('pinia');
    const { createRouter, createWebHistory } = await import('vue-router');
    const App = (await import('@/vue/App.vue')).default;
    const Home = (await import('@/vue/pages/Home.vue')).default;
    const pinia = createPinia();

    // store
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

    // app setup
    const app = createApp(App).use(pinia).use(router);
    const { useStoreKey } = await import('@/types/vue-store');
    app.provide(useStoreKey, store(pinia));
    app.mount(vueRoot as HTMLElement);
    if (import.meta.hot) {
      import.meta.hot.dispose(() => app.unmount());
    }
  })();
}

export {};
