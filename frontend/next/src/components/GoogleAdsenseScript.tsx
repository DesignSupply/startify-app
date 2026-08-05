'use client';

import Script from 'next/script';

type GoogleAdsenseScriptProps = {
  clientId: string;
};

export default function GoogleAdsenseScript({ clientId }: GoogleAdsenseScriptProps) {
  return (
    <Script
      async
      src={`https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=${clientId}`}
      crossOrigin="anonymous"
      strategy="afterInteractive"
    />
  );
}
