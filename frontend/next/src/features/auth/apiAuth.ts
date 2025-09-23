import { apiFetch } from '@/helpers/api';
import { setAccessToken, clearAccessToken } from '@/helpers/storeAccessToken';

export type LoginParams = { email: string; password: string };
export type LoginResponse = { access_token: string };
export type MeResponse = { id: number; name: string; email: string; created_at?: string };

export async function login(params: LoginParams): Promise<LoginResponse> {
  const res = await apiFetch<LoginResponse>('/auth/login', {
    method: 'POST',
    body: params,
    // 必須: クロスサイトでSet-Cookieを受け取るため
    withCredentials: true,
  });
  setAccessToken(res.access_token);
  return res;
}

export async function refresh(): Promise<LoginResponse> {
  const res = await apiFetch<LoginResponse>('/auth/refresh', {
    method: 'POST',
    withCredentials: true,
  });
  setAccessToken(res.access_token);
  return res;
}

export async function logout(): Promise<void> {
  await apiFetch<void>('/auth/logout', {
    method: 'POST',
    withCredentials: true,
  });
  clearAccessToken();
}

export async function me(): Promise<MeResponse> {
  return apiFetch<MeResponse>('/auth/me', {
    method: 'GET',
    auth: true,
    autoRefresh: true,
  });
}
