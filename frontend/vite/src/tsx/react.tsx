import React from 'react';
import ReactDOM from 'react-dom/client';
import App from '../tsx/App';

const Root = import.meta.env.DEV ? <App /> : <React.StrictMode><App /></React.StrictMode>;
ReactDOM.createRoot(document.getElementById('app-react') as HTMLElement).render(Root);
