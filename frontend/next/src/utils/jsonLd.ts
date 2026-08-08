export type JsonLdListItem = {
  '@type': string;
  position: number;
  item: {
    '@id': string;
    name: string;
  };
};

export function buildBreadcrumbListJsonLd(itemListElement: JsonLdListItem[]) {
  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement,
  };
}

export function serializeJsonLd(jsonData: unknown): string {
  return JSON.stringify(jsonData).replace(/</g, '\\u003c');
}
