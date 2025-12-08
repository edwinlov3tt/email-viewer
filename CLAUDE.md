# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Email Viewer Pro is a web-based email parser that allows users to view .eml and .msg files directly in their browser. Features a premium glassmorphic UI design with full attachment handling, nested email support, and embedded image rendering.

**Repository**: https://github.com/edwinlov3tt/email-viewer
**Production URL**: https://email-viewer.up.railway.app/
**Railway Project**: `devoted-nature`

## Tech Stack

- **Backend**: Python 3.13, Flask 3.0, Gunicorn
- **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Email Parsing**: extract-msg (MSG), email stdlib (EML)
- **HTML Sanitization**: BeautifulSoup4
- **Hosting**: Railway (project: devoted-nature)

## Development Commands

```bash
# Install dependencies
pip install -r requirements.txt

# Run locally (development)
python app.py

# Run with Gunicorn (production-like)
gunicorn app:app --bind 0.0.0.0:5000

# Deploy to Railway
railway up
```

## Architecture & Structure

```
email-viewer/
├── app.py              # Flask backend - email parsing API
├── index.html          # Frontend UI (glassmorphic design)
├── js/app.js           # EmailViewer class - frontend logic
├── uploads/            # Temporary file storage
├── requirements.txt    # Python dependencies
├── Procfile            # Railway process definition
├── runtime.txt         # Python version (3.13.7)
├── nixpacks.toml       # Railway build configuration
└── .claude/docs/       # Architecture documentation
```

### Core Components

1. **app.py** (Flask Backend):
   - `parse_eml_file()` - Parse .eml files using Python email library
   - `parse_msg_file()` - Parse .msg files using extract-msg
   - `sanitize_html()` - Remove scripts/styles, secure links
   - `resolve_cid_images()` - Convert embedded images to base64 data URLs

2. **js/app.js** (Frontend):
   - `EmailViewer` class handles all UI interactions
   - File upload with drag-and-drop
   - Bulk upload queue system
   - Fullscreen viewer with zoom (50-200%)
   - Pop-out windows for multitasking
   - Click-to-copy email addresses

3. **index.html** (UI):
   - Glassmorphic design with animated gradients
   - Collapsible recipient lists (3+ addresses)
   - Attachment grid with file icons
   - Nested email modal viewer

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/parse-email` | Upload and parse .eml/.msg file |
| GET | `/api/download-attachment` | Download attachment by ID |
| POST | `/api/parse-nested-email` | Parse nested email attachment |
| GET | `/health` | Health check endpoint |

## Key Implementation Notes

### Email Parsing
- EML files: Python's built-in `email` library with `policy.default`
- MSG files: `extract-msg` library for Outlook format
- Embedded images: CID references converted to base64 data URLs
- HTML sanitization: BeautifulSoup removes `<script>` and `<style>` tags

### Storage
- **In-Memory**: Parsed emails stored in `email_storage` dict (lost on restart)
- **File System**: Uploads saved to `uploads/` directory
- **No Database**: Schema provided in `.env.example` for future migration

### Security
- HTML content sanitized with BeautifulSoup
- XSS prevention via `escapeHtml()` function
- External links get `target="_blank" rel="noopener noreferrer"`
- File validation: extension (.eml, .msg) and size (10MB max)
- Filename sanitization removes null bytes and special characters

## Railway Deployment

**Project Name**: devoted-nature
**Service Name**: email-viewer
**Domain**: email-viewer.up.railway.app

```bash
# Link to project
railway link -p devoted-nature -s email-viewer

# Deploy
railway up

# Check logs
railway logs --lines 50

# Check status
railway status
```

The `nixpacks.toml` forces Python detection (overrides PHP detection from legacy `vendor/` directory).

## Known Limitations

1. In-memory storage - data lost on server restart
2. No rate limiting implemented
3. No file cleanup mechanism for uploads/
4. Bulk upload is sequential, not parallel
5. Large embedded images increase JSON response size

## Documentation

Detailed documentation in `.claude/docs/`:
- `ARCHITECTURE.md` - System design and data flow
- `CHANGELOG.md` - Version history
- `KNOWN_ISSUES.md` - Bugs and technical debt
- `DECISIONS.md` - Architectural decisions
- `components/api-routes.md` - API documentation
- `services/railway.md` - Deployment guide
