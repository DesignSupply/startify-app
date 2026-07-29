import { defineConfig } from 'vitest/config';
import { fileURLToPath } from 'node:url';

export default defineConfig({
  // Vite 8 (Vitest 4.1+) uses oxc for JSX. Next.js sets jsx: "preserve" in tsconfig,
  // so tests need an explicit automatic runtime here without affecting the app build.
  oxc: {
    jsx: {
      runtime: 'automatic',
    },
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  test: {
    environment: 'jsdom',
  },
});
