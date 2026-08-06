export type DeployEnv = 'development' | 'staging' | 'production';

export function getDeployEnv(): DeployEnv | undefined {
  const value = process.env.NEXT_PUBLIC_DEPLOY_ENV;
  if (value === 'development' || value === 'staging' || value === 'production') {
    return value;
  }
  return undefined;
}

export function isProductionDeployEnv(): boolean {
  return getDeployEnv() === 'production';
}

function normalizeId(id: string | undefined): string | undefined {
  const trimmed = id?.trim();
  return trimmed ? trimmed : undefined;
}

export function getGoogleAnalyticsId(): string | undefined {
  return normalizeId(process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS_ID);
}

export function getGoogleAdsenseId(): string | undefined {
  return normalizeId(process.env.NEXT_PUBLIC_GOOGLE_ADSENSE_ID);
}

export function isGoogleAnalyticsEnabled(analyticsId?: string): boolean {
  const id = normalizeId(analyticsId ?? process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS_ID);
  return isProductionDeployEnv() && Boolean(id);
}

export function isGoogleAdsenseEnabled(adsenseId?: string): boolean {
  const id = normalizeId(adsenseId ?? process.env.NEXT_PUBLIC_GOOGLE_ADSENSE_ID);
  return isProductionDeployEnv() && Boolean(id);
}
