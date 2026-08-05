'use client';

import { useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';
import { getGoogleAdsenseId, isGoogleAdsenseEnabled } from '@/utils/googleTags';

type propsType = {
  slot: string;
  format: string;
};

declare global {
  interface Window {
    adsbygoogle: { [key: string]: unknown }[];
  }
}

export default function AdsenseUnit(props: propsType) {
  const didEffect = useRef(false);
  const currentPath = usePathname();
  const adsenseId = getGoogleAdsenseId();
  const enabled = isGoogleAdsenseEnabled(adsenseId);

  useEffect(() => {
    if (!enabled) {
      return;
    }

    if (!didEffect.current) {
      didEffect.current = true;
      try {
        (window.adsbygoogle = window.adsbygoogle || []).push({});
      } catch (err) {
        console.error(err);
      }
    }
  }, [currentPath, enabled]);

  if (!enabled || !adsenseId) {
    return null;
  }

  return (
    <ins
      className="adsbygoogle"
      style={{ display: 'block' }}
      data-ad-client={adsenseId}
      data-ad-slot={props.slot}
      data-ad-format={props.format}
      data-full-width-responsive="true"
    />
  );
}
