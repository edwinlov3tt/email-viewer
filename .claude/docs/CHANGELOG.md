# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Documentation system with automated slash commands

---

## 2025-12-08

### Railway Deployment Fix
- **Fixed**: Railway deployment was failing due to PHP detection from legacy `vendor/` directory
- **Added**: `nixpacks.toml` to explicitly configure Python 3.13 build
- **Added**: Full project audit and documentation system
- **Updated**: CLAUDE.md with current architecture (was outdated, described "demo mode")
- Railway project: `devoted-nature`, Service: `email-viewer`
- Production URL: https://email-viewer.up.railway.app/

### Documentation Overhaul
- **Added**: Complete architecture documentation in `.claude/docs/`
- **Added**: API routes documentation with request/response examples
- **Added**: Railway deployment guide
- **Added**: Frontend component documentation (EmailViewer class)
- **Updated**: KNOWN_ISSUES.md with 9 active issues identified during audit
- **Updated**: DECISIONS.md with 7 architectural decisions

---

## 2024-10-16

### JSON Serialization Fix
- **Fixed**: JSON serialization error when embedded images were present
- Binary attachment data is now properly removed before returning JSON response
- Commit: `1d02a0f`

### Embedded Image Support
- **Added**: Support for embedded/inline images (CID resolution)
- Added `resolve_cid_images()` function to convert `cid:` references to base64 data URLs
- Inline images now display correctly in email body
- Commit: `d72f711`

### Pop-out Window Fix
- **Fixed**: Huge dropdown icons in pop-out email windows
- Icons now display at correct size in separate window views
- Commit: `a030ccd`

### Parser and Display Fixes
- **Fixed**: lxml parser error - switched to html.parser (built-in)
- **Fixed**: Email body text wrapping issues
- Added proper CSS for word-wrap and overflow handling
- Commit: `00c3850`

### Static File Routing
- **Fixed**: 404 error when accessing application
- Added explicit routes to serve static files (index.html, js/app.js)
- Commit: `7a71a37`

---

## 2024-10-16 (Initial Release)

### Migration to Python/Flask
- **Removed**: PHP backend files
- **Added**: Python Flask backend with full email parsing
- Migrated from PHP mail-mime-parser to Python extract-msg
- Commit: `e2fe53b`

### Initial Implementation
- **Added**: Email Viewer Pro - professional web-based email parser
- **Added**: Support for .eml (standard email) and .msg (Outlook) files
- **Added**: Bulk upload with queue system
- **Added**: Nested email support (open .eml/.msg attachments)
- **Added**: Attachment download functionality
- **Added**: Glassmorphic UI design with animated gradients
- **Added**: Fullscreen viewer with zoom controls (50-200%)
- **Added**: Pop-out windows for multitasking
- **Added**: Collapsible recipient lists (3+ addresses)
- **Added**: Click-to-copy email addresses
- **Added**: HTML sanitization with BeautifulSoup
- **Added**: Railway deployment configuration
- Commit: `b73fd13`

---

## Format Guide

Each entry should include:
- **Added**: New features or capabilities
- **Changed**: Changes to existing functionality
- **Fixed**: Bug fixes
- **Removed**: Removed features or capabilities
- **Security**: Security-related fixes
- **Deprecated**: Features that will be removed in future versions

### Example Entry

```markdown
## 2025-11-27

### Campaign Pacing Dashboard
- Added threshold alerts for underperforming campaigns
- Fixed CSV export encoding issue for special characters
- **Known issue**: Large datasets (>10k rows) cause timeout - see KNOWN_ISSUES.md
```
