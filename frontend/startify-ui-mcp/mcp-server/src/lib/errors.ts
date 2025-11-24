export type ToolErrorType = 'validation' | 'internal' | 'dependency';

export function classifyError(e: unknown): ToolErrorType {
	if (e && typeof e === 'object' && 'name' in (e as any)) {
		const name = String((e as any).name || '');
		if (name.toLowerCase().includes('validation')) return 'validation';
	}
	if (e && typeof e === 'object' && 'code' in (e as any)) {
		const code = String((e as any).code || '');
		// 一般的なファイル/ネットワーク系
		if (['ENOENT', 'EACCES', 'ECONNREFUSED', 'ECONNRESET', 'ETIMEDOUT'].includes(code)) {
			return 'dependency';
		}
	}
	return 'internal';
}

export function formatToolError(e: unknown, type?: ToolErrorType) {
	const t = type ?? classifyError(e);
	const message =
		e instanceof Error ? e.message :
		typeof e === 'string' ? e :
		'Unknown error';
	return {
		ok: false as const,
		error: {
			type: t,
			message,
		},
	};
}


