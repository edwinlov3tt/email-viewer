# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is an Email Viewer web application - a client-side email file parser that allows users to view .eml and .msg files directly in their browser. The application features a premium glassmorphic UI design with full attachment handling capabilities, including nested email preview support.

## Tech Stack

- **Frontend Only**: Pure HTML5, CSS3, and vanilla JavaScript (ES6+)
- **No Framework Dependencies**: All functionality is self-contained
- **No Build Process**: The application runs directly from static files
- **CSS Design System**: Custom glassmorphic design with animated backgrounds

## Development Commands

Since this is a static frontend application, there are no build or test commands. To run the application:

1. Open `index.html` directly in a web browser, or
2. Use any static file server (e.g., `python -m http.server` or Live Server VS Code extension)

## Architecture & Structure

### Core Components

1. **index.html**: Main application file containing the complete UI structure
   - Self-contained with inline CSS and JavaScript
   - Features drag-and-drop upload area
   - Modal system for nested email viewing
   - Glassmorphic card-based layout

2. **EmailViewer Class** (JavaScript):
   - Handles file upload and validation
   - Simulates email parsing (demonstration mode)
   - Manages attachment display and download
   - Provides nested email modal functionality

3. **CSS Design System**:
   - Custom properties (CSS variables) for theming
   - Glassmorphic effects with backdrop filters
   - Animated gradient background orbs
   - Custom styled scrollbars
   - Responsive grid layouts

### Key UI Elements

- **Upload Area**: Drag-and-drop zone with hover effects
- **Email Display**: Header fields, body content, and attachment grid
- **Modal System**: For viewing nested email attachments
- **Loading States**: Spinner animations during processing
- **Error Handling**: User-friendly error messages

## Important Implementation Notes

### Current State
The application is currently in **demonstration mode** - it simulates email parsing with sample data rather than actually parsing uploaded files. The `parseEmailFile()` method generates mock email data for UI demonstration purposes.

### To Convert to Production
To make this application functional, you would need to:
1. Implement actual .eml and .msg file parsing (using libraries like EmailJS or server-side processing)
2. Add real attachment extraction and handling
3. Implement actual file download functionality for attachments
4. Add proper security measures (content sanitization, file validation)

### File Handling Limitations
- Browser JavaScript cannot directly parse .eml/.msg files without appropriate libraries
- Attachment downloads in demo mode show alerts instead of actual downloads
- Nested email viewing uses simulated content

## Design Patterns

### Glassmorphic UI
- Semi-transparent backgrounds with blur effects
- Layered glass cards with subtle borders
- Animated gradient orbs in the background
- Consistent use of rgba colors for transparency

### Event Handling
- Class-based architecture with centralized event management
- Drag-and-drop API integration
- Modal management through class methods
- Proper event delegation for dynamic content

### Responsive Design
- CSS Grid for attachment layouts
- Flexible card components
- Mobile-friendly viewport settings
- Overflow handling with custom scrollbars

## Security Considerations

When converting to production:
- Implement proper HTML sanitization for email content
- Validate file types and sizes on both client and server
- Use Content Security Policy headers
- Sanitize filenames before display
- Implement session-based access control for attachments