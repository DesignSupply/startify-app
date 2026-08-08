import type { Metadata } from 'next';
import ExamplePageContent from './_content';
import JsonLd from '@/components/JsonLd';
import { metaDefault } from '@/utils/meta';

export const metadata: Metadata = {
  title: `静的ルーティングページサンプル | ${process.env.APPNAME}`,
  alternates: { canonical: `${process.env.APPURL}/example` },
  openGraph: {
    type: 'article',
    url: `${process.env.APPURL}/example`,
    title: `静的ルーティングページサンプル | ${process.env.APPNAME}`,
    description: metaDefault.openGraph?.description,
    siteName: metaDefault.openGraph?.siteName,
    locale: metaDefault.openGraph?.locale,
    images: metaDefault.openGraph?.images,
  },
  twitter: {
    site: metaDefault.twitter?.site,
    title: `静的ルーティングページサンプル | ${process.env.APPNAME}`,
    description: metaDefault.twitter?.description,
    images: metaDefault.twitter?.images,
  },
};

export default function ExamplePage() {
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}`, name: 'HOME' },
    },
    {
      '@type': 'ListItem',
      position: 2,
      item: { '@id': `${process.env.APPURL}/example`, name: '静的ルーティングページサンプル' },
    },
  ];

  return (
    <main className="app-main">
      <ExamplePageContent />
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}
