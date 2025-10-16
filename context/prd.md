# Email Viewer Application - Detailed Summary

## Overview
A professional web-based email parser application that allows users to view `.eml` and `.msg` files directly in their browser. Features a premium glassmorphic UI design with full attachment handling, including the ability to preview nested email files.

## Tech Stack

### Frontend
- **HTML5**: Semantic markup with drag-and-drop API support
- **CSS3**: Pure CSS with glassmorphic design (no frameworks)
  - Custom properties for theming
  - Backdrop filters for glass effects
  - CSS Grid and Flexbox for layouts
  - Custom animations and transitions
- **Vanilla JavaScript (ES6+)**: 
  - Class-based architecture
  - Async/await for API calls
  - FileReader API for client-side file handling
  - Fetch API for server communication

### Backend
- **PHP 7.4+** (SiteGround supports up to PHP 8.2)
- **Composer** for dependency management
- **PHP Libraries**:
  - `php-mime-mail-parser/php-mime-mail-parser` (^7.0) - For .eml parsing
  - `hfig/mapi` (^1.0) - For .msg file parsing
  - PHP sessions for temporary attachment storage

### No External CDNs Required
- All assets are self-hosted
- No jQuery, Bootstrap, or other framework dependencies
- Icons are inline SVG (no icon fonts)

## Core Features

### File Support
- **.eml files**: Standard email format (RFC 822 compliant)
- **.msg files**: Microsoft Outlook proprietary format
- **Drag-and-drop** or click-to-upload interface
- **File size limit**: 10MB (configurable)

### Email Display
- Full header information (From, To, CC, Subject, Date)
- HTML and plain text body rendering
- Sanitized HTML output to prevent XSS attacks
- Responsive scrollable content area

### Attachment Handling
- List all attachments with file size
- Download individual attachments
- **Special handling for nested .eml/.msg files**:
  - Opens in modal popup
  - Full parsing of nested email content
  - Recursive attachment support

### UI/UX Features
- Glassmorphic design with blur effects
- Animated background gradients
- Loading states and error handling
- Smooth animations and micro-interactions
- Custom styled scrollbars
- Responsive design

## SiteGround-Specific Considerations

### 1. **PHP Configuration**
```apache
# Add to .htaccess
# SiteGround supports these directives
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
php_value memory_limit 256M
```

### 2. **Composer Installation**
SiteGround supports Composer via SSH:
```bash
# SSH into your SiteGround account
cd ~/public_html/email-viewer
/usr/local/bin/ea-php74/bin/php /usr/local/bin/composer install
# Or use SiteGround's Site Tools > Devs > SSH Keys Manager
```

### 3. **File Permissions**
```bash
# SiteGround recommended permissions
chmod 755 public_html/email-viewer
chmod 755 php
chmod 644 *.php
chmod 777 uploads  # Temporary files directory
```

### 4. **Cron Job Setup**
Add via SiteGround Site Tools > Devs > Cron Jobs:
```bash
# Run cleanup every hour
0 * * * * /usr/local/bin/ea-php74/bin/php /home/username/public_html/email-viewer/php/cleanup.php
```

### 5. **Security Headers**
Add to `.htaccess` for enhanced security:
```apache
# Security headers
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Referrer-Policy "strict-origin-when-cross-origin"

# Disable directory browsing
Options -Indexes

# Protect sensitive directories
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to composer files
<FilesMatch "composer\.(json|lock)">
    Order allow,deny
    Deny from all
</FilesMatch>
```

## Implementation Architecture

### Request Flow
1. User uploads file via drag-drop or file picker
2. JavaScript validates file type client-side
3. File sent to PHP via FormData/AJAX
4. PHP validates and parses email file
5. Attachments extracted and stored temporarily
6. JSON response sent back to client
7. JavaScript renders the email content
8. Session stores attachment references for download

### Session Management
- PHP sessions store attachment metadata
- Temporary files cleaned up after 1 hour
- Each upload gets unique identifier
- Attachment downloads verified against session

### Error Handling
- Client-side validation (file type, size)
- Server-side validation and sanitization
- Try-catch blocks throughout
- User-friendly error messages
- Graceful fallbacks

## Security Measures

1. **Input Validation**
   - File type verification (MIME and extension)
   - File size limits
   - Filename sanitization

2. **XSS Prevention**
   - HTML content sanitization
   - Content Security Policy headers
   - Escaped output in JavaScript

3. **File Security**
   - Uploads outside web root (or protected)
   - Unique identifiers for files
   - Session-based access control
   - Automatic cleanup of old files

4. **CSRF Protection** (to implement)
   ```php
   // Add to forms if needed
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   ```

## Performance Optimizations

1. **Asset Optimization**
   - Minify CSS and JavaScript for production
   - Gzip compression via .htaccess
   ```apache
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/html text/css text/javascript application/javascript
   </IfModule>
   ```

2. **Caching Strategy**
   ```apache
   # Add to .htaccess
   <IfModule mod_expires.c>
       ExpiresActive On
       ExpiresByType text/css "access plus 1 month"
       ExpiresByType application/javascript "access plus 1 month"
   </IfModule>
   ```

3. **Lazy Loading**
   - Attachments loaded on-demand
   - Modal content loaded when needed

## Directory Structure
```
email-viewer/
├── index.html                 # Main application page
├── css/
│   └── styles.css            # Glassmorphic styles
├── js/
│   └── app.js                # Application logic
├── php/
│   ├── config.php            # Configuration
│   ├── parse-email.php       # Email parser
│   ├── download-attachment.php # Attachment handler
│   └── cleanup.php           # Cron cleanup script
├── uploads/                  # Temporary file storage (777)
├── vendor/                   # Composer dependencies
├── composer.json             # PHP dependencies
├── composer.lock            # Dependency lock file
└── .htaccess                # Apache configuration
```

## Deployment Steps for SiteGround

1. **Upload files via FTP/SFTP or Git**
2. **SSH into server and install dependencies**:
   ```bash
   cd public_html/email-viewer
   composer install --no-dev --optimize-autoloader
   ```
3. **Set permissions**:
   ```bash
   chmod 777 uploads
   chmod 644 php/*.php
   ```
4. **Configure cron job** in Site Tools
5. **Test with sample .eml and .msg files**

## Monitoring & Maintenance

- **Error Logs**: Check SiteGround's error logs in Site Tools
- **Disk Usage**: Monitor uploads folder size
- **PHP Error Reporting** (for debugging):
  ```php
  ini_set('display_errors', 0);  // Production
  error_reporting(E_ALL);
  ini_set('log_errors', 1);
  ```

## Scalability Considerations

- **File Storage**: Implement S3 or external storage for high volume
- **Queue System**: Add job queue for large file processing
- **CDN**: Use SiteGround's CDN for static assets
- **Database**: Add MySQL for persistent email storage if needed

## Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile responsive

This application is production-ready for SiteGround hosting with proper security measures and optimizations in place. The glassmorphic design provides a premium user experience without external dependencies.

## Styling

Replace `css/styles.css` with:

```css
:root {
    /* shadcn/ui inspired color palette */
    --background: 0 0% 100%;
    --foreground: 222.2 84% 4.9%;
    --card: 0 0% 100%;
    --card-foreground: 222.2 84% 4.9%;
    --popover: 0 0% 100%;
    --popover-foreground: 222.2 84% 4.9%;
    --primary: 221.2 83.2% 53.3%;
    --primary-foreground: 210 40% 98%;
    --secondary: 210 40% 96.1%;
    --secondary-foreground: 222.2 47.4% 11.2%;
    --muted: 210 40% 96.1%;
    --muted-foreground: 215.4 16.3% 46.9%;
    --accent: 210 40% 96.1%;
    --accent-foreground: 222.2 47.4% 11.2%;
    --destructive: 0 84.2% 60.2%;
    --destructive-foreground: 210 40% 98%;
    --border: 214.3 31.8% 91.4%;
    --input: 214.3 31.8% 91.4%;
    --ring: 221.2 83.2% 53.3%;
    --radius: 0.5rem;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell", "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
    min-height: 100vh;
    padding: 20px;
    position: relative;
    overflow-x: hidden;
}

/* Animated background orbs */
body::before,
body::after {
    content: '';
    position: fixed;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.3;
    animation: float 20s infinite ease-in-out;
    pointer-events: none;
}

body::before {
    width: 600px;
    height: 600px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    top: -200px;
    right: -200px;
}

body::after {
    width: 800px;
    height: 800px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    bottom: -300px;
    left: -300px;
    animation-delay: 10s;
}

@keyframes float {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -30px) rotate(120deg); }
    66% { transform: translate(-20px, 20px) rotate(240deg); }
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* Glass card effect */
.glass-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 
        0 8px 32px 0 rgba(31, 38, 135, 0.37),
        inset 0 1px 0 0 rgba(255, 255, 255, 0.1);
}

header {
    text-align: center;
    margin-bottom: 40px;
}

header h1 {
    font-size: 3rem;
    font-weight: 700;
    background: linear-gradient(to right, #fff 0%, #94a3b8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
    letter-spacing: -0.02em;
}

header p {
    color: #94a3b8;
    font-size: 1.1rem;
    font-weight: 400;
}

.upload-section {
    padding: 40px;
    margin-bottom: 30px;
}

.upload-area {
    border: 2px dashed rgba(148, 163, 184, 0.3);
    border-radius: 12px;
    padding: 60px 40px;
    text-align: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.upload-area::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at center, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.upload-area:hover::before,
.upload-area.dragover::before {
    opacity: 1;
}

.upload-area:hover,
.upload-area.dragover {
    border-color: rgba(139, 92, 246, 0.5);
    transform: translateY(-2px);
}

.upload-icon {
    width: 80px;
    height: 80px;
    color: #64748b;
    margin: 0 auto 24px;
    display: block;
}

.upload-area p {
    color: #94a3b8;
    margin: 8px 0;
    font-size: 1rem;
}

.upload-area p:first-of-type {
    font-size: 1.25rem;
    font-weight: 500;
    color: #cbd5e1;
}

.btn {
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s;
}

.btn:hover::before {
    transform: translateX(100%);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px 0 rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(102, 126, 234, 0.6);
}

.btn-secondary {
    background: rgba(148, 163, 184, 0.1);
    color: #cbd5e1;
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.btn-secondary:hover {
    background: rgba(148, 163, 184, 0.2);
    transform: translateY(-1px);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 15px 0 rgba(16, 185, 129, 0.4);
}

.btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(16, 185, 129, 0.6);
}

.btn-info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    box-shadow: 0 4px 15px 0 rgba(59, 130, 246, 0.4);
}

.btn-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px 0 rgba(59, 130, 246, 0.6);
}

.email-content {
    padding: 40px;
}

.email-header {
    padding-bottom: 24px;
    margin-bottom: 24px;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.email-field {
    display: flex;
    align-items: baseline;
    margin-bottom: 16px;
    color: #e2e8f0;
}

.email-field label {
    font-weight: 600;
    color: #94a3b8;
    min-width: 100px;
    margin-right: 16px;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.email-field span {
    color: #e2e8f0;
    flex: 1;
    font-size: 1rem;
}

.email-body {
    padding: 24px 0;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
    margin-bottom: 24px;
}

.email-body h3 {
    color: #94a3b8;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 16px;
    font-weight: 600;
}

.email-body-content {
    background: rgba(15, 23, 42, 0.5);
    border-radius: 8px;
    padding: 20px;
    color: #e2e8f0;
    line-height: 1.7;
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid rgba(148, 163, 184, 0.1);
}

/* Custom scrollbar */
.email-body-content::-webkit-scrollbar {
    width: 8px;
}

.email-body-content::-webkit-scrollbar-track {
    background: rgba(15, 23, 42, 0.3);
    border-radius: 4px;
}

.email-body-content::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.3);
    border-radius: 4px;
}

.email-body-content::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.5);
}

.attachments-section {
    margin-top: 32px;
}

.attachments-title {
    color: #94a3b8;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.attachment-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.attachment-item {
    background: rgba(30, 41, 59, 0.5);
    border: 1px solid rgba(148, 163, 184, 0.1);
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.attachment-item:hover {
    background: rgba(51, 65, 85, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px 0 rgba(0, 0, 0, 0.2);
}

.attachment-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.attachment-icon {
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    color: #94a3b8;
}

.attachment-details {
    min-width: 0;
    flex: 1;
}

.attachment-name {
    color: #e2e8f0;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.attachment-size {
    color: #64748b;
    font-size: 0.75rem;
}

.loading-spinner {
    text-align: center;
    padding: 60px 40px;
}

.spinner {
    width: 48px;
    height: 48px;
    border: 3px solid rgba(148, 163, 184, 0.1);
    border-radius: 50%;
    border-top-color: #8b5cf6;
    animation: spin 1s linear infinite;
    margin: 0 auto 24px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-spinner p {
    color: #94a3b8;
    font-size: 1rem;
}

.error-message {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: #f87171;
    padding: 16px 20px;
    border-radius: 8px;
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.error-message::before {
    content: '';
    display: inline-block;
    width: 20px;
    height: 20px;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23f87171'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'%3E%3C/path%3E%3C/svg%3E") no-repeat center;
    flex-shrink: 0;
}

.hidden {
    display: none !important;
}

.modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    padding: 20px;
    animation: modalFadeIn 0.2s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.modal-content {
    background: rgba(30, 41, 59, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(148, 163, 184, 0.1);
    border-radius: 16px;
    padding: 32px;
    width: 100%;
    max-width: 1000px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    animation: modalSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes modalSlideIn {
    from {
        transform: translateY(20px) scale(0.95);
        opacity: 0;
    }
    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.close-modal {
    position: absolute;
    right: 24px;
    top: 24px;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: rgba(148, 163, 184, 0.1);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 20px;
    line-height: 1;
}

.close-modal:hover {
    background: rgba(148, 163, 184, 0.2);
    color: #e2e8f0;
    transform: rotate(90deg);
}

/* Icon styles */
.icon {
    width: 20px;
    height: 20px;
    display: inline-block;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    fill: none;
}

.icon-sm {
    width: 16px;
    height: 16px;
}

.icon-lg {
    width: 24px;
    height: 24px;
}
```