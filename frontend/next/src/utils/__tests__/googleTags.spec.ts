import { afterEach, describe, expect, it, vi } from 'vitest';
import {
  getDeployEnv,
  isGoogleAdsenseEnabled,
  isGoogleAnalyticsEnabled,
  isProductionDeployEnv,
} from '../googleTags';

const originalDeployEnv = process.env.NEXT_PUBLIC_DEPLOY_ENV;
const originalAnalyticsId = process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS_ID;
const originalAdsenseId = process.env.NEXT_PUBLIC_GOOGLE_ADSENSE_ID;

afterEach(() => {
  process.env.NEXT_PUBLIC_DEPLOY_ENV = originalDeployEnv;
  process.env.NEXT_PUBLIC_GOOGLE_ANALYTICS_ID = originalAnalyticsId;
  process.env.NEXT_PUBLIC_GOOGLE_ADSENSE_ID = originalAdsenseId;
  vi.unstubAllEnvs();
});

describe('googleTags', () => {
  it('disables Google tags in development', () => {
    vi.stubEnv('NEXT_PUBLIC_DEPLOY_ENV', 'development');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', 'G-DUMMY');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ADSENSE_ID', 'ca-pub-dummy');

    expect(isProductionDeployEnv()).toBe(false);
    expect(isGoogleAnalyticsEnabled()).toBe(false);
    expect(isGoogleAdsenseEnabled()).toBe(false);
  });

  it('disables Google tags in staging', () => {
    vi.stubEnv('NEXT_PUBLIC_DEPLOY_ENV', 'staging');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', 'G-DUMMY');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ADSENSE_ID', 'ca-pub-dummy');

    expect(isGoogleAnalyticsEnabled()).toBe(false);
    expect(isGoogleAdsenseEnabled()).toBe(false);
  });

  it('disables Google tags in production when IDs are empty', () => {
    vi.stubEnv('NEXT_PUBLIC_DEPLOY_ENV', 'production');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', '');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ADSENSE_ID', '');

    expect(isGoogleAnalyticsEnabled()).toBe(false);
    expect(isGoogleAdsenseEnabled()).toBe(false);
  });

  it('enables Google tags in production when IDs are set', () => {
    vi.stubEnv('NEXT_PUBLIC_DEPLOY_ENV', 'production');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', 'G-DUMMY');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ADSENSE_ID', 'ca-pub-dummy');

    expect(isGoogleAnalyticsEnabled()).toBe(true);
    expect(isGoogleAdsenseEnabled()).toBe(true);
  });

  it('disables Google tags when deploy env is unset', () => {
    vi.stubEnv('NEXT_PUBLIC_DEPLOY_ENV', '');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', 'G-DUMMY');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ADSENSE_ID', 'ca-pub-dummy');

    expect(getDeployEnv()).toBeUndefined();
    expect(isGoogleAnalyticsEnabled()).toBe(false);
    expect(isGoogleAdsenseEnabled()).toBe(false);
  });

  it('disables Google tags for invalid deploy env values', () => {
    vi.stubEnv('NEXT_PUBLIC_DEPLOY_ENV', 'preview');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ANALYTICS_ID', 'G-DUMMY');
    vi.stubEnv('NEXT_PUBLIC_GOOGLE_ADSENSE_ID', 'ca-pub-dummy');

    expect(getDeployEnv()).toBeUndefined();
    expect(isGoogleAnalyticsEnabled()).toBe(false);
    expect(isGoogleAdsenseEnabled()).toBe(false);
  });
});
