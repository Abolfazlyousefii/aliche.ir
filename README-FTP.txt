Core AJAX Fix — FTP deployment

Production files:
1) public/assets/js/ajax-core.js
2) resources/views/frontend/partials/scripts.blade.php
3) resources/views/frontend/layouts/app.blade.php

No database migration is required.
No Model/Controller/route changes are required.

After upload:
- Clear Laravel caches if available.
- Hard refresh once.
- Test:
  * Homepage tabs (unions / tourism / multimedia)
  * Homepage latest-news pagination
  * /guilds type filters
  * /guilds search
  * /guilds pagination
  * Browser Back/Forward after changing /guilds filters/pages

Security:
- No eval/new Function/unsafe-eval was added.
- GET AJAX requests use same-origin credentials and X-Requested-With.
- If AJAX fails, normal Laravel links remain the fallback.
