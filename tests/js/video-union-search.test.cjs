const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const script = fs.readFileSync(
    path.resolve(__dirname, '../../public/assets/admin/js/video-union-search.js'),
    'utf8'
);
const sandbox = { module: { exports: {} }, globalThis: {} };
vm.runInNewContext(script, sandbox, { filename: 'video-union-search.js' });

const { normalize, matchOptions } = sandbox.module.exports;
const entries = [
    { index: 0, value: '', label: 'ویدیوی عمومی (بدون اتحادیه)' },
    { index: 1, value: '12', label: 'اتحاديه آرايشگران مردانه گرگان' },
    { index: 2, value: '17', label: 'اتحادیه آرایشگران زنانه گرگان' },
    { index: 3, value: '45', label: 'اتحادیه مصالح ساختمانی گرگان' },
    { index: 4, value: '49', label: 'اتحادیه فروشندگان مواد غذایی' },
];

test('search normalizes Persian/Arabic forms and half-spaces', () => {
    assert.equal(normalize('  اتحاديه   كسب\u200cوكار  '), 'اتحادیه کسب وکار');
    assert.equal(normalize('آرايشگران'), 'آرایشگران');
    assert.equal(normalize('كَسب'), 'کسب');
});

test('live search narrows union list, retains native IDs, and prioritizes prefix', () => {
    const result = matchOptions(entries, 'آرایشگران');
    assert.equal(result.total, 2);
    assert.deepEqual(Array.from(result.matches, (row) => row.value), ['12', '17']);
    assert.equal(matchOptions(entries, 'مصالح').matches[0].value, '45');
    assert.equal(matchOptions(entries, 'بدون اتحادیه').matches[0].value, '');
    assert.equal(matchOptions(entries, 'unknown').total, 0);
});

test('empty query keeps all choices and a bounded list requests a narrower search', () => {
    const result = matchOptions(entries, '', 2);
    assert.equal(result.total, 5);
    assert.deepEqual(Array.from(result.matches, (row) => row.value), ['', '12']);
});

test('combobox keeps original form select and accessible keyboard support', () => {
    for (const snippet of [
        'data-video-union-native',
        "native.value = entry.value",
        "native.dispatchEvent(new Event('change'",
        "role', 'combobox'",
        "role', 'listbox'",
        "aria-activedescendant",
        "aria-expanded",
        "aria-live",
        "event.key === 'ArrowDown'",
        "event.key === 'ArrowUp'",
        "event.key === 'Escape'",
        "event.key === 'Enter'",
        "event.key === 'Tab'",
        "event.preventDefault(); // Never accidentally submit",
        "input.addEventListener('input'",
        "querying ? input.value : ''",
    ]) assert.ok(script.includes(snippet), 'Missing combobox feature: ' + snippet);
});
