'use client';

import Link from 'next/link';
import { Suspense, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import SigninForm from '@/components/auth/SigninForm';
import { useMeQuery } from '@/hooks/auth/useAuth';

export default function SigninPageContent() {
  const router = useRouter();
  const { data, isLoading, isError } = useMeQuery();

  useEffect(() => {
    if (data && !isLoading && !isError) {
      router.replace('/dashboard');
    }
  }, [data, isLoading, isError, router]);

  return (
    <>
      <h1>ログイン</h1>
      <Suspense fallback={null}>
        <SigninForm />
      </Suspense>
      <Link href={'/'}>トップページへ</Link>
    </>
  );
}
