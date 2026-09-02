import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { basename, resolve } from 'node:path';
import { parse as parseYaml } from 'yaml';

const EXPECTED_FILES = [
  'color-scheme.yaml',
  'size-scale.yaml',
  'typography.yaml',
  'grid-system.yaml',
  'dropshadow.yaml',
  'corner-style.yaml',
  'easing.yaml',
];

const defaultTokensDir = resolve(import.meta.dirname, '../../_design-tokens');
const tokensDir = resolve(process.argv[2] ?? defaultTokensDir);

/** @type {{ file: string; message: string }[]} */
const errors = [];

/**
 * @param {string} file
 * @param {string} message
 */
function addError(file, message) {
  errors.push({ file, message });
}

/**
 * @param {string} file
 */
function validateTokenFile(file) {
  const filePath = resolve(tokensDir, file);

  let content;
  try {
    content = readFileSync(filePath, 'utf8');
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    addError(file, `Failed to read file: ${message}`);
    return;
  }

  let doc;
  try {
    doc = parseYaml(content);
  } catch (error) {
    const message = error instanceof Error ? error.message : String(error);
    addError(file, `YAML parse error: ${message}`);
    return;
  }

  if (doc === null || typeof doc !== 'object' || Array.isArray(doc)) {
    addError(file, 'Root must be an object');
    return;
  }

  const tokenName = doc['token-name'];
  if (typeof tokenName !== 'string' || tokenName.trim() === '') {
    addError(file, 'token-name must be a non-empty string');
    return;
  }

  const expectedName = basename(file, '.yaml');
  if (tokenName !== expectedName) {
    addError(file, `token-name "${tokenName}" does not match file name "${expectedName}"`);
    return;
  }

  const tokenValue = doc['token-value'];
  if (tokenValue === null || typeof tokenValue !== 'object' || Array.isArray(tokenValue)) {
    addError(file, 'token-value must be a non-array object');
    return;
  }

  if (Object.keys(tokenValue).length === 0) {
    addError(file, 'token-value must not be empty');
  }
}

if (!existsSync(tokensDir)) {
  addError(tokensDir, 'Token directory does not exist');
} else {
  const discoveredFiles = readdirSync(tokensDir)
    .filter((name) => name.endsWith('.yaml'))
    .sort();

  for (const file of EXPECTED_FILES) {
    if (!discoveredFiles.includes(file)) {
      addError(file, 'Expected token file is missing');
    }
  }

  for (const file of discoveredFiles) {
    if (!EXPECTED_FILES.includes(file)) {
      addError(file, 'Unexpected token file is present');
    }
  }

  for (const file of discoveredFiles) {
    validateTokenFile(file);
  }
}

if (errors.length > 0) {
  for (const { file, message } of errors) {
    console.error(`Error in ${file}: ${message}`);
  }
  process.exit(1);
}

const validatedCount = existsSync(tokensDir)
  ? readdirSync(tokensDir).filter((name) => name.endsWith('.yaml')).length
  : 0;

console.log(`Successfully validated ${validatedCount} design token file(s).`);
process.exit(0);
