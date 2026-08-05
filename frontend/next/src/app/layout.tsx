import type { Viewport, Metadata } from 'next';
import { Suspense } from 'react';
import '@/styles/globals.css';
import Base from '@/components/Base';
import { metaDefault } from '@/utils/meta';
import { notoSansJP } from '@/utils/fonts';
// import SiteThemeProvider from '@/providers/SiteThemeProvider'; // context
import ReactQueryProvider from '@/providers/ReactQueryProvider';
import { GoogleAnalytics } from '@next/third-parties/google';
import GoogleAdsenseScript from '@/components/GoogleAdsenseScript';
import {
  getGoogleAdsenseId,
  getGoogleAnalyticsId,
  isGoogleAdsenseEnabled,
  isGoogleAnalyticsEnabled,
} from '@/utils/googleTags';

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
  const gaId = getGoogleAnalyticsId();
  const adsenseId = getGoogleAdsenseId();
  const showGoogleAnalytics = isGoogleAnalyticsEnabled(gaId);
  const showGoogleAdsense = isGoogleAdsenseEnabled(adsenseId);

  return (
    <html lang="ja">
      <body className={`${notoSansJP.variable}`}>
        {/* <SiteThemeProvider> */} {/* context */}
        <ReactQueryProvider>
          <Suspense>
            <Base>{children}</Base>
          </Suspense>
        </ReactQueryProvider>
        {/* </SiteThemeProvider> */} {/* context */}
      </body>
      {showGoogleAnalytics && gaId && <GoogleAnalytics gaId={gaId} />}
      {showGoogleAdsense && adsenseId && <GoogleAdsenseScript clientId={adsenseId} />}
    </html>
  );
}
