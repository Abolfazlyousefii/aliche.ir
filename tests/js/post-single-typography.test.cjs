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


test('minimal editorial refinement removes heavy decoration without changing news typography', () => {
    const refinement = css.split('Minimal editorial refinement')[1];
    assert.ok(refinement, 'Missing news-only refinement layer');
    assert.match(rule(refinement, '.single-post-page .post-hero-media'), /box-shadow:\s*none\s*;/);
    assert.match(rule(refinement, '.single-post-page .latest-news-card.sidebar-card'), /box-shadow:\s*none\s*!important\s*;/);
    assert.match(rule(refinement, '.single-post-page .post-heading-link'), /background:\s*#f2f6f8\s*;/);
    assert.match(rule(refinement, '.single-post-page .post-keywords-section .post-tag'), /border-radius:\s*5px\s*;/);
    assert.match(rule(refinement, '.single-post-page .post-keywords-section .post-tag::before'), /content:\s*none\s*;/);
    assert.match(rule(refinement, '.single-post-page .latest-news-index'), /background:\s*transparent\s*;/);
    assert.doesNotMatch(refinement, /\.single-post-page \.single-post-title\s*\{[^}]*font-size:\s*30px/);
});

test('refined mobile image preserves natural ratio and approved heading sizes', () => {
    const refinement = css.split('Minimal editorial refinement')[1];
    const mobile = refinement.slice(refinement.indexOf('@media (max-width: 767.98px)'));
    assert.match(rule(mobile, '.single-post-page .single-post-title'), /font-size:\s*25px\s*;/);
    assert.match(rule(mobile, '.single-post-page .post-keywords-section h2'), /font-size:\s*15px\s*;/);
    assert.match(rule(mobile, '.single-post-page .post-featured-img'), /height:\s*auto\s*;/);
    assert.match(rule(mobile, '.single-post-page .post-featured-img'), /aspect-ratio:\s*16\s*\/\s*10\s*;/);
});
