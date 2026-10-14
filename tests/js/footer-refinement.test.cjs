const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const root = path.resolve(__dirname, '../..');
const css = fs.readFileSync(path.join(root, 'public/assets/css/footer-refinement.css'), 'utf8');
const mirror = fs.readFileSync(path.join(root, 'public_html/assets/css/footer-refinement.css'), 'utf8');
const blade = fs.readFileSync(path.join(root, 'resources/views/frontend/partials/footer.blade.php'), 'utf8');
const styles = fs.readFileSync(path.join(root, 'resources/views/frontend/partials/styles.blade.php'), 'utf8');
const footerScript = fs.readFileSync(path.join(root, 'public_html/assets/js/main.js'), 'utf8');

const section = (selector) => {
    const start = css.indexOf(selector + ' {');
    if (start < 0) return '';
    const brace = css.indexOf('{', start);
    return css.slice(brace + 1, css.indexOf('}', brace));
};

test('footer CSS ships identically in the source and public web-root mirrors', () => {
    assert.equal(css, mirror);
    assert.ok(styles.includes("assets/css/footer-refinement.css"));
});

test('desktop quick-links use a two-column grid with compact spacing', () => {
    assert.match(section('.site-footer .footer-accordion-panel ul.footer-quick-links'), /display:\s*grid\s*;/);
    assert.match(section('.site-footer .footer-accordion-panel ul.footer-quick-links'), /grid-template-columns:\s*repeat\(2,\s*minmax\(0,\s*1fr\)\)/);
    assert.match(section('.site-footer .footer-quick-links li a'), /min-height:\s*30px\s*;/);
    assert.match(blade, /<ul class="footer-quick-links">/);
});

test('logo is inside a white framed identity and retains dynamic site logo fallback', () => {
    assert.ok(blade.includes('footer-brand-identity'));
    assert.ok(blade.includes('footer-logo-frame'));
    assert.ok(blade.includes('footer-brand-copy'));
    assert.ok(blade.includes("image_url("));
    assert.match(section('.site-footer .footer-brand-col .footer-logo-frame'), /background:\s*#fff\s*;/);
    assert.match(section('.site-footer .footer-brand-col .footer-logo-frame img'), /object-fit:\s*contain\s*;/);
});

test('mobile keeps accessible accordions and two columns until narrow phones', () => {
    assert.match(css, /@media \(max-width:\s*767\.98px\)/);
    assert.match(css, /@media \(max-width:\s*389\.98px\)/);
    assert.match(css, /\.site-footer \.footer-accordion-panel\[hidden\]\s*\{\s*display:\s*none\s*!important;/);
    assert.match(footerScript, /footerMobile = window\.matchMedia\('\(max-width: 767\.98px\)'\)/);
    assert.ok(blade.includes('aria-expanded="false"'));
    assert.ok(blade.includes('data-footer-accordion="quick-links"'));
});

test('footer refinement does not change site-wide navigation or assets', () => {
    assert.doesNotMatch(css, /(?:^|\n)\s*(?:\.main-navbar|\.site-header|\.header-logo)\s*\{/);
    assert.doesNotMatch(css, /!important/);
});
