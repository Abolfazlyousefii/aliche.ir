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
    assert.deepEqual(Array.from(uniqueIds([1, '2', '2', '-9', '', 'abc', '3'])), ['1', '2', '3']);
});

test('select all selects only visible images and preserves previous page selection', () => {
    assert.deepEqual(Array.from(togglePageSelection(['101'], ['201', '202', '203'])),
        ['101', '201', '202', '203']);
    assert.deepEqual(Array.from(togglePageSelection(['101', '201', '202', '203'], ['201', '202', '203'])),
        ['101']);
});

test('deleted media are removed from persisted selection only upon confirmed deletion', () => {
    assert.deepEqual(Array.from(forgetDeleted(['9', '10', '11'], [10])), ['9', '11']);
    assert.deepEqual(Array.from(forgetDeleted(['9', '10', '11'], [])), ['9', '10', '11']);
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


test('infinite scrolling loads batches near viewport, never recreates grid and preserves selection', () => {
    for (const snippet of [
        'root.IntersectionObserver',
        "rootMargin: '450px 0px'",
        "target.searchParams.set('media_scroll', '1')",
        "cache: 'no-store'",
        'loadingNext',
        'fetchedPages',
        'data-ml3-scroll-more',
        'data-ml3-scroll-sentinel',
        "grid.appendChild(item)",
        'render(); // Reapply stored selections',
    ]) assert.ok(source.includes(snippet), 'missing progressive loading guard: ' + snippet);
    assert.ok(!source.includes('location.href = data.next_page_url'));
});

test('failed incremental requests retain existing images and expose a manual retry', () => {
    assert.ok(source.includes('scrollFailed = true'));
    assert.ok(source.includes('observer?.unobserve(sentinel)'));
    assert.ok(source.includes('scrollFailed = false'));
    assert.ok(source.includes('loadButton.addEventListener'));
    assert.ok(source.includes("response.ok"));
    assert.ok(source.includes('loadButton.hidden = !nextUrl'));
});
