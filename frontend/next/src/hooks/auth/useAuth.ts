'use client';

import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { login, logout, me, LoginParams, MeResponse } from '@/features/auth/apiAuth';
import { clearAccessToken } from '@/helpers/storeAccessToken';

const ME_KEY = ['auth', 'me'] as const;

export function useMeQuery() {
  return useQuery<MeResponse>({
    queryKey: ME_KEY,
    queryFn: me,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    retry(failureCount, error: any) {
      if (error?.status === 401) return false;
      return failureCount < 2;
    },
  });
}

export function useLoginMutation() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (p: LoginParams) => login(p),
    onSuccess() {
      qc.invalidateQueries({ queryKey: ME_KEY });
    },
  });
}

export function useLogoutMutation() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: () => logout(),
    onSuccess() {
      clearAccessToken();
      qc.invalidateQueries({ queryKey: ME_KEY });
    },
  });
}
