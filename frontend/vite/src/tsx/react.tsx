import React from 'react';
import ReactDOM from 'react-dom/client';
import App from '../tsx/App';

ReactDOM.createRoot(document.getElementById('app-react') as HTMLElement).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
