import { createContext } from 'react';

export type Store = {
  message: string;
  // eslint-disable-next-line no-unused-vars
  updateMessage: (text: string) => void;
};

export const storeContext = createContext<Store | null>(null);
