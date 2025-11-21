import { readdirSync, readFileSync, existsSync } from 'node:fs';
import { resolve, basename, extname } from 'node:path';
import { parse } from 'yaml';

export type DesignTokens = Record<string, unknown>;

function findTokensDir(baseDir: string): string {
	const direct = resolve(baseDir, 'design-tokens');
	if (existsSync(direct)) return direct;
	const parent = resolve(baseDir, '..', 'design-tokens');
	if (existsSync(parent)) return parent;
	throw new Error(`design-tokens directory not found. Tried: ${direct} and ${parent}`);
}

export function loadDesignTokens(cwd: string = process.cwd()): DesignTokens {
	const tokensDir = findTokensDir(cwd);
	const files = readdirSync(tokensDir)
		.filter((f) => extname(f) === '.yaml')
		.sort();
	if (files.length === 0) {
		throw new Error(`no *.yaml found in: ${tokensDir}`);
	}
	const result: DesignTokens = {};
	for (const file of files) {
		const name = basename(file, '.yaml');
		const raw = readFileSync(resolve(tokensDir, file), 'utf-8');
		const data = parse(raw);
		result[name] = data;
	}
	return result;
}
