# Architecture Overview

Email Viewer Pro - A web-based email parser for .eml and .msg files.

**Repository**: https://github.com/edwinlov3tt/email-viewer
**Production URL**: https://email-viewer.up.railway.app/
**Railway Project**: `devoted-nature` | **Service**: `email-viewer`
**Last Updated**: December 2025

## Tech Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| Frontend | HTML5/CSS3/Vanilla JS | User interface (glassmorphic design) |
| Backend | Flask 3.0 (Python 3.13) | API server and email parsing |
| Email Parsing | extract-msg, email (stdlib) | Parse .eml and .msg formats |
| Sanitization | BeautifulSoup4 | HTML content sanitization |
| Server | Gunicorn | Production WSGI server |
| Hosting | Railway | Deployment platform |

## System Diagram

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   Browser       │────▶│   Flask API     │────▶│  File System    │
│  (index.html)   │     │   (app.py)      │     │   (uploads/)    │
│   + app.js      │◀────│                 │◀────│                 │
└─────────────────┘     └─────────────────┘     └─────────────────┘
        │                       │
        │                       ▼
        │              ┌─────────────────┐
        └──────────────│  In-Memory      │
           (WebSocket) │  email_storage  │
                       └─────────────────┘
```

## Key Components

### Frontend (`index.html`, `js/app.js`)
- **Purpose**: Email viewing UI with drag-and-drop upload
- **Location**: Root directory
- **Entry Point**: `index.html` loads `js/app.js`
- **Key Features**:
  - EmailViewer class handles all frontend logic
  - Glassmorphic UI with animated gradients
  - Bulk upload queue system
  - Fullscreen viewer with zoom controls
  - Pop-out email windows
  - Clickable/copyable email addresses
  - Collapsible recipient lists (3+)

### Backend/API (`app.py`)
- **Purpose**: Email parsing, file handling, attachment management
- **Location**: `app.py` (root directory)
- **Entry Point**: `app.py` (Flask application)
- **Key Dependencies**:
  - Flask 3.0.0 - Web framework
  - Flask-CORS 4.0.0 - Cross-origin support
  - extract-msg 0.48.7 - MSG file parsing
  - beautifulsoup4 4.12.2 - HTML sanitization
  - lxml 5.1.0 - XML/HTML parser
  - gunicorn 21.2.0 - Production server

### Key Functions

#### `parse_eml_file(file_path)` - Line 56
Parses .eml files using Python's built-in email library.

#### `parse_msg_file(file_path)` - Line 206
Parses .msg (Outlook) files using extract-msg library.

#### `sanitize_html(html_content)` - Line 36
Removes scripts/styles, secures links with target="_blank".

#### `resolve_cid_images(html_content, attachments)` - Line 174
Converts cid: image references to base64 data URLs for embedded images.

#### `sanitize_filename(filename)` - Line 156
Removes null bytes and invalid characters from filenames.

## Data Flow

1. **Upload**: User drops/selects .eml or .msg file
2. **Validation**: Frontend checks extension and size (10MB limit)
3. **API Call**: File sent to `/api/parse-email`
4. **Parsing**: Backend parses using appropriate library
5. **Sanitization**: HTML content sanitized, CID images resolved
6. **Storage**: Email data stored in memory (email_storage dict)
7. **Response**: JSON with headers, body, and attachments returned
8. **Display**: Frontend renders email in glassmorphic UI

## External Services

| Service | Purpose | Docs Location |
|---------|---------|---------------|
| Railway | Production hosting | `.claude/docs/services/railway.md` |

## Environment Variables

| Variable | Purpose | Required | Default |
|----------|---------|----------|---------|
| `PORT` | Server port | No | 5000 |
| `FLASK_ENV` | Environment mode | No | development |
| `MAX_UPLOAD_SIZE` | Max file size (bytes) | No | 10485760 (10MB) |
| `SESSION_TIMEOUT` | Session duration (sec) | No | 3600 |
| `TEMP_FILE_RETENTION` | File retention (sec) | No | 3600 |

### Optional Database Variables (Not Currently Used)
| Variable | Purpose |
|----------|---------|
| `DB_HOST` | Database host |
| `DB_NAME` | Database name |
| `DB_USER` | Database username |
| `DB_PASS` | Database password |

## Directory Structure

```
email-viewer/
├── app.py                  # Flask API backend (472 lines)
├── index.html              # Main application UI (986 lines)
├── js/
│   └── app.js              # Frontend logic - EmailViewer class (1036 lines)
├── uploads/                # Temporary file storage (gitignored)
├── requirements.txt        # Python dependencies
├── Procfile                # Railway deployment config
├── runtime.txt             # Python version (3.13.7)
├── .gitignore              # Git ignore rules
├── .env                    # Environment variables
├── .env.example            # Environment template
├── CLAUDE.md               # Claude Code instructions
├── AGENTS.md               # Agent documentation
├── README.md               # Project documentation
├── context/                # Project context docs
│   ├── prd.md              # Product requirements
│   └── web-viewer-eml-msg.md
├── .claude/
│   ├── commands/           # Slash commands
│   ├── docs/               # Architecture documentation
│   └── settings.local.json # Local settings
└── vendor/                 # Legacy PHP vendor (unused)
```

## Deployment

### Production (Railway)
- **Platform**: Railway
- **URL**: `https://your-app.up.railway.app`
- **Deploy Command**: `git push` triggers auto-deploy
- **Process**: `gunicorn app:app --bind 0.0.0.0:$PORT`

### Local Development
```bash
# Backend
python app.py  # Runs on port 5000

# Or with frontend
python -m http.server 8080  # Serve index.html
```

## Performance Considerations

- **File Size Limit**: 10MB per email file
- **Memory Storage**: Emails stored in memory (dict), not persisted
- **Attachment Storage**: Saved to `uploads/` folder temporarily
- **No Database**: Current implementation is stateless per session
- **Base64 Images**: CID images converted to data URLs (increases payload size)

## Security Considerations

- **HTML Sanitization**: BeautifulSoup removes `<script>` and `<style>` tags
- **XSS Prevention**: All user content escaped with `escapeHtml()`
- **Link Security**: External links get `target="_blank" rel="noopener noreferrer"`
- **File Validation**: Extension and size validation on both client and server
- **CORS**: Flask-CORS enabled for API access
- **Filename Sanitization**: Null bytes and special characters removed

## API Rate Limits

No rate limiting currently implemented.

## Known Limitations

1. Email storage is in-memory only (lost on restart)
2. No user authentication
3. No persistent database
4. Large base64 embedded images increase response size
5. Bulk upload processes files sequentially (not true parallel)
