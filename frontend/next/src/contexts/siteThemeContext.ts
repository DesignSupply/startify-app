'use client';

import { createContext, useContext } from 'react';

export type stateType = {
  currentTheme: 'light' | 'dark';
}

export type siteThemeContextType = {
  state: stateType;
  setState: React.Dispatch<React.SetStateAction<stateType>>;
};

export const defaultState: stateType = {
  currentTheme: 'light',
};

export const SiteThemeContext = createContext<siteThemeContextType>({
  state: defaultState,
  setState: () => {}
});

export const useSiteThemeContext = () => useContext(SiteThemeContext); 