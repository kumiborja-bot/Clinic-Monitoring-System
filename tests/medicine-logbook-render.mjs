import assert from 'node:assert/strict';
import { readFile, writeFile, unlink } from 'node:fs/promises';
import { parse, compileScript } from '@vue/compiler-sfc';
import { createSSRApp } from 'vue';
import { renderToString } from '@vue/server-renderer';

// Render the real page with the paginated data returned by MedicineController.
// Stub only the surrounding layout, navigation, and chart (which mounts in-browser).
const source = await readFile(new URL('../resources/js/Pages/Medicines/Index.vue', import.meta.url), 'utf8');
const { descriptor } = parse(source);
let code = compileScript(descriptor, {
    id: 'medicine-logbook-test',
    inlineTemplate: true,
    templateOptions: { ssr: true },
}).content;
code = code
    .replace(/import AuthenticatedLayout from [^;]+;/, 'const AuthenticatedLayout = { template: "<main><slot /></main>" };')
    .replace(/import \{ Head, Link, router \} from [^;]+;/, 'const Head = { render: () => null }; const Link = { template: "<a><slot /></a>" }; const router = {};')
    .replace(/import \{ Chart, registerables \} from [^;]+;/, 'const Chart = { register() {} }; const registerables = [];')
    .replace(/import \{ formatDate \} from [^;]+;/, 'const formatDate = value => value;');
const generated = new URL('./.medicine-logbook-render.generated.mjs', import.meta.url);
try {
    await writeFile(generated, code);
    const { default: Page } = await import(generated.href);
    for (const populated of [false, true]) {
        const app = createSSRApp(Page, {
            medicines: { data: populated ? [{ id: 1, name: 'Test Medicine', supplier: 'Test Supplier', stock_quantity: 10, unit: 'tablet' }] : [], links: [] },
            suppliers: ['Test Supplier'], topDispensed: [], filters: {},
        });
        app.config.globalProperties.route = name => `/${name}`;
        const html = await renderToString(app);
        assert.match(html, /All Suppliers/);
        assert.doesNotMatch(html, /type="checkbox"/);
        assert.doesNotMatch(html, /Delete Selected/);
        assert.doesNotMatch(html, /Delete All Medicines/);
        assert.match(html, /Medicine inventory/);
        assert.match(html, populated ? /Test Medicine/ : /No medicines found/);
        if (populated) assert.match(html, /Stock Card/);
    }
    console.log('Medicine logbook renders successfully with empty and populated inventory.');
} finally {
    await unlink(generated).catch(() => {});
}
