import { readFileSync } from 'node:fs';
import { resolve, isAbsolute } from 'node:path';
import { parse } from 'yaml';

export type Component = {
	id: string;
	name: string;
	category: string;
	variants?: string[];
	description?: string;
};

export function loadComponents(baseDir: string = process.cwd()): Component[] {
	// 環境変数で上書き可能: STARTIFY_COMPONENTS_FILE
	const overridePath = process.env.STARTIFY_COMPONENTS_FILE;
	const file = overridePath
		? (isAbsolute(overridePath) ? overridePath : resolve(baseDir, overridePath))
		: resolve(baseDir, 'config', 'components.yaml');
	const raw = readFileSync(file, 'utf-8');
	const data = parse(raw);
	if (!Array.isArray(data)) {
		throw new Error('components.yaml must be an array');
	}
	return data as Component[];
}
