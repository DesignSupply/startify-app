'use client';

import { create } from 'zustand';

export type ThemeMode = 'light' | 'dark';

type SiteThemeState = {
  currentTheme: ThemeMode;
  setTheme: (mode: ThemeMode) => void;
};

export const useSiteThemeStore = create<SiteThemeState>((set) => ({
  currentTheme: 'light',
  setTheme: (mode) => set({ currentTheme: mode }),
}));
