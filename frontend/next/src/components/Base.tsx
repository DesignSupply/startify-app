'use client';

import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';
import { useSiteThemeContext } from '@/contexts/siteThemeContext';

export default function Base({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const themeMode = useSiteThemeContext().state.currentTheme;

  return (
    <div className="app-base" data-theme={themeMode}>
      <div className="app-layout">
        <Header />
        {children}
        <Footer />
      </div>
      <OffCanvas />
    </div>
  );
} 
