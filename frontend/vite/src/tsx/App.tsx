import React, { createContext, useState, useContext, useEffect } from "react";
import ExampleComponent from "./components/ExampleComponent";
import { createBrowserRouter, RouterProvider } from "react-router-dom";
import Home from "./pages/Home";

// store
const storeData = {
  message: "Hello World",
};
const Context = createContext<{ message: string } | null>(null);

// route
const routes = createBrowserRouter([
  {
    path: "/",
    element: <Home />,
  },
]);

const App = (): JSX.Element => {
  const [text, setText] = useState("ExampleComponent");
  const [context, setContext] = useState(storeData);
  console.log(`React is ready. ${context.message}`);
  useEffect(() => {
    setContext({ message: "state updated" });
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
export function useSampleContext() {
  return useContext(Context);
}

export default App;
