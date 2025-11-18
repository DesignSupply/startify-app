import { fileURLToPath } from 'node:url';
import { dirname } from 'node:path';
import { Server } from '@modelcontextprotocol/sdk/server'; // 実装は後続タスクで追加
import { loadComponents } from './lib/components.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

async function main(): Promise<void> {
	// 将来的に MCP Server をここで初期化し、メソッドを登録する
	// const server = new Server({ name: 'startify-ui-mcp', version: '0.1.0' });
	// server.registerTool(...);
	// await server.start();

	console.log('[MCP] Startify-UI MCP server bootstrap ready.');
	console.log(`[MCP] entry: ${__dirname}`);
	console.log('[MCP] components:', loadComponents(process.cwd()).length);
}

main().catch((err) => {
	console.error('[MCP] Fatal error:', err);
	process.exit(1);
});

// --- list_components tool (skeleton) ---
// NOTE: The MCP SDK's Server class does not expose `registerTool` directly.
// Tools are typically declared during initialization and handled via a tools/call route.
// We'll wire this properly in the subsequent task when we add request routing.
// const server = new Server({ name: 'startify-ui-mcp', version: '0.1.0' });
// // Pseudo wiring (to be replaced with proper tools/call handler):
// // server.on('tools/call', async ({ name, arguments: args }) => {
// //  if (name === 'list_components') {
// //    const components = loadComponents(process.cwd());
// //    return { ok: true, data: components };
// //  }
// // });
