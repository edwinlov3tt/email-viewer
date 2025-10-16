

## Premium Design Instructions

### Step 1: Replace the CSS file with this premium glassmorphic design

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

### Step 2: Update the HTML to use SVG icons instead of emojis

Update `index.html`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Viewer - Professional Email Parser</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Email File Viewer</h1>
            <p>Professional EML & MSG file parser with attachment support</p>
        </header>
        
        <div class="upload-section glass-card">
            <div class="upload-area" id="uploadArea">
                <svg class="upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p>Drop your email files here</p>
                <p>or</p>
                <input type="file" id="fileInput" accept=".eml,.msg" hidden>
                <button class="btn btn-primary" onclick="document.getElementById('fileInput').click()">
                    <svg class="icon icon-sm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Select File
                </button>
            </div>
        </div>
        
        <div id="loadingSpinner" class="loading-spinner glass-card hidden">
            <div class="spinner"></div>
            <p>Processing email file...</p>
        </div>
        
        <div id="emailContent" class="email-content glass-card hidden">
            <!-- Email content will be inserted here -->
        </div>
        
        <div id="errorMessage" class="error-message hidden"></div>
    </div>
    
    <!-- Modal for nested emails -->
    <div id="nestedEmailModal" class="modal hidden">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <div id="nestedEmailContent"></div>
        </div>
    </div>
    
    <script src="js/app.js"></script>
</body>
</html>
```

### Step 3: Update JavaScript to use SVG icons

Update the relevant parts of `js/app.js`:

```javascript
// Update the getFileIcon method in the EmailViewer class
getFileIcon(filename) {
    const ext = filename.split('.').pop().toLowerCase();
    
    // SVG icon definitions
    const icons = {
        pdf: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        
        doc: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        
        docx: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>',
        
        xls: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><rect x="8" y="12" width="8" height="6"></rect></svg>',
        
        xlsx: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><rect x="8" y="12" width="8" height="6"></rect></svg>',
        
        zip: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11v10H2V3h12"></path><path d="M14 3v4a2 2 0 0 0 2 2h4"></path><path d="M10 11v-1"></path><path d="M10 14v-1"></path><path d="M10 17v-1"></path></svg>',
        
        jpg: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        
        png: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        
        eml: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        
        msg: '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>'
    };
    
    // Default file icon
    const defaultIcon = '<svg class="attachment-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>';
    
    return icons[ext] || defaultIcon;
}

// Update the displayEmail method to use the new icon method and button styles
displayEmail(emailData) {
    let html = `
        <div class="email-header">
            <div class="email-field">
                <label>From</label>
                <span>${this.escapeHtml(emailData.from || 'Unknown')}</span>
            </div>
            <div class="email-field">
                <label>To</label>
                <span>${this.escapeHtml(emailData.to || 'Unknown')}</span>
            </div>
            ${emailData.cc ? `
            <div class="email-field">
                <label>CC</label>
                <span>${this.escapeHtml(emailData.cc)}</span>
            </div>
            ` : ''}
            <div class="email-field">
                <label>Subject</label>
                <span>${this.escapeHtml(emailData.subject || 'No Subject')}</span>
            </div>
            <div class="email-field">
                <label>Date</label>
                <span>${this.escapeHtml(emailData.date || 'Unknown')}</span>
            </div>
        </div>
        
        <div class="email-body">
            <h3>Message Content</h3>
            <div class="email-body-content">
                ${emailData.bodyHtml || this.escapeHtml(emailData.bodyText) || 'No content available'}
            </div>
        </div>
    `;
    
    if (emailData.attachments && emailData.attachments.length > 0) {
        html += `
            <div class="attachments-section">
                <h3 class="attachments-title">
                    <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                    </svg>
                    Attachments (${emailData.attachments.length})
                </h3>
                <div class="attachment-list">
        `;
        
        emailData.attachments.forEach(attachment => {
            const isEmail = attachment.filename.toLowerCase().endsWith('.eml') || 
                           attachment.filename.toLowerCase().endsWith('.msg');
            
            html += `
                <div class="attachment-item">
                    <div class="attachment-info">
                        ${this.getFileIcon(attachment.filename)}
                        <div class="attachment-details">
                            <div class="attachment-name">${this.escapeHtml(attachment.filename)}</div>
                            <div class="attachment-size">${this.formatFileSize(attachment.size)}</div>
                        </div>
                    </div>
                    ${isEmail ? `
                        <button class="btn btn-info" onclick="emailViewer.openNestedEmail('${attachment.id}', '${attachment.filename}')">
                            <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            View
                        </button>
                    ` : `
                        <button class="btn btn-success" onclick="emailViewer.downloadAttachment('${attachment.id}', '${attachment.filename}')">
                            <svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Download
                        </button>
                    `}
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
    }
    
    this.emailContent.innerHTML = html;
    this.emailContent.classList.remove('hidden');
}
```

### Key Design Changes:

1. **Glassmorphic Effects**: 
   - `.glass-card` class with backdrop-filter blur
   - Semi-transparent backgrounds with rgba colors
   - Subtle inset shadows for depth

2. **Premium Color Scheme**:
   - Dark slate backgrounds (#0f172a, #1e293b)
   - Muted text colors (#94a3b8, #cbd5e1, #e2e8f0)
   - Gradient accents for buttons

3. **Animated Background**:
   - Floating gradient orbs in the background
   - Subtle animations for premium feel

4. **Professional Icons**:
   - All emojis replaced with clean SVG icons
   - Consistent stroke-based icon style
   - Icons integrated into buttons

5. **Typography**:
   - Clean, modern font stack
   - Uppercase labels with letter-spacing
   - Gradient text effect on the main heading

6. **Micro-interactions**:
   - Smooth hover effects with transform
   - Button shine effect on hover
   - Modal animations (fade and slide)

7. **Custom Scrollbar**:
   - Styled scrollbars for content areas
   - Matches the overall dark theme

This design gives you that premium shadcn/ui aesthetic without needing any external libraries - it's all pure CSS with a sophisticated glassmorphic look that's very popular in modern web design.