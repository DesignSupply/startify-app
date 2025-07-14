import type { Viewport, Metadata } from 'next';
import '@/styles/globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import OffCanvas from '@/components/OffCanvas';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';

export const metadata: Metadata = metaDefault;
export const viewport: Viewport = { 
  themeColor: '#000000',
  colorScheme: 'light dark',
  width: 'device-width',
  initialScale: 1
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="ja">
      <body className={`${notoSansJP.variable}`}>
        <div className="app-layout">
          <Header />
          {children}
          <Footer />
        </div>
        <OffCanvas />
      </body>
    </html>
  );
}
