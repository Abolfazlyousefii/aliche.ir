const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

// The application uses plain browser scripts while the Vite package enables
// ESM for .js. Evaluate this browser script as classic JS for Node unit tests.
const sandbox = { module: { exports: {} }, globalThis: {} };
vm.runInNewContext(
    fs.readFileSync(path.resolve(__dirname, '../../public/assets/admin/js/media-bulk-upload.js'), 'utf8'),
    sandbox,
    { filename: 'media-bulk-upload.js' }
);
const { uploadFiles, messageFrom } = sandbox.module.exports;

const photo = (id, bytes = 1024) => ({ name: `photo-${id}.jpg`, size: bytes });

test('one selection of sixty images is sent as sixty safe sequential media requests', async () => {
    let inflight = 0;
    let maxInflight = 0;
    const selected = [];
    const progress = [];
    const files = Array.from({ length: 60 }, (_, i) => photo(i + 1));

    const result = await uploadFiles(files, {
        sendOne: async (file) => {
            inflight++;
            maxInflight = Math.max(maxInflight, inflight);
            await Promise.resolve();
            inflight--;
            return { id: Number(file.name.match(/\d+/)[0]), url: '/storage/' + file.name };
        },
        onUploaded: (media) => selected.push(media.id),
        onProgress: (status) => progress.push(status),
    });

    assert.equal(maxInflight, 1);
    assert.equal(result.uploaded, 60);
    assert.equal(result.failures.length, 0);
    assert.equal(selected.length, 60);
    assert.deepEqual(selected, Array.from({ length: 60 }, (_, i) => i + 1));
    assert.equal(progress[59].total, 60);
    assert.equal(progress[59].finished, 60);
    assert.equal(progress[59].uploaded, 60);
});

test('retry re-sends only failed files and preserves all successful media ids', async () => {
    const calls = [];
    let firstTry = true;
    const saved = new Set();
    const opts = {
        sendOne: async (file) => {
            calls.push(file.name);
            if (file.name === 'photo-2.jpg' && firstTry) throw new Error('Network interrupted');
            return { id: file.name, url: '/' + file.name };
        },
        onUploaded: (item) => saved.add(item.id),
    };

    const first = await uploadFiles([photo(1), photo(2), photo(3)], opts);
    assert.equal(first.uploaded, 2);
    assert.equal(first.failures.length, 1);
    assert.equal(first.failures[0].file.name, 'photo-2.jpg');
    assert.deepEqual([...saved], ['photo-1.jpg', 'photo-3.jpg']);

    firstTry = false;
    const retry = await uploadFiles(first.failures.map((failure) => failure.file), opts);
    assert.equal(retry.uploaded, 1);
    assert.equal(retry.failures.length, 0);
    assert.equal(saved.size, 3);
    assert.deepEqual(calls, ['photo-1.jpg', 'photo-2.jpg', 'photo-3.jpg', 'photo-2.jpg']);
});

test('invalid file types, empty files and images over five MB are rejected before upload', async () => {
    let requests = 0;
    const result = await uploadFiles([
        { name: 'script.php', size: 123 },
        { name: 'huge.jpeg', size: 5 * 1024 * 1024 + 1 },
        { name: 'empty.jpg', size: 0 },
        photo(4),
    ], { sendOne: async () => { requests++; return { id: 4 }; } });
    assert.equal(requests, 1);
    assert.equal(result.uploaded, 1);
    assert.equal(result.failures.length, 3);
    assert.match(result.failures[1].message, /۵ مگابایت/);
});

test('server validation messages for failed uploads are user-readable', () => {
    assert.equal(messageFrom({ errors: { 'files.0': ['فایل تصویر معتبر نیست.'] } }), 'فایل تصویر معتبر نیست.');
    assert.equal(messageFrom({ message: 'آپلود رد شد' }), 'آپلود رد شد');
});


test('transient server failure retries only the current image and reports one final success', async () => {
    let attempts = 0;
    const events = [];
    const result = await uploadFiles([photo(7)], {
        sendOne: async () => {
            attempts++;
            if (attempts < 3) {
                const error = new Error('Temporary upstream failure');
                error.retryable = true;
                throw error;
            }
            return { id: 7, url: '/photo7.jpg' };
        },
        delay: async () => {},
        onRetry: (data) => events.push(data.attempt),
    });
    assert.equal(attempts, 3);
    assert.equal(result.uploaded, 1);
    assert.equal(result.failures.length, 0);
    assert.deepEqual(events, [1, 2]);
});

test('a UI selection callback throwing does not misreport a server success or reupload it', async () => {
    let requests = 0;
    const result = await uploadFiles([photo(9)], {
        sendOne: async () => {
            requests++;
            return { id: 9, url: '/photo9.jpg' };
        },
        onUploaded: () => { throw new Error('UI render failed'); },
    });
    assert.equal(requests, 1);
    assert.equal(result.uploaded, 1);
    assert.equal(result.failures.length, 0);
    assert.equal(result.uiWarnings.length, 1);
});

test('validation failures are not automatically retried', async () => {
    let requests = 0;
    const result = await uploadFiles([photo(12)], {
        sendOne: async () => {
            requests++;
            const error = new Error('422 invalid image');
            error.retryable = false;
            throw error;
        },
    });
    assert.equal(requests, 1);
    assert.equal(result.uploaded, 0);
    assert.equal(result.failures.length, 1);
});
