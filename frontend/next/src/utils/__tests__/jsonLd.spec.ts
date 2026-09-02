import { describe, expect, it } from 'vitest';
import { buildBreadcrumbListJsonLd, serializeJsonLd } from '../jsonLd';

describe('jsonLd', () => {
  it('uses https://schema.org as @context', () => {
    const jsonData = buildBreadcrumbListJsonLd([
      {
        '@type': 'ListItem',
        position: 1,
        item: { '@id': 'https://example.com/', name: 'HOME' },
      },
    ]);

    expect(jsonData['@context']).toBe('https://schema.org');
    expect(jsonData['@type']).toBe('BreadcrumbList');
  });

  it('builds a valid BreadcrumbList structure', () => {
    const jsonData = buildBreadcrumbListJsonLd([
      {
        '@type': 'ListItem',
        position: 1,
        item: { '@id': 'https://example.com/', name: 'HOME' },
      },
      {
        '@type': 'ListItem',
        position: 2,
        item: { '@id': 'https://example.com/example/', name: 'Example' },
      },
    ]);

    expect(jsonData.itemListElement).toHaveLength(2);
    expect(jsonData.itemListElement[1].position).toBe(2);
  });

  it('escapes < characters in serialized output', () => {
    const serialized = serializeJsonLd({
      name: '<script>alert(1)</script>',
    });

    expect(serialized).not.toContain('<');
    expect(serialized).toContain('\\u003cscript');
  });

  it('produces parseable JSON output', () => {
    const jsonData = buildBreadcrumbListJsonLd([
      {
        '@type': 'ListItem',
        position: 1,
        item: { '@id': 'https://example.com/', name: 'HOME' },
      },
    ]);
    const serialized = serializeJsonLd(jsonData);

    expect(() => JSON.parse(serialized)).not.toThrow();
    expect(JSON.parse(serialized)).toEqual(jsonData);
  });
});
