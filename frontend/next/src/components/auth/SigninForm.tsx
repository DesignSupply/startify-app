'use client';

import { useForm } from 'react-hook-form';
import { z } from 'zod';
import { zodResolver } from '@hookform/resolvers/zod';
import { useLoginMutation } from '@/hooks/auth/useAuth';
import { useRouter, useSearchParams } from 'next/navigation';
import { useState } from 'react';

const schema = z.object({
  email: z.string({ required_error: 'メールは必須です' }).email('メール形式が正しくありません'),
  password: z.string({ required_error: 'パスワードは必須です' }).min(8, { message: '8文字以上で入力してください' }),
});

type FormValues = z.infer<typeof schema>;

export default function SigninForm() {
  const router = useRouter();
  const params = useSearchParams();
  const next = params.get('next') || '/dashboard';
  const { mutateAsync, isPending } = useLoginMutation();
  const [apiError, setApiError] = useState<string | null>(null);

  const { register, handleSubmit, formState: { errors } } = useForm<FormValues>({
    resolver: zodResolver(schema),
    mode: 'onSubmit',
  });

  const onSubmit = async (values: FormValues) => {
    setApiError(null);
    try {
      await mutateAsync(values);
      router.replace(next);
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    } catch (e: any) {
      setApiError(e?.data?.message || 'ログインに失敗しました');
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <div>
        <label htmlFor="email">メールアドレス</label>
        <input id="email" type="email" autoComplete="email" {...register('email')} />
        {errors.email && <p style={{ color: 'red' }}>{errors.email.message}</p>}
      </div>
      <div>
        <label htmlFor="password">パスワード</label>
        <input id="password" type="password" autoComplete="current-password" {...register('password')} />
        {errors.password && <p style={{ color: 'red' }}>{errors.password.message}</p>}
      </div>
      {apiError && <p style={{ color: 'red' }}>{apiError}</p>}
      <button type="submit" disabled={isPending}>ログイン</button>
    </form>
  );
}


