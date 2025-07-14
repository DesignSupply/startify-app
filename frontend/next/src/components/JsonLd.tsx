'use client';

export type propsType = {
  jsonLd: {
    '@type': string;
    position: number;
    item: {
      '@id': string;
      name: string;
    };
  }[];
};

export default function JsonLd(props: propsType) {
  const jsonData = {
    '@context': 'http://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: props.jsonLd
  };
  
  return (
    <>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonData) }} />
    </>
  );
}
