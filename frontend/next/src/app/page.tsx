'use client';

import Link from 'next/link';
import JsonLd from '@/components/JsonLd';
import AdsenseUnit from '@/components/AdsenseUnit';
import { useMeQuery } from '@/hooks/auth/useAuth';

export default function HomePage() {
  const { data: meData } = useMeQuery();
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}`, name: 'HOME' },
    },
  ];

  return (
    <main className="app-main">
      <h1>トップページ</h1>
      <Link href={'/example'}>静的ルーティングページサンプルへ</Link>
      <br />
      <Link href={meData ? '/dashboard' : '/signin'}>
        {meData ? 'ダッシュボードへ' : 'ログインページへ'}
      </Link>
      <AdsenseUnit slot="XXXXXXXXXX" format="auto" />
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}
