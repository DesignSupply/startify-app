// React Fast Refresh preamble for dev when manually mounting React
if (import.meta.env.DEV && !(window as any).__vite_plugin_react_preamble_installed__) {
  (async () => {
    // @ts-expect-error: Vite dev virtual module is not resolvable by TS
    const RefreshRuntime = (await import('/@react-refresh')).default as any;
    RefreshRuntime.injectIntoGlobalHook(window as any);
    (window as any).$RefreshReg$ = () => {};
    (window as any).$RefreshSig$ = () => (type: any) => type;
    (window as any).__vite_plugin_react_preamble_installed__ = true;
  })();
}

const reactRoot = document.querySelector('#app-react');
if (reactRoot) {
  (async () => {
    const React = (await import('react')).default;
    const { createRoot } = await import('react-dom/client');
    const AppReact = (await import('@/react/App')).default;
    createRoot(reactRoot as HTMLElement).render(React.createElement(AppReact));
  })();
}

export {};
