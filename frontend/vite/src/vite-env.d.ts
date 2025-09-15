/// <reference types="vite/client" />
/// <reference types="vite-plugin-glsl/ext" />

declare module '*.vue' {
  import { ComponentOptions } from 'vue'
  const component: ComponentOptions;
  export default component
}