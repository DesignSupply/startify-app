import { createApp } from 'vue';
import { createPinia } from 'pinia';
import { useRouter } from './route.ts';
import App from '../vue/App.vue';

createApp(App).use(createPinia()).use(useRouter).mount('#app-vue');
