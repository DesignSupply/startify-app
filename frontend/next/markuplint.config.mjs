/** @type {import('@markuplint/ml-config').Config} */
export default {
  extends: [
    'markuplint:recommended'
  ],
  parser: {
    '\\.jsx$': '@markuplint/jsx-parser',
    '\\.tsx$': '@markuplint/jsx-parser'
  },
  specs: {
    '\\.jsx$': '@markuplint/react-spec',
    '\\.tsx$': '@markuplint/react-spec'
  },
  rules: {
    'character-reference': false,
    'attr-duplication': true,
    'deprecated-element': true,
    'required-attr': true,
    'landmark-roles': true,
    'required-h1': false,
    'no-refer-to-non-existent-id': true,
    'use-list': true,
    'no-empty-palpable-content': false,
    'no-hard-code-id': false,
    'wai-aria': true,
    'ineffective-attr': true
  },
  nodeRules: [
    {
      selector: 'img',
      rules: {
        'required-attr': ['alt']
      }
    }
  ]
}; 