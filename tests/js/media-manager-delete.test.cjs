const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(
    path.resolve(__dirname, '../../public/assets/admin/js/media-manager-v2.js'),
    'utf8'
);
const sandbox = { module: { exports: {} }, globalThis: {} };
vm.runInNewContext(source, sandbox, { filename: 'media-manager-v2.js' });

const { forgetDeletedIds, toggleVisibleDeleteIds } = sandbox.module.exports;

test('successful deletion evicts only confirmed IDs, including selection and cache', () => {
    const selectedIds = new Set(['11', '12', '13']);
    const deleteIds = new Set(['11', '12']);
    const items = new Map([['11', { title: 'One' }], ['12', { title: 'Two' }], ['13', { title: 'Three' }]]);
    const selected = new Map(items);
    const generic = { ids: ['11', '13'] };
    forgetDeletedIds([11, '12'], { activeIds: selectedIds, deleteIds, items, selected, genericSelections: new Set([generic]) });
    assert.deepEqual(generic.ids, ['13']);

    assert.deepEqual([...selectedIds], ['13']);
    assert.deepEqual([...deleteIds], []);
    assert.deepEqual([...items.keys()], ['13']);
    assert.deepEqual([...selected.keys()], ['13']);
});

test('blocked or failed deletes never remove local selections', () => {
    const selectedIds = new Set(['8', '9']);
    const items = new Map([['8', { title: 'Used' }], ['9', { title: 'Failed' }]]);
    forgetDeletedIds([], { activeIds: selectedIds, items });

    assert.deepEqual([...selectedIds], ['8', '9']);
    assert.deepEqual([...items.keys()], ['8', '9']);
});

test('delete controls use distinct selection mode and explicit confirmation', () => {
    for (const pattern of [
        'adminMediaCanDelete', 'data-mm2-manage-toggle', 'data-mm2-bulk-delete',
        'data-mm2-confirm-delete', 'data-mm2-delete-id', 'pendingDeleteIds',
        'X-CSRF-TOKEN', 'adminMediaBulkDeleteUrl',
    ]) assert.ok(source.includes(pattern), 'missing ' + pattern);
});


test('select-all marks loaded images and keeps marks from other pages', () => {
    const marked = new Set(['101']);
    toggleVisibleDeleteIds(marked, ['201', '202', '203']);
    assert.deepEqual([...marked], ['101', '201', '202', '203']);
    toggleVisibleDeleteIds(marked, ['201', '202', '203']);
    assert.deepEqual([...marked], ['101']);
    toggleVisibleDeleteIds(marked, []);
    assert.deepEqual([...marked], ['101']);
});

test('bulk selection controls and small photo-scoped delete icon are present', () => {
    const css = fs.readFileSync(
        path.resolve(__dirname, '../../public/assets/admin/css/media-manager-v2.css'),
        'utf8'
    );
    for (const selector of [
        'data-mm2-select-visible', 'data-mm2-clear-delete', 'data-mm2-delete-toolbar',
        'is-managing', 'mm2-tile-delete', 'toggleVisibleDeleteIds',
    ]) assert.ok(source.includes(selector), 'missing UI control ' + selector);
    assert.match(css, /\.mm2-grid > \.mm2-tile-container\s*\{[^}]*position:\s*relative;/);
    assert.match(css, /\.mm2-grid \.mm2-tile-container > \.mm2-tile-delete\s*\{[^}]*position:\s*absolute;/);
    assert.match(css, /\.mm2-grid\.is-managing \.mm2-tile-delete\s*\{\s*display:\s*none/);
});
