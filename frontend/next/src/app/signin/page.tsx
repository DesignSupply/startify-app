'use client';

import Link from 'next/link';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import SigninForm from '@/components/auth/SigninForm';
import JsonLd from '@/components/JsonLd';
import { useMeQuery } from '@/hooks/auth/useAuth';

export default function SigninPage() {
  const router = useRouter();
  const { data, isLoading, isError } = useMeQuery();
  const jsonLdData = [
    {
      '@type': 'ListItem',
      position: 1,
      item: { '@id': `${process.env.APPURL}`, name: 'HOME' },
    },
    {
      '@type': 'ListItem',
      position: 2,
      item: { '@id': `${process.env.APPURL}/signin`, name: 'ログイン' },
    },
  ];

  useEffect(() => {
    if (data && !isLoading && !isError) {
      router.replace('/dashboard');
    }
  }, [data, isLoading, isError, router]);

  return (
    <main className="app-main">
      <h1>ログイン</h1>
      <SigninForm />
      <Link href={'/'}>トップページへ</Link>
      <JsonLd jsonLd={jsonLdData} />
    </main>
  );
}
