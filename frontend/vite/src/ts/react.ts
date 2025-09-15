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

let root: import('react-dom/client').Root | undefined;
const reactRoot = document.querySelector('#app-react');
if (reactRoot) {
  (async () => {
    const React = (await import('react')).default;
    const { useMemo, useState } = await import('react');
    const { createRoot } = await import('react-dom/client');
    const { createBrowserRouter, RouterProvider } = await import('react-router-dom');
    const { storeContext } = await import('@/react/storeContext');
    const App = (await import('@/react/App')).default;
    const Home = (await import('@/react/pages/Home')).default;

    // router
    const router = createBrowserRouter([
      {
        path: '/',
        id: 'Home',
        element: React.createElement(Home),
        handle: {
          name: 'Home'
        }
      },
      {
        path: '*',
        id: 'NotFound',
        element: React.createElement(Home),
        handle: {
          name: 'NotFound'
        }
      }
    ]);

    // store
    const useStore = () => {
      const [message, setMessage] = useState('Hello World');
      const store = useMemo(() => ({ message, updateMessage: setMessage }), [message]);
      return store;
    };

    // provider & app component
    const Provider = () => {
      return React.createElement(
        storeContext.Provider,
        { value: useStore() },
        React.createElement(
          React.Fragment,
          null,
          React.createElement(RouterProvider, { router }),
          React.createElement(App)
        )
      );
    };

    root = createRoot(reactRoot as HTMLElement);
    root.render(React.createElement(Provider));
  })();
}

if (import.meta.hot) {
  import.meta.hot.dispose(() => root?.unmount());
}

export {};
