const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const css = fs.readFileSync(
    path.resolve(__dirname, '../../public/assets/css/post-single.css'),
    'utf8'
);
const blade = fs.readFileSync(
    path.resolve(__dirname, '../../resources/views/frontend/posts/show.blade.php'),
    'utf8'
);
const rule = (source, selector) => {
    const index = source.indexOf(selector + ' {');
    if (index < 0) return '';
    return source.slice(index).split('{', 2)[1].split('}', 1)[0];
};
const section = (start, end) => css.slice(
    css.indexOf(start),
    end ? css.indexOf(end, css.indexOf(start) + start.length) : undefined
);

test('desktop post title is 30px and tag heading is 20px', () => {
    const desktop = css.split('@media (max-width: 1199.98px)')[0];
    assert.match(rule(desktop, '.single-post-title'), /font-size:\s*30px\s*;/);
    assert.match(rule(desktop, '.post-keywords-section h2'), /font-size:\s*20px\s*;/);
});

test('tablet retains desktop title size until mobile breakpoint', () => {
    const tablet = section('@media (max-width: 1199.98px)', '@media (max-width: 991.98px)');
    assert.match(rule(tablet, '.single-post-title'), /font-size:\s*30px\s*;/);
});

test('mobile title is 25px and tag heading 15px, including small phones', () => {
    const mobile = section('@media (max-width: 767.98px)', '@media (max-width: 575.98px)');
    const small = section('@media (max-width: 575.98px)', '@media (prefers-reduced-motion: reduce)');
    assert.match(rule(mobile, '.single-post-title'), /font-size:\s*25px\s*;/);
    assert.match(rule(mobile, '.post-keywords-section h2'), /font-size:\s*15px\s*;/);
    assert.match(rule(small, '.single-post-title'), /font-size:\s*25px\s*;/);
    assert.doesNotMatch(small, /\.post-keywords-section h2\s*\{/);
});

test('styles target the news single view without renaming shared headings', () => {
    assert.match(blade, /<h1 class="single-post-title">/);
    assert.match(blade, /<section class="post-keywords-section"/);
    assert.match(blade, /<h2 id="post-keywords-title">برچسب‌های این خبر<\/h2>/);
    assert.match(blade, /assets\/css\/post-single\.css/);
});
