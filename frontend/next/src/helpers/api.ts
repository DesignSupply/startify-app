'use client';

import { getAccessToken, clearAccessToken, setAccessToken } from './storeAccessToken';

type ApiOptions = {
  method?: 'GET' | 'POST';
  body?: unknown;
  auth?: boolean; // default false
  withCredentials?: boolean; // default false
  autoRefresh?: boolean; // default false
  signal?: AbortSignal;
  headers?: Record<string, string>;
};

export const baseURL = process.env.NEXT_PUBLIC_API_BASE_URL ?? '';

// Single-flight refresh control
let refreshPromise: Promise<boolean> | null = null;

async function ensureRefreshOnce(): Promise<boolean> {
  if (!refreshPromise) {
    refreshPromise = tryRefreshToken().finally(() => {
      refreshPromise = null;
    });
  }
  return refreshPromise;
}

export async function apiFetch<T>(path: string, options: ApiOptions = {}): Promise<T> {
  const {
    method = 'GET',
    body,
    auth = false,
    withCredentials = false,
    autoRefresh = false,
    signal,
    headers = {},
  } = options;

  const url = path.startsWith('http') ? path : `${baseURL}${path}`;

  const baseHeaders: Record<string, string> = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...headers,
  };

  // Authorization
  if (auth) {
    const token = getAccessToken();
    if (token) baseHeaders['Authorization'] = `Bearer ${token}`;
  }

  const init: RequestInit = {
    method,
    headers: baseHeaders,
    body: body != null ? JSON.stringify(body) : undefined,
    credentials: withCredentials ? 'include' : 'same-origin',
    signal,
  };

  const res = await fetch(url, init);

  // 401 auto refresh (one-shot)
  if (autoRefresh && res.status === 401 && !isAuthRoute(path)) {
    const refreshed = await ensureRefreshOnce();
    if (refreshed) {
      const retryRes = await fetch(url, initWithNewToken(init));
      return parseJson<T>(retryRes);
    }
  }

  return parseJson<T>(res);
}

function initWithNewToken(init: RequestInit): RequestInit {
  const token = getAccessToken();
  const headers = new Headers(init.headers as HeadersInit);
  if (token) headers.set('Authorization', `Bearer ${token}`);
  return { ...init, headers };
}

function isAuthRoute(path: string): boolean {
  const p = path.startsWith('http') ? new URL(path).pathname : path;
  return p.includes('/auth/login') || p.includes('/auth/refresh') || p.includes('/auth/logout');
}

async function tryRefreshToken(): Promise<boolean> {
  try {
    const res = await fetch(`${baseURL}/auth/refresh`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!res.ok) {
      clearAccessToken();
      return false;
    }
    const data = (await res.json()) as { access_token?: string };
    if (!data.access_token) {
      clearAccessToken();
      return false;
    }
    setAccessToken(data.access_token);
    return true;
  } catch {
    clearAccessToken();
    return false;
  }
}

async function parseJson<T>(res: Response): Promise<T> {
  const text = await res.text();
  const data = text ? JSON.parse(text) : undefined;
  if (!res.ok) {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const err: any = new Error(data?.message || 'Request failed');
    err.status = res.status;
    err.code = data?.code;
    err.data = data;
    throw err;
  }
  return data as T;
}
