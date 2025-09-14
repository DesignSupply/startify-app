import React, { createContext, useState, useContext, useEffect } from 'react';
import ExampleComponent from '@/react/components/ExampleComponent';
import { createBrowserRouter, RouterProvider } from 'react-router-dom';
import Home from '@/react/pages/Home';

// store
const storeData = {
  message: 'Hello World'
};
const Context = createContext<{ message: string } | null>(null);

// route
const routes = createBrowserRouter([
  {
    path: '/',
    element: <Home />
  },
  {
    path: '*',
    element: <Home /> // fallback
  }
]);

let didEffect = false;

const AppReact = () => {
  const [text, setText] = useState('This is ExampleComponent (React)');
  const [context, setContext] = useState(storeData);
  useEffect(() => {
    if (import.meta.env.DEV && didEffect) return;
    didEffect = true;
    setContext(() => {
      const contextData = { message: 'state updated.' };
      console.log(`React is ready. ${contextData.message}`);
      return contextData;
    });
  }, []);
  return (
    <>
      <Context.Provider value={context}>
        <RouterProvider router={routes} />
        <ExampleComponent message={text} />
      </Context.Provider>
    </>
  );
};

// eslint-disable-next-line react-refresh/only-export-components
export function useSomeContext() {
  return useContext(Context);
}

export default AppReact;
