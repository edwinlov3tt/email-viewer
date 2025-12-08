# Architectural Decisions

Record of significant technical decisions and their context.

**Last Updated**: December 2025

---

## Decisions

### Nixpacks Configuration to Force Python Build
- **Date**: 2025-12-08
- **Status**: Accepted

#### Context
Railway deployment was failing because Nixpacks detected PHP from the legacy `vendor/` directory (leftover from PHP-to-Flask migration) instead of Python.

#### Decision
Create `nixpacks.toml` to explicitly configure Python 3.13 build, overriding automatic detection.

```toml
[phases.setup]
nixPkgs = ["python313", "gcc"]

[phases.install]
cmds = ["pip install -r requirements.txt"]

[start]
cmd = "gunicorn app:app --bind 0.0.0.0:$PORT"
```

#### Alternatives Considered
1. **Remove vendor/ directory**: Would work but requires git history cleanup
2. **Add .railwayignore**: Railway doesn't support this well
3. **Dockerfile**: More complex, nixpacks.toml is simpler

#### Consequences
- **Positive**: Deployment works correctly, explicit configuration
- **Negative**: Extra config file to maintain
- **Neutral**: Should eventually remove vendor/ directory anyway

---

### Flask over Node.js/Express for Backend
- **Date**: 2024-10-16
- **Status**: Accepted

#### Context
Needed a backend to parse email files (.eml and .msg formats). Both Python and Node.js were viable options.

#### Decision
Use Python Flask for the backend with extract-msg library for MSG parsing.

#### Alternatives Considered
1. **Node.js/Express**: Rejected because MSG parsing libraries are more mature in Python
2. **PHP**: Initially used, but migrated away due to hosting complexity and library limitations

#### Consequences
- **Positive**: Excellent MSG parsing with extract-msg, built-in email library for EML, BeautifulSoup for HTML sanitization
- **Negative**: Python hosting slightly more complex than Node.js on some platforms
- **Neutral**: Python 3.13 requirement may limit some hosting options

---

### In-Memory Storage over Database
- **Date**: 2024-10-16
- **Status**: Accepted (with known limitations)

#### Context
Needed to store parsed email data for attachment downloads and nested email viewing.

#### Decision
Use Python dictionary (`email_storage = {}`) for storing parsed email data.

#### Alternatives Considered
1. **SQLite**: Would provide persistence but adds complexity
2. **Redis**: Good for caching but requires additional infrastructure
3. **PostgreSQL**: Full database but overkill for current use case

#### Consequences
- **Positive**: Simple implementation, fast access, no additional dependencies
- **Negative**: Data lost on server restart, not scalable for multiple workers
- **Neutral**: Database schema is documented in `.env.example` for future migration

---

### Vanilla JavaScript over React/Vue
- **Date**: 2024-10-16
- **Status**: Accepted

#### Context
Frontend needed to handle file uploads, display emails, and manage modals/popups.

#### Decision
Use vanilla JavaScript (ES6+) with a single `EmailViewer` class.

#### Alternatives Considered
1. **React**: Rejected - adds build complexity for relatively simple UI
2. **Vue**: Rejected - same reasoning as React
3. **Svelte**: Rejected - team familiarity with vanilla JS

#### Consequences
- **Positive**: No build step required, smaller bundle size, easier deployment
- **Negative**: Manual DOM manipulation, no component reusability
- **Neutral**: Works well for current feature set

---

### Railway for Hosting
- **Date**: 2024-10-16
- **Status**: Accepted

#### Context
Needed a platform to host the Flask application with Python 3.13 support.

#### Decision
Deploy to Railway with auto-deploy from GitHub.

#### Alternatives Considered
1. **Heroku**: Similar but free tier discontinued
2. **Vercel**: Python support limited
3. **AWS/GCP**: More complex, overkill for this project
4. **DigitalOcean App Platform**: Good option but Railway simpler

#### Consequences
- **Positive**: Simple deployment, free tier available, auto-deploy from git
- **Negative**: Vendor lock-in, less control than VPS
- **Neutral**: Uses Gunicorn WSGI server via Procfile

---

### BeautifulSoup with html.parser over lxml
- **Date**: 2024-10-16
- **Status**: Accepted

#### Context
Needed to sanitize HTML content from emails to prevent XSS attacks.

#### Decision
Use BeautifulSoup with Python's built-in `html.parser` instead of `lxml`.

#### Alternatives Considered
1. **lxml parser**: Initially tried but caused import errors
2. **bleach library**: Good alternative but BeautifulSoup more flexible
3. **Custom regex**: Too error-prone for HTML parsing

#### Consequences
- **Positive**: No additional C dependencies, works reliably
- **Negative**: Slightly slower than lxml for large documents
- **Neutral**: lxml is still in requirements.txt but unused

---

### Base64 Data URLs for Embedded Images
- **Date**: 2024-10-16
- **Status**: Accepted

#### Context
Emails often contain embedded images referenced by `cid:` (Content-ID) URLs. These don't work when displayed in a web browser.

#### Decision
Convert `cid:` references to base64 data URLs inline in the HTML.

#### Alternatives Considered
1. **Serve images via API**: Would require additional endpoints and storage management
2. **Store images separately**: More complex, requires cleanup mechanism
3. **Ignore embedded images**: Poor user experience

#### Consequences
- **Positive**: Images display correctly, no additional API calls needed
- **Negative**: Increases JSON response size significantly for image-heavy emails
- **Neutral**: Performance acceptable for typical email sizes

---

### Glassmorphic UI Design
- **Date**: 2024-10-16
- **Status**: Accepted

#### Context
Wanted a modern, professional look for the email viewer.

#### Decision
Implement glassmorphic design with animated gradient backgrounds.

#### Alternatives Considered
1. **Material Design**: Common but less distinctive
2. **Tailwind CSS**: Would require build step
3. **Bootstrap**: Dated look, heavy framework

#### Consequences
- **Positive**: Modern, distinctive appearance, works well with dark theme
- **Negative**: CSS-only, some older browsers may not support backdrop-filter
- **Neutral**: All styles inline in index.html

---

## When to Record a Decision

Record decisions when:
- Choosing between technologies or frameworks
- Designing data models or API structures
- Setting up deployment or infrastructure
- Establishing patterns that will be repeated
- Making tradeoffs that might not be obvious later

Don't record obvious choices or standard patterns unless there's something unusual about the context.
