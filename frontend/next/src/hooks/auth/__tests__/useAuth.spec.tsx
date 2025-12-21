'use client';

/**
 * 認証フックのテスト:
 * - useMeQuery は ME を取得して成功すること
 * - useLoginMutation は login API を呼んで ['auth','me'] を invalidates すること
 * - useLogoutMutation はトークンをクリアし ['auth','me'] を invalidates すること
 */

import React from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { act, renderHook, waitFor } from '@testing-library/react';
import {
  beforeEach,
  describe,
  expect,
  it,
  MockedFunction,
  vi,
} from 'vitest';
import type { ReactNode } from 'react';
import * as apiAuth from '../../../features/auth/apiAuth';
import * as storeAccessToken from '../../../helpers/storeAccessToken';
import { useLoginMutation, useLogoutMutation, useMeQuery } from '../useAuth';

vi.mock('@/features/auth/apiAuth');
vi.mock('@/helpers/storeAccessToken');

const makeQueryClient = () =>
  new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
    },
  });

const createWrapper = (client: QueryClient) => {
  const Wrapper = ({ children }: { children: ReactNode }) => (
    <QueryClientProvider client={client}>{children}</QueryClientProvider>
  );
  Wrapper.displayName = 'QueryClientWrapper';
  return Wrapper;
};

describe('useAuth hooks', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('useMeQuery fetches data successfully', async () => {
    const client = makeQueryClient();
    const mockResponse = { id: 1, name: 'Test', email: 'test@example.com' };
    (apiAuth.me as MockedFunction<typeof apiAuth.me>).mockResolvedValue(mockResponse);

    const { result } = renderHook(() => useMeQuery(), {
      wrapper: createWrapper(client),
    });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));
    expect(result.current.data).toEqual(mockResponse);
  });

  it('useLoginMutation invalidates cache', async () => {
    const client = makeQueryClient();
    const invalidateSpy = vi.spyOn(client, 'invalidateQueries');
    (apiAuth.login as MockedFunction<typeof apiAuth.login>).mockResolvedValue({ access_token: 'token' });

    const { result } = renderHook(() => useLoginMutation(), {
      wrapper: createWrapper(client),
    });

    await act(async () => {
      await result.current.mutateAsync({ email: 'a', password: 'b' });
    });

    expect(apiAuth.login).toHaveBeenCalledWith({ email: 'a', password: 'b' });
    expect(invalidateSpy).toHaveBeenCalledWith({ queryKey: ['auth', 'me'] });
  });

  it('useLogoutMutation clears tokens and invalidates cache', async () => {
    const client = makeQueryClient();
    const invalidateSpy = vi.spyOn(client, 'invalidateQueries');
    (apiAuth.logout as MockedFunction<typeof apiAuth.logout>).mockResolvedValue();

    const { result } = renderHook(() => useLogoutMutation(), {
      wrapper: createWrapper(client),
    });

    await act(async () => {
      await result.current.mutateAsync();
    });

    expect(apiAuth.logout).toHaveBeenCalled();
    expect(storeAccessToken.clearAccessToken).toHaveBeenCalled();
    expect(invalidateSpy).toHaveBeenCalledWith({ queryKey: ['auth', 'me'] });
  });
});
