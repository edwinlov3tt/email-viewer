# Repository Guidelines

## Project Structure & Module Organization
- `index.html`: UI entry point.
- `js/app.js`: Frontend logic for uploads, list, and rendering.
- `php/`: Endpoints and helpers (`config.php`, `parse-email.php`, `download-attachment.php`, `cleanup.php`, etc.).
- `uploads/`: Temp storage for emails/attachments (cleaned by `cleanup.php`).
- `composer.json` + `vendor/`: PHP deps (MailMimeParser, hfig/mapi, HTMLPurifier).
- Classes (if introduced) live in `php/classes/` under the `EmailViewer\\` namespace (PSR‑4).

## Build, Test, and Development Commands
- Install deps: `composer install --no-dev --optimize-autoloader`.
- Run locally: `php -S localhost:8080 -t .` then open `http://localhost:8080/index.html`.
- Parse via API (example): `curl -F "email=@sample.eml" http://localhost:8080/php/parse-email.php`.
- Cleanup temp files: `php php/cleanup.php` (cron-friendly).

## Coding Style & Naming Conventions
- PHP: PSR‑12 style, 4‑space indent, UTF‑8. Use camelCase for functions (e.g., `validateUploadedFile`).
- Filenames: action‑based kebab‑case for endpoints (e.g., `parse-email.php`, `download-attachment.php`). Prefix ad‑hoc checks with `test-`.
- Namespacing: new classes under `EmailViewer\\` and `php/classes/` (autoloaded via Composer).
- JS: camelCase, avoid globals, keep logic in `js/app.js`.

## Testing Guidelines
- Framework: none yet; use lightweight PHP scripts in `php/` (e.g., `test-basic.php`) and `curl` calls.
- Cover both `.eml` and `.msg`, attachments, and HTML sanitization paths.
- Example: `curl -F "email=@fixtures/example.msg" http://localhost:8080/php/parse-email.php`.

## Commit & Pull Request Guidelines
- Commits: follow Conventional Commits (`feat:`, `fix:`, `chore:`, `docs:`). Keep messages imperative and scoped.
- PRs: include clear description, linked issue, steps to verify, sample files used, screenshots/GIFs of UI changes, and risk/rollback notes.

## Security & Configuration Tips
- Never commit `.env` or sample secrets. `.htaccess` blocks sensitive files; keep it intact.
- Always `require_once 'config.php';` in new endpoints and use `validateUploadedFile`, `sanitizeFilename`, and `UPLOAD_DIR` helpers.
- Respect `MAX_UPLOAD_SIZE` and `ALLOWED_EXTENSIONS`. Sanitize all HTML through HTMLPurifier.
- Ensure `uploads/` is writable on deploy and cleaned via cron.

## Agent-Specific Instructions
- Maintain JSON response shape used by the UI; avoid breaking fields without coordinating frontend updates.
- Prefer early returns with appropriate HTTP codes. Log server errors; never expose stack traces in responses.
- After adding classes, run `composer dump-autoload`.
