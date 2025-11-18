import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';

export type Component = {
	id: string;
	name: string;
	category: string;
	variants?: string[];
	description?: string;
};

export function loadComponents(baseDir: string = process.cwd()): Component[] {
	const file = resolve(baseDir, 'config', 'components.json');
	const raw = readFileSync(file, 'utf-8');
	const data = JSON.parse(raw);
	if (!Array.isArray(data)) {
		throw new Error('components.json must be an array');
	}
	return data as Component[];
}
