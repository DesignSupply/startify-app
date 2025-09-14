const vueRoot = document.querySelector('#app-vue');
if (vueRoot) {
  (async () => {
    const { createApp } = await import('vue');
    const { createPinia } = await import('pinia');
    const { useRouter } = await import('@/scripts/route');
    const App = (await import('@/vue/App.vue')).default;
    createApp(App).use(createPinia()).use(useRouter).mount(vueRoot as HTMLElement);
  })();
}

export {};
