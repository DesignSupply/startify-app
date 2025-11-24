type LogLevelName = 'debug' | 'info' | 'warn' | 'error';

const levelOrder: Record<LogLevelName, number> = {
	debug: 10,
	info: 20,
	warn: 30,
	error: 40,
};

export function getLogLevelFromEnv(): LogLevelName {
	const raw = (process.env.STARTIFY_LOG_LEVEL || '').toLowerCase();
	if (raw === 'debug' || raw === 'info' || raw === 'warn' || raw === 'error') {
		return raw;
	}
	return 'info';
}

export function createLogger(prefix: string, level: LogLevelName = getLogLevelFromEnv()) {
	function enabled(target: LogLevelName): boolean {
		return levelOrder[target] >= levelOrder[level];
	}
	const tag = prefix ? `${prefix}` : '';
	return {
		debug: (...args: unknown[]) => { if (enabled('debug')) console.debug(tag, ...args); },
		info: (...args: unknown[]) => { if (enabled('info')) console.info(tag, ...args); },
		warn: (...args: unknown[]) => { if (enabled('warn')) console.warn(tag, ...args); },
		error: (...args: unknown[]) => { console.error(tag, ...args); },
		withPrefix: (p: string) => createLogger(`${tag} ${p}`.trim(), level),
	};
}


