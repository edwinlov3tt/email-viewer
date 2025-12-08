# EmailViewer Frontend Component

## Overview

The `EmailViewer` class is the main JavaScript component that handles all frontend functionality for the email viewer application.

## Location

- **File**: `js/app.js`
- **Lines**: 1-1036
- **Initialization**: `index.html:984`

## Class Structure

```javascript
class EmailViewer {
    constructor()           // Initialize state and event listeners
    initializeElements()    // DOM element references
    initializeEventListeners() // Event bindings
    // ... methods
}
```

## Properties

| Property | Type | Description |
|----------|------|-------------|
| `currentEmail` | Object | Currently displayed email data |
| `emailList` | Array | All parsed emails |
| `uploadQueue` | Array | Files pending upload |
| `activeEmailId` | String | ID of selected email |
| `apiBase` | String | API endpoint base URL |
| `fullscreenModal` | Element | Fullscreen viewer modal |
| `currentZoom` | Number | Current zoom level (50-200) |

## Key Methods

### File Handling

#### `handleFiles(files)` - Line 131
Entry point for file uploads. Routes to single or bulk upload.

```javascript
handleFiles(files) {
    const validFiles = fileArray.filter(file => this.validateFile(file));
    if (validFiles.length === 1) {
        this.uploadSingleFile(validFiles[0]);
    } else {
        this.addToQueue(validFiles);
    }
}
```

#### `validateFile(file)` - Line 150
Validates file extension (.eml, .msg) and size (10MB max).

#### `uploadSingleFile(file)` - Line 312
Uploads single file to `/api/parse-email`.

#### `uploadAll()` - Line 221
Processes all files in upload queue sequentially.

### Display Methods

#### `displayEmail(emailData)` - Line 458
Renders email content in the viewer panel.

Features:
- Email headers (from, to, cc, bcc, subject, date)
- Clickable email addresses
- Collapsible recipient lists (3+)
- HTML body with sanitized content
- Attachment grid

#### `displayNestedEmail(emailData)` - Line 590
Renders nested email in modal dialog.

#### `openFullscreen()` - Line 769
Opens email body in fullscreen modal with zoom controls.

#### `popoutEmail()` - Line 813
Opens email in separate browser window.

### Email Address Handling

#### `parseEmailAddresses(emailStr)` - Line 392
Extracts email addresses using regex.

#### `makeEmailAddressClickable(emailStr, fieldName)` - Line 401
Wraps email addresses in clickable spans.

#### `copyEmailAddress(email, event)` - Line 443
Copies email to clipboard with visual feedback.

#### `toggleEmailList(id)` - Line 433
Expands/collapses recipient lists.

### Attachment Handling

#### `downloadAttachment(emailId, attachmentId, filename)` - Line 679
Triggers attachment download via API.

#### `openNestedEmail(emailId, attachmentId, filename)` - Line 565
Parses and displays nested email attachments.

#### `getFileIcon(filename)` - Line 691
Returns emoji icon based on file extension.

### Queue Management

#### `addToQueue(files)` - Line 167
Adds files to upload queue.

#### `renderQueue()` - Line 182
Renders queue UI with status badges.

#### `removeFromQueue(itemId)` - Line 206
Removes single item from queue.

#### `clearQueue()` - Line 215
Clears all queued items.

### Zoom Controls

#### `zoomIn()` - Line 793
Increases zoom by 10% (max 200%).

#### `zoomOut()` - Line 800
Decreases zoom by 10% (min 50%).

#### `updateZoom()` - Line 807
Applies zoom transform to content.

### Utility Methods

#### `escapeHtml(text)` - Line 720
Prevents XSS by escaping HTML entities.

#### `formatFileSize(bytes)` - Line 708
Converts bytes to human-readable format.

#### `generateId()` - Line 732
Generates random ID for queue items.

## Event Listeners

Initialized in `initializeEventListeners()` (Line 60):

| Event | Element | Handler |
|-------|---------|---------|
| dragover | uploadArea | Add dragover class |
| dragleave | uploadArea | Remove dragover class |
| drop | uploadArea | handleFiles() |
| click | browseBtn | Open file dialog |
| click | bulkBtn | Open file dialog (multiple) |
| change | fileInput | handleFiles() |
| click | uploadAllBtn | uploadAll() |
| click | clearQueueBtn | clearQueue() |
| input | searchInput | filterEmailList() |
| click | sectionToggle | Toggle collapse |
| click | closeModal | closeNestedEmailModal() |

## UI States

### Loading State
```javascript
showLoading()  // Show spinner, hide content
hideLoading()  // Hide spinner, show content
```

### Error State
```javascript
showError(message)  // Display error with auto-hide (5s)
```

### Success State
```javascript
showSuccess(message)  // Currently only logs to console
```

## CSS Classes Used

| Class | Purpose |
|-------|---------|
| `.glass-card` | Glassmorphic card styling |
| `.upload-area` | Drop zone styling |
| `.dragover` | Drag hover state |
| `.email-list-item` | Email list entry |
| `.active` | Selected email |
| `.hidden` | Display none |
| `.collapsed` | Upload section collapsed |
| `.email-address` | Clickable email |
| `.copied` | Copy feedback state |

## Dependencies

- No external JavaScript libraries
- Requires modern browser (ES6+)
- Uses native `fetch()` API
- Uses native `clipboard` API

## Initialization

```javascript
// In index.html
let emailViewer;
document.addEventListener('DOMContentLoaded', () => {
    emailViewer = new EmailViewer();
});
```

Global `emailViewer` variable allows onclick handlers in HTML.
