'use client';

import { useRouter, usePathname } from 'next/navigation';
import { useEffect } from 'react';
import { useMeQuery } from '@/hooks/auth/useAuth';

type Props = {
  children: React.ReactNode;
};

export default function AuthGuard({ children }: Props) {
  const router = useRouter();
  const pathname = usePathname();
  const { data, isLoading, error } = useMeQuery();

  useEffect(() => {
    if (isLoading) return;
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    if (!data || (error && (error as any).status === 401)) {
      // 未認証の場合にはsigninへリダイレクト（nextパラメーターに現在のパスを渡すと再ログイン後にそのページにリダイレクト）
      // const next = pathname ? `?next=${encodeURIComponent(pathname)}` : '';
      // router.replace(`/signin${next}`);
      router.replace('/signin');
    }
  }, [data, error, isLoading, pathname, router]);

  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  if (isLoading || (!data && !(error as any)?.status)) {
    return null;
  }

  if (!data) return null;
  return <>{children}</>;
}
