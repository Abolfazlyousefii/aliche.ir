const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(
    path.resolve(__dirname, '../../public/assets/admin/js/media-library-v3.js'),
    'utf8'
);
const sandbox = { module: { exports: {} }, globalThis: {} };
vm.runInNewContext(source, sandbox, { filename: 'media-library-v3.js' });
const { uniqueIds, togglePageSelection, forgetDeleted } = sandbox.module.exports;

test('deduplicates validated media IDs without accepting arbitrary payloads', () => {
    assert.deepEqual(uniqueIds([1, '2', '2', '-9', '', 'abc', '3']), ['1', '2', '3']);
});

test('select all selects only visible images and preserves previous page selection', () => {
    assert.deepEqual(togglePageSelection(['101'], ['201', '202', '203']),
        ['101', '201', '202', '203']);
    assert.deepEqual(togglePageSelection(['101', '201', '202', '203'], ['201', '202', '203']),
        ['101']);
});

test('deleted media are removed from persisted selection only upon confirmed deletion', () => {
    assert.deepEqual(forgetDeleted(['9', '10', '11'], [10]), ['9', '11']);
    assert.deepEqual(forgetDeleted(['9', '10', '11'], []), ['9', '10', '11']);
});

test('permanent delete uses server-authorized routes and CSRF and never retries automatically', () => {
    for (const snippet of [
        'adminBatchMediaUpload.createQueue',
        'data-ml3-delete-one',
        'data-ml3-delete-selected',
        'data-ml3-confirm-delete',
        "method: 'DELETE'",
        "'X-CSRF-TOKEN': csrf",
        'page.dataset.deleteManyUrl',
        'tally.blocked',
        'tally.failed',
        'sessionStorage',
    ]) assert.ok(source.includes(snippet), 'missing "' + snippet + '"');
    assert.ok(!source.includes('setInterval('));
});
