import type { DesignTokens } from './tokens.js';
import { loadComponents } from './components.js';

export type GenerateComponentInput = {
	id: string;
	element?: string;
	options?: Record<string, string>;
	text?: string;
	props?: Record<string, unknown>; // e.g. { disabled: true, formType: 'submit' }
};

export type GeneratePageInput = {
	title?: string;
	components: GenerateComponentInput[];
};

export function generatePage(input: GeneratePageInput, tokens: DesignTokens): string {
	const parts: string[] = [];
	const defs = loadComponents(process.cwd()) as any[];

	if (input.title) {
		parts.push(`<h1>${escapeHtml(String(input.title))}</h1>`);
	}

	for (const c of input.components ?? []) {
		const def = defs.find((d) => d.id === c.id);
		if (!def) continue;

		const element = c.element ?? def?.defaults?.element ?? 'div';

		// Classes
		const classNames: string[] = [];
		if (def.baseClass) classNames.push(def.baseClass);
		const options = c.options ?? {};
		for (const [axis, value] of Object.entries(options)) {
			const entry = def?.variants?.[axis]?.[value];
			if (entry?.classNames) classNames.push(...entry.classNames);
		}
		if (c.props?.['disabled'] && def?.props?.disabled?.classNames) {
			classNames.push(...def.props.disabled.classNames);
		}

		// Attributes (last write wins)
		const attrs: Record<string, string | number | boolean> = {};
		if (def.attributes) Object.assign(attrs, def.attributes);
		if (def.elementAttributes?.[element]) Object.assign(attrs, def.elementAttributes[element]);
		if (c.props?.['disabled'] && def?.props?.disabled?.elementAttributes?.[element]) {
			Object.assign(attrs, def.props.disabled.elementAttributes[element]);
		}
		const formType = String((c.props as any)?.formType ?? def?.defaults?.formType ?? '');
		if (formType && def?.props?.formType?.[formType]?.elementAttributes?.[element]) {
			Object.assign(attrs, def.props.formType[formType].elementAttributes[element]);
		}

		const label = String(c.text ?? '');
		const attrsStr = renderAttrs(attrs);
		const classStr = classNames.join(' ').trim();
		const classAttr = classStr.length > 0 ? ` class="${classStr}"` : '';
		const space = attrsStr ? ' ' : '';

		parts.push(`<${element}${classAttr}${space}${attrsStr}>${escapeHtml(label)}</${element}>`);
	}

	return parts.join('\n');
}

function renderAttrs(attrs: Record<string, string | number | boolean>): string {
	const parts: string[] = [];
	for (const [key, value] of Object.entries(attrs)) {
		if (typeof value === 'boolean') {
			if (value) parts.push(key);
		} else {
			parts.push(`${key}="${String(value)}"`);
		}
	}
	return parts.join(' ');
}

function escapeHtml(s: string): string {
	return s.replace(/[&<>"']/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch] as string));
}
