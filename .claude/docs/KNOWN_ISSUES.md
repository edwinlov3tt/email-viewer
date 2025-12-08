# Known Issues

Track bugs, edge cases, and technical debt.

**Last Updated**: December 2025

## Active Issues

### [LOW] Debug Print Statements in Production Code
- **Location**: `app.py` - lines 265, 268
- **Symptom**: Debug messages printed to console during attachment processing errors
- **Root Cause**: Print statements used instead of proper logging
- **Workaround**: None needed - doesn't affect functionality
- **Proper Fix**: Replace `print()` with Python logging module
- **Added**: 2025-12-08

### [LOW] Console.log Statements in JavaScript
- **Location**: `js/app.js` - lines 256, 260, 286, 303, 454, 585, 762, 988
- **Symptom**: Debug messages in browser console during errors and success states
- **Root Cause**: Debug logging left in production code
- **Workaround**: None needed
- **Proper Fix**: Remove or replace with conditional debug logging
- **Added**: 2025-12-08

### [MEDIUM] In-Memory Email Storage
- **Location**: `app.py` - line 33 (`email_storage = {}`)
- **Symptom**: All parsed emails lost when server restarts
- **Root Cause**: Design choice to use in-memory dict instead of database
- **Workaround**: Re-upload emails after server restart
- **Proper Fix**: Implement database storage (schema provided in `.env.example`)
- **Added**: 2025-12-08

### [LOW] Bulk Upload Processes Sequentially
- **Location**: `js/app.js` - lines 237-266 (`uploadAll()` method)
- **Symptom**: Multiple files upload one at a time instead of in parallel
- **Root Cause**: Original bulk endpoint was PHP, code was adapted to sequential uploads
- **Workaround**: None - uploads work, just slower
- **Proper Fix**: Implement true parallel upload with Promise.all()
- **Note**: Commented-out bulk upload code at lines 269-309
- **Added**: 2025-12-08

### [LOW] Bare Exception Handlers
- **Location**: `app.py` - lines 84-85, 90-91
- **Symptom**: Silent failures when parsing email content
- **Root Cause**: Broad `except:` clauses catch all exceptions
- **Workaround**: None needed
- **Proper Fix**: Catch specific exceptions, add proper logging
- **Added**: 2025-12-08

### [MEDIUM] No File Cleanup Mechanism
- **Location**: `app.py` - `uploads/` directory
- **Symptom**: Uploaded files and attachments accumulate indefinitely
- **Root Cause**: No scheduled cleanup of temporary files
- **Workaround**: Manually delete files from `uploads/` folder
- **Proper Fix**: Implement scheduled cleanup based on `TEMP_FILE_RETENTION` env var
- **Added**: 2025-12-08

### [LOW] Legacy PHP Vendor Directory
- **Location**: `vendor/` directory
- **Symptom**: Unused code increases repository size
- **Root Cause**: Legacy from PHP-based implementation before Flask migration
- **Workaround**: None needed - code is unused
- **Proper Fix**: Remove `vendor/` directory from repository
- **Added**: 2025-12-08

### [MEDIUM] No Rate Limiting
- **Location**: `app.py` - all API endpoints
- **Symptom**: API vulnerable to abuse/DoS
- **Root Cause**: Not implemented
- **Workaround**: Rely on hosting platform's rate limiting
- **Proper Fix**: Add Flask-Limiter or similar rate limiting middleware
- **Added**: 2025-12-08

### [LOW] Success Message Not Displayed
- **Location**: `js/app.js` - line 760-763 (`showSuccess()` method)
- **Symptom**: Success notifications only logged to console, not shown to user
- **Root Cause**: Method only has `console.log()`, no UI notification
- **Workaround**: None - functionality works, just no visual feedback
- **Proper Fix**: Implement toast/notification UI for success messages
- **Added**: 2025-12-08

---

## Resolved Issues

### [RESOLVED] JSON Serialization Error for Embedded Images
- **Location**: `app.py` - `parse_eml_file()` and `parse_msg_file()`
- **Resolution**: Added code to remove binary `data` field from attachments before JSON response
- **Resolved**: 2024-10-16 (commit `1d02a0f`)

### [RESOLVED] lxml Parser Error
- **Location**: `app.py` - `sanitize_html()`
- **Resolution**: Switched from lxml to html.parser (Python built-in)
- **Resolved**: 2024-10-16 (commit `00c3850`)

### [RESOLVED] 404 Error on Static Files
- **Location**: `app.py` - routing
- **Resolution**: Added explicit routes for `index.html` and `js/` directory
- **Resolved**: 2024-10-16 (commit `7a71a37`)

### [RESOLVED] Huge Icons in Pop-out Window
- **Location**: `js/app.js` - `popoutEmail()` CSS
- **Resolution**: Fixed icon sizing in pop-out window styles
- **Resolved**: 2024-10-16 (commit `a030ccd`)

---

## Severity Guide

| Level | Description | Response |
|-------|-------------|----------|
| **CRITICAL** | System unusable, data loss risk | Fix immediately |
| **HIGH** | Major feature broken, no workaround | Fix this sprint |
| **MEDIUM** | Feature impaired, workaround exists | Fix when possible |
| **LOW** | Minor inconvenience, cosmetic | Fix eventually |

## Template

When adding an issue, copy this template:

```markdown
### [SEVERITY] Brief Descriptive Title
- **Location**: `path/to/file.ts` - `functionName()`
- **Symptom**: What happens when this issue occurs
- **Root Cause**: Why it happens (if known, otherwise "Unknown")
- **Workaround**: Temporary fix (if any, otherwise "None")
- **Proper Fix**: What needs to be done to resolve permanently
- **Reproduction**: Steps to reproduce (optional but helpful)
- **Added**: YYYY-MM-DD
```
