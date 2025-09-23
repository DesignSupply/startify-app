'use client';

import { useMeQuery, useLogoutMutation } from '@/hooks/auth/useAuth';
import { useRouter } from 'next/navigation';

export default function DashboardContents() {
  const { data } = useMeQuery();
  const { mutateAsync, isPending } = useLogoutMutation();
  const router = useRouter();

  const onLogout = async () => {
    await mutateAsync();
    router.replace('/signin');
  };

  return (
    <>
      <p>こんにちは、{data?.name} さん</p>
      <button onClick={onLogout} disabled={isPending}>ログアウト</button>
    </>
  );
}


