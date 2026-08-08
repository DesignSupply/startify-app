'use client';

import Link from 'next/link';
import AdsenseUnit from '@/components/AdsenseUnit';
import { useMeQuery } from '@/hooks/auth/useAuth';

export default function HomePageContent() {
  const { data: meData } = useMeQuery();

  return (
    <>
      <h1>トップページ</h1>
      <Link href={'/example'}>静的ルーティングページサンプルへ</Link>
      <br />
      <Link href={meData ? '/dashboard' : '/signin'}>
        {meData ? 'ダッシュボードへ' : 'ログインページへ'}
      </Link>
      <AdsenseUnit slot="XXXXXXXXXX" format="auto" />
    </>
  );
}
