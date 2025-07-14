import type { Viewport, Metadata } from 'next';
import '@/styles/globals.css';
import Base from '@/components/Base';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';
import SiteThemeProvider from '@/providers/SiteThemeProvider';

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
        <SiteThemeProvider>
          <Base>{children}</Base>
        </SiteThemeProvider>
      </body>
    </html>
  );
}
