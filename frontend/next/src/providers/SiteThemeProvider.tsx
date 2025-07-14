'use client';

import { useState } from 'react';
import { SiteThemeContext, defaultState, stateType } from '@/contexts/siteThemeContext';

export default function SiteThemeProvider({ children }: { children: React.ReactNode }) {
  const [state, setState] = useState<stateType>(defaultState);
  const contextValue = { state, setState };

  return <SiteThemeContext.Provider value={contextValue}>{children}</SiteThemeContext.Provider>;
}
