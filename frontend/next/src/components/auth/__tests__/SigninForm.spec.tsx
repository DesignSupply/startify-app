'use client';

/**
 * SigninForm のテスト:
 * - 必須項目を空で送信するとバリデーションエラーが表示される
 * - 正常入力で useLoginMutation が呼ばれ、/dashboard にリダイレクトされる
 */

import React from 'react';
import { fireEvent, render, screen, waitFor, cleanup } from '@testing-library/react';
import { describe, expect, it, vi, beforeEach, afterEach, MockedFunction } from 'vitest';
import SigninForm from '../SigninForm';
import { useLoginMutation } from '@/hooks/auth/useAuth';
import { useRouter, useSearchParams } from 'next/navigation';

vi.mock('@/hooks/auth/useAuth', () => ({
  useLoginMutation: vi.fn(),
}));
vi.mock('next/navigation', () => ({
  useRouter: vi.fn(),
  useSearchParams: vi.fn(),
}));

const mockUseLoginMutation = useLoginMutation as MockedFunction<
  typeof useLoginMutation
>;
const mockUseRouter = useRouter as MockedFunction<typeof useRouter>;
const mockUseSearchParams = useSearchParams as MockedFunction<typeof useSearchParams>;

describe('SigninForm', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    mockUseLoginMutation.mockReturnValue({
      mutateAsync: vi.fn().mockResolvedValue({}),
      isPending: false,
    } as unknown as ReturnType<typeof useLoginMutation>);
    mockUseRouter.mockReturnValue({ replace: vi.fn() } as unknown as ReturnType<typeof useRouter>);
    mockUseSearchParams.mockReturnValue({
      get: vi.fn().mockReturnValue('/dashboard'),
    } as unknown as ReturnType<typeof useSearchParams>);
  });

  it('shows validation errors', async () => {
    render(<SigninForm />);

    const loginButton = screen.getAllByRole('button', { name: 'ログイン' })[0];
    fireEvent.click(loginButton);

    expect(await screen.findByText('メール形式が正しくありません')).toBeTruthy();
    expect(await screen.findByText('8文字以上で入力してください')).toBeTruthy();
  });

  it('submits credentials and redirects on success', async () => {
    const mutateAsync = vi.fn().mockResolvedValue({});
    const replace = vi.fn();
    mockUseLoginMutation.mockReturnValue({
      mutateAsync,
      isPending: false,
    } as unknown as ReturnType<typeof useLoginMutation>);
    mockUseRouter.mockReturnValue({ replace } as unknown as ReturnType<typeof useRouter>);
    mockUseSearchParams.mockReturnValue({
      get: vi.fn().mockReturnValue('/dashboard'),
    } as unknown as ReturnType<typeof useSearchParams>);

    render(<SigninForm />);

    fireEvent.change(screen.getByLabelText('メールアドレス'), {
      target: { value: 'test@example.com' },
    });
    fireEvent.change(screen.getByLabelText('パスワード'), {
      target: { value: 'Password123' },
    });
    const loginButton = screen.getAllByRole('button', { name: 'ログイン' })[0];
    fireEvent.click(loginButton);

    await waitFor(() => expect(mutateAsync).toHaveBeenCalled());
    expect(replace).toHaveBeenCalledWith('/dashboard');
  });
  afterEach(() => {
    cleanup();
  });
});
