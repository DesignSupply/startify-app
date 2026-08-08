import { buildBreadcrumbListJsonLd, serializeJsonLd, type JsonLdListItem } from '@/utils/jsonLd';

export type propsType = {
  jsonLd: JsonLdListItem[];
};

export default function JsonLd(props: propsType) {
  const jsonData = buildBreadcrumbListJsonLd(props.jsonLd);

  return (
    <script
      type="application/ld+json"
      dangerouslySetInnerHTML={{ __html: serializeJsonLd(jsonData) }}
    />
  );
}
