'use client';

import { useEffect, useRef } from 'react';
import { usePathname } from 'next/navigation';

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

  useEffect(() => {
    if (!didEffect.current) {
      didEffect.current = true;
      try {
        if (process.env.NODE_ENV !== 'development') {
          (window.adsbygoogle = window.adsbygoogle || []).push({});
        }
      } catch (err) {
        console.error(err);
      }
    }
  }, [currentPath]);

  return (
    <>
      {process.env.NODE_ENV !== 'development' && (
        <ins
          className="adsbygoogle"
          style={{ display: 'block' }}
          data-ad-client={process.env.GOOGLE_ADSENSE_ID}
          data-ad-slot={props.slot}
          data-ad-format={props.format}
          data-full-width-responsive="true"
        />
      )}
    </>
  );
}
