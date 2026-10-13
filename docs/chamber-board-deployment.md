# Chamber board / about page

The about page uses the existing editable title, excerpt, body, SEO and publication controls. The canonical `about-gorgan-guild-chamber` page automatically renders the board. Selecting the new `chamber-about` page template preserves this layout if its slug changes.

Manage members below the page editor, or use the new «رییس هیئت اتاق اصناف» shortcut in Content Management. Existing member CRUD permissions still apply. The number of members is unrestricted. Only active members appear publicly, ordered by `sort_order`, then ID. One active president appears above the remaining members. Optional bios are escaped plain text in a keyboard-accessible native dialog.

## Production installation

The migration adds two columns to `chamber_members`. Existing member names, images, bios and activity are preserved; nobody is automatically designated president. A nullable unique index also enforces one active president when requests race. No dependency or frontend build changes are required.

Run from the existing host checkout after a normal database backup:

```bash
cd /home/gorganas/laravel &&
git pull --ff-only origin main &&
php artisan migrate --path=database/migrations/2026_10_13_000001_add_president_to_chamber_members.php --force &&
mkdir -p /home/gorganas/public_html/assets/frontend/css /home/gorganas/public_html/assets/frontend/js &&
cp public/assets/frontend/css/chamber-board.css /home/gorganas/public_html/assets/frontend/css/chamber-board.css &&
cp public/assets/frontend/js/chamber-board.js /home/gorganas/public_html/assets/frontend/js/chamber-board.js &&
php artisan optimize:clear
```

Open the about page editor and add the real members, select the president, upload/select portraits, set the order and active state. To replace the president, first deactivate or uncheck the previous president. No sample people are seeded on production.

The unique-index migration has a tested reverse operation, but production rollback should restore the previous application first and keep the additional columns rather than dropping member data during an incident. Existing routes remain supported.

## Verification

29 focused tests / 357 assertions passed, including member CRUD/media regressions, permission boundaries, president uniqueness, preserving existing records across migration, custom page slugs and escaping bios. Full-suite comparison added no unsuccessful cases: both baseline and changed versions retained the same 16 failures and 2 errors. Chromium checks confirmed member/president dialogs, Escape/close behavior, white background under dark appearance, no mobile horizontal overflow and no JavaScript errors. Preview fixtures ran only in local in-memory SQLite; no production members were created.
