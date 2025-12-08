'use client';

import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';
import { useSiteThemeStore } from '@/stores/siteThemeStore';
// import { useSiteThemeContext } from '@/contexts/siteThemeContext'; // context

export default function Base({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  // const themeMode = useSiteThemeContext().state.currentTheme; // context
  const themeMode = useSiteThemeStore((state) => state.currentTheme);

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
