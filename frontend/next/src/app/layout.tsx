import type { Viewport, Metadata } from 'next';
import { Suspense } from 'react';
import '@/styles/globals.css';
import Base from '@/components/Base';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';
import SiteThemeProvider from '@/providers/SiteThemeProvider';
import { GoogleAnalytics } from '@next/third-parties/google';
import GoogleAdsenseScript from '@/components/GoogleAdsenseScript';

export const metadata: Metadata = metaDefault;
export const viewport: Viewport = {
  themeColor: '#000000',
  colorScheme: 'light dark',
  width: 'device-width',
  initialScale: 1,
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
          <Suspense>
            <Base>{children}</Base>
          </Suspense>
        </SiteThemeProvider>
      </body>
      {process.env.NODE_ENV !== 'development' && process.env.GOOGLE_ANALYTICS_ID && (
        <GoogleAnalytics gaId={process.env.GOOGLE_ANALYTICS_ID} />
      )}
      {process.env.NODE_ENV !== 'development' && process.env.GOOGLE_ADSENSE_ID && (
        <GoogleAdsenseScript />
      )}
    </html>
  );
}
