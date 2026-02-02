/**
 * Email Viewer Application
 *
 * Handles email file uploads, parsing, and display with bulk upload support
 */

class EmailViewer {
    constructor() {
        this.currentEmail = null;
        this.emailList = [];
        this.uploadQueue = [];
        this.activeEmailId = null;
        // Use same origin for production, localhost for development
        this.apiBase = window.location.hostname === 'localhost' ?
            'http://localhost:5000/api' :
            `${window.location.protocol}//${window.location.host}/api`;

        this.initializeElements();
        this.initializeEventListeners();
    }

    initializeElements() {
        // Upload elements
        this.uploadSection = document.getElementById('uploadSection');
        this.sectionToggle = document.getElementById('sectionToggle');
        this.uploadArea = document.getElementById('uploadArea');
        this.fileInput = document.getElementById('fileInput');
        this.browseBtn = document.getElementById('browseBtn');
        this.bulkBtn = document.getElementById('bulkBtn');

        // Fullscreen modal elements
        this.fullscreenModal = document.getElementById('fullscreenModal');
        this.fullscreenContent = document.getElementById('fullscreenContent');
        this.zoomLevel = document.getElementById('zoomLevel');
        this.currentZoom = 100;

        // Queue elements
        this.uploadQueue = document.getElementById('uploadQueue');
        this.queueList = document.getElementById('queueList');
        this.uploadAllBtn = document.getElementById('uploadAllBtn');
        this.clearQueueBtn = document.getElementById('clearQueueBtn');

        // Email list elements
        this.emailListDiv = document.getElementById('emailList');
        this.emailListItems = document.getElementById('emailListItems');
        this.searchInput = document.getElementById('searchInput');

        // Viewer elements
        this.emailViewer = document.getElementById('emailViewer');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.emailContent = document.getElementById('emailContent');
        this.errorMessage = document.getElementById('errorMessage');

        // Modal elements
        this.nestedEmailModal = document.getElementById('nestedEmailModal');
        this.closeModal = document.getElementById('closeModal');
        this.nestedEmailContent = document.getElementById('nestedEmailContent');
    }

    initializeEventListeners() {
        // Drag and drop
        this.uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.uploadArea.classList.add('dragover');
        });

        this.uploadArea.addEventListener('dragleave', (e) => {
            e.preventDefault();
            this.uploadArea.classList.remove('dragover');
        });

        this.uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            this.uploadArea.classList.remove('dragover');
            this.handleFiles(e.dataTransfer.files);
        });

        // Click to upload
        this.browseBtn.addEventListener('click', () => {
            this.fileInput.click();
        });

        this.bulkBtn.addEventListener('click', () => {
            this.fileInput.multiple = true;
            this.fileInput.click();
        });

        this.uploadArea.addEventListener('click', (e) => {
            if (e.target === this.uploadArea) {
                this.fileInput.click();
            }
        });

        this.fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.handleFiles(e.target.files);
            }
        });

        // Queue actions
        this.uploadAllBtn?.addEventListener('click', () => {
            this.uploadAll();
        });

        this.clearQueueBtn?.addEventListener('click', () => {
            this.clearQueue();
        });

        // Search functionality
        this.searchInput?.addEventListener('input', (e) => {
            this.filterEmailList(e.target.value);
        });

        // Section toggle
        this.sectionToggle?.addEventListener('click', () => {
            this.uploadSection.classList.toggle('collapsed');
        });

        // Modal close
        this.closeModal?.addEventListener('click', () => {
            this.closeNestedEmailModal();
        });

        this.nestedEmailModal?.addEventListener('click', (e) => {
            if (e.target === this.nestedEmailModal) {
                this.closeNestedEmailModal();
            }
        });
    }

    handleFiles(files) {
        const fileArray = Array.from(files);

        // Validate files
        const validFiles = fileArray.filter(file => this.validateFile(file));

        if (validFiles.length === 0) {
            return;
        }

        // Single file - upload immediately
        if (validFiles.length === 1) {
            this.uploadSingleFile(validFiles[0]);
        } else {
            // Multiple files - add to queue
            this.addToQueue(validFiles);
        }
    }

    validateFile(file) {
        const validExtensions = ['.eml', '.msg'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

        if (!validExtensions.includes(fileExtension)) {
            this.showError(`Invalid file type for ${file.name}. Please select EML or MSG files.`);
            return false;
        }

        if (file.size > 50 * 1024 * 1024) {
            this.showError(`File ${file.name} exceeds 50MB limit.`);
            return false;
        }

        return true;
    }

    addToQueue(files) {
        files.forEach(file => {
            const queueItem = {
                id: this.generateId(),
                file: file,
                status: 'pending',
                progress: 0
            };
            this.uploadQueue.push(queueItem);
        });

        this.renderQueue();
        document.getElementById('uploadQueue').classList.remove('hidden');
    }

    renderQueue() {
        this.queueList.innerHTML = '';

        this.uploadQueue.forEach(item => {
            const queueElement = document.createElement('div');
            queueElement.className = 'queue-item';
            queueElement.innerHTML = `
                <div class="queue-item-info">
                    <span class="queue-item-name">${this.escapeHtml(item.file.name)}</span>
                    <span class="queue-item-status ${item.status}">${item.status}</span>
                </div>
                ${item.status === 'uploading' ? `
                <div class="progress-bar" style="width: 100px;">
                    <div class="progress-bar-fill" style="width: ${item.progress}%"></div>
                </div>
                ` : ''}
                ${item.status === 'pending' ? `
                <button class="btn btn-sm" onclick="emailViewer.removeFromQueue('${item.id}')">×</button>
                ` : ''}
            `;
            this.queueList.appendChild(queueElement);
        });
    }

    removeFromQueue(itemId) {
        this.uploadQueue = this.uploadQueue.filter(item => item.id !== itemId);
        this.renderQueue();

        if (this.uploadQueue.length === 0) {
            document.getElementById('uploadQueue').classList.add('hidden');
        }
    }

    clearQueue() {
        this.uploadQueue = [];
        this.renderQueue();
        document.getElementById('uploadQueue').classList.add('hidden');
    }

    async uploadAll() {
        const pendingItems = this.uploadQueue.filter(item => item.status === 'pending');

        if (pendingItems.length === 0) {
            return;
        }

        // Prepare FormData for bulk upload
        const formData = new FormData();
        pendingItems.forEach((item, index) => {
            formData.append('emails[]', item.file);
            item.status = 'uploading';
        });

        this.renderQueue();

        try {
            // For now, use individual uploads until bulk is fixed
            for (const item of pendingItems) {
                const formData = new FormData();
                formData.append('email', item.file);

                try {
                    const response = await fetch(`${this.apiBase}/parse-email`, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        item.status = 'success';
                        this.addEmailToList(result.data);
                    } else {
                        item.status = 'error';
                        console.error(`Failed to parse ${item.file.name}: ${result.error}`);
                    }
                } catch (error) {
                    item.status = 'error';
                    console.error(`Upload error for ${item.file.name}:`, error);
                }

                this.renderQueue();
            }

            return;

            // Original bulk upload code (commented out for now)
            const response = await fetch(`${this.apiBase}/bulk-upload.php`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Process successful uploads
                result.results.forEach((fileResult, index) => {
                    const queueItem = pendingItems[index];

                    if (fileResult.success) {
                        queueItem.status = 'success';
                        this.addEmailToList(fileResult.data);
                    } else {
                        queueItem.status = 'error';
                        console.error(`Failed to parse ${fileResult.filename}: ${fileResult.error}`);
                    }
                });

                this.renderQueue();

                // Show first email if available
                if (this.emailList.length > 0 && !this.activeEmailId) {
                    this.displayEmail(this.emailList[0]);
                }

                // Show summary
                this.showSuccess(`Uploaded ${result.summary.success} of ${result.summary.total} files`);
            } else {
                throw new Error(result.error || 'Upload failed');
            }
        } catch (error) {
            console.error('Bulk upload error:', error);
            pendingItems.forEach(item => {
                item.status = 'error';
            });
            this.renderQueue();
            this.showError('Failed to upload files: ' + error.message);
        }
    }

    async uploadSingleFile(file) {
        this.showLoading();

        const formData = new FormData();
        formData.append('email', file);

        try {
            const response = await fetch(`${this.apiBase}/parse-email`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.addEmailToList(result.data);
                this.displayEmail(result.data);
                this.hideLoading();
            } else {
                throw new Error(result.error || 'Failed to parse email');
            }
        } catch (error) {
            this.hideLoading();
            this.showError('Failed to parse email: ' + error.message);
        }
    }

    addEmailToList(emailData) {
        this.emailList.push(emailData);
        this.renderEmailList();
        this.emailListDiv.classList.remove('hidden');

        // Auto-collapse upload section and show toggle
        this.uploadSection.classList.add('has-emails', 'collapsed');
    }

    renderEmailList() {
        this.emailListItems.innerHTML = '';

        this.emailList.forEach(email => {
            const listItem = document.createElement('div');
            listItem.className = 'email-list-item';
            if (email.id === this.activeEmailId) {
                listItem.classList.add('active');
            }

            listItem.innerHTML = `
                <div class="email-list-item-subject">
                    ${this.escapeHtml(email.headers.subject || 'No Subject')}
                </div>
                <div class="email-list-item-from">
                    ${this.escapeHtml(email.headers.from || 'Unknown Sender')}
                </div>
            `;

            listItem.addEventListener('click', () => {
                this.displayEmail(email);
            });

            this.emailListItems.appendChild(listItem);
        });
    }

    filterEmailList(searchTerm) {
        const items = this.emailListItems.querySelectorAll('.email-list-item');
        const term = searchTerm.toLowerCase();

        items.forEach((item, index) => {
            const email = this.emailList[index];
            const subject = (email.headers.subject || '').toLowerCase();
            const from = (email.headers.from || '').toLowerCase();

            if (subject.includes(term) || from.includes(term)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    parseEmailAddresses(emailStr) {
        if (!emailStr) return [];

        // Extract email addresses
        const emailRegex = /([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)/gi;
        const matches = emailStr.match(emailRegex);
        return matches || [];
    }

    makeEmailAddressClickable(emailStr, fieldName = 'email') {
        if (!emailStr) return '';

        const emails = this.parseEmailAddresses(emailStr);

        // If 3 or more emails, make it collapsible
        if (emails.length >= 3) {
            const id = `${fieldName}_${Math.random().toString(36).substr(2, 9)}`;
            let html = `
                <div class="email-list-toggle" onclick="emailViewer.toggleEmailList('${id}')" id="toggle_${id}">
                    <svg class="email-list-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                    <span>${emails.length} recipients</span>
                </div>
                <div class="email-list-items" id="${id}">
            `;

            emails.forEach(email => {
                html += `<span class="email-address email-address-item" onclick="emailViewer.copyEmailAddress('${email}', event)" title="Click to copy">${email}</span>`;
            });

            html += `</div>`;
            return html;
        } else {
            // Less than 3, just make them clickable inline
            return emailStr.replace(/([a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9_-]+)/gi, (email) => {
                return `<span class="email-address" onclick="emailViewer.copyEmailAddress('${email}', event)" title="Click to copy">${email}</span>`;
            });
        }
    }

    toggleEmailList(id) {
        const list = document.getElementById(id);
        const toggle = document.getElementById(`toggle_${id}`);

        if (list && toggle) {
            list.classList.toggle('expanded');
            toggle.classList.toggle('expanded');
        }
    }

    copyEmailAddress(email, event) {
        // Copy to clipboard
        navigator.clipboard.writeText(email).then(() => {
            // Visual feedback
            const element = event.target;
            element.classList.add('copied');

            setTimeout(() => {
                element.classList.remove('copied');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }

    displayEmail(emailData) {
        this.currentEmail = emailData;
        this.activeEmailId = emailData.id;
        this.renderEmailList(); // Update active state

        let html = `
            <div class="email-header">
                <div class="email-field">
                    <label>From:</label>
                    <span>${this.makeEmailAddressClickable(emailData.headers.from || 'Unknown', 'from')}</span>
                </div>
                <div class="email-field">
                    <label>To:</label>
                    <span>${this.makeEmailAddressClickable(emailData.headers.to || 'Unknown', 'to')}</span>
                </div>
                ${emailData.headers.cc ? `
                <div class="email-field">
                    <label>CC:</label>
                    <span>${this.makeEmailAddressClickable(emailData.headers.cc, 'cc')}</span>
                </div>
                ` : ''}
                ${emailData.headers.bcc ? `
                <div class="email-field">
                    <label>BCC:</label>
                    <span>${this.makeEmailAddressClickable(emailData.headers.bcc, 'bcc')}</span>
                </div>
                ` : ''}
                <div class="email-field">
                    <label>Subject:</label>
                    <span>${this.escapeHtml(emailData.headers.subject || 'No Subject')}</span>
                </div>
                <div class="email-field">
                    <label>Date:</label>
                    <span>${this.escapeHtml(emailData.headers.date || 'Unknown')}</span>
                </div>
            </div>

            <div class="email-body">
                <h3>Message Content</h3>
                <div class="email-body-actions">
                    <button class="btn btn-primary btn-icon" onclick="emailViewer.openFullscreen()">
                        <svg class="icon icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                        Expand
                    </button>
                    <button class="btn btn-info btn-icon" onclick="emailViewer.popoutEmail()">
                        <svg class="icon icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Pop Out
                    </button>
                </div>
                <div class="email-body-content">
                    ${emailData.body.html || this.escapeHtml(emailData.body.text) || 'No content available'}
                </div>
            </div>
        `;

        if (emailData.attachments && emailData.attachments.length > 0) {
            // Count image attachments
            const imageCount = emailData.attachments.filter(att =>
                att.content_type && att.content_type.startsWith('image/')
            ).length;

            html += `
                <div class="attachments-section">
                    <div class="attachments-title">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                        </svg>
                        Attachments (${emailData.attachments.length})
                        ${imageCount > 0 ? `
                            <button class="btn btn-primary" style="margin-left: auto; font-size: 0.8rem; padding: 6px 12px;" onclick="emailViewer.downloadImagesZip('${emailData.id}')">
                                <svg class="icon icon-sm" style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download All Images (${imageCount})
                            </button>
                        ` : ''}
                    </div>
                    <div class="attachment-list">
            `;

            emailData.attachments.forEach(attachment => {
                const isEmail = attachment.filename.toLowerCase().endsWith('.eml') ||
                               attachment.filename.toLowerCase().endsWith('.msg');

                html += `
                    <div class="attachment-item">
                        <div class="attachment-info">
                            <div class="attachment-icon">${this.getFileIcon(attachment.filename)}</div>
                            <div class="attachment-details">
                                <div class="attachment-name">${this.escapeHtml(attachment.filename)}</div>
                                <div class="attachment-size">${this.formatFileSize(attachment.size)}</div>
                            </div>
                        </div>
                        ${isEmail ? `
                            <button class="btn btn-info" onclick="emailViewer.openNestedEmail('${emailData.id}', '${attachment.id}', '${attachment.filename}')">
                                Open Email
                            </button>
                        ` : `
                            <button class="btn btn-success" onclick="emailViewer.downloadAttachment('${emailData.id}', '${attachment.id}', '${attachment.filename}')">
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
        this.emailViewer.classList.remove('hidden');
    }

    async openNestedEmail(emailId, attachmentId, filename) {
        try {
            // Parse the nested email on server
            const formData = new FormData();
            formData.append('email_id', emailId);
            formData.append('attachment_id', attachmentId);

            const response = await fetch(`${this.apiBase}/parse-nested-email`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.displayNestedEmail(result.data);
            } else {
                alert('Failed to parse nested email: ' + result.error);
            }
        } catch (error) {
            console.error('Error opening nested email:', error);
            alert('Failed to open nested email: ' + error.message);
        }
    }

    displayNestedEmail(emailData) {
        // Store current email temporarily for popout
        const previousEmail = this.currentEmail;
        this.currentEmail = emailData;

        // Build the same email display but in a modal
        let html = `
            <h2 style="color: #e2e8f0; margin-bottom: 30px; border-bottom: 2px solid rgba(139, 92, 246, 0.3); padding-bottom: 15px;">
                ${this.escapeHtml(emailData.headers.subject || 'No Subject')}
            </h2>
            <div class="email-header">
                <div class="email-field">
                    <label>From:</label>
                    <span>${this.makeEmailAddressClickable(emailData.headers.from || 'Unknown')}</span>
                </div>
                <div class="email-field">
                    <label>To:</label>
                    <span>${this.makeEmailAddressClickable(emailData.headers.to || 'Unknown')}</span>
                </div>
                ${emailData.headers.cc ? `
                <div class="email-field">
                    <label>CC:</label>
                    <span>${this.makeEmailAddressClickable(emailData.headers.cc)}</span>
                </div>
                ` : ''}
                <div class="email-field">
                    <label>Date:</label>
                    <span>${this.escapeHtml(emailData.headers.date || 'Unknown')}</span>
                </div>
            </div>

            <div class="email-body">
                <h3>Message Content</h3>
                <div class="email-body-actions">
                    <button class="btn btn-info btn-icon" onclick="emailViewer.popoutEmail()">
                        <svg class="icon icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Pop Out
                    </button>
                </div>
                <div class="email-body-content">
                    ${emailData.body.html || this.escapeHtml(emailData.body.text) || 'No content available'}
                </div>
            </div>
        `;

        // Store previous email for restoration
        this._previousEmail = previousEmail;

        if (emailData.attachments && emailData.attachments.length > 0) {
            html += `
                <div class="attachments-section">
                    <div class="attachments-title">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                        </svg>
                        Attachments (${emailData.attachments.length})
                    </div>
                    <div class="attachment-list">
            `;

            emailData.attachments.forEach(attachment => {
                html += `
                    <div class="attachment-item">
                        <div class="attachment-info">
                            <div class="attachment-icon">${this.getFileIcon(attachment.filename)}</div>
                            <div class="attachment-details">
                                <div class="attachment-name">${this.escapeHtml(attachment.filename)}</div>
                                <div class="attachment-size">${this.formatFileSize(attachment.size)}</div>
                            </div>
                        </div>
                        <button class="btn btn-success" onclick="emailViewer.downloadAttachment('${emailData.id}', '${attachment.id}', '${attachment.filename}')">
                            Download
                        </button>
                    </div>
                `;
            });

            html += `
                    </div>
                </div>
            `;
        }

        this.nestedEmailContent.innerHTML = html;
        this.nestedEmailModal.classList.remove('hidden');
    }

    downloadAttachment(emailId, attachmentId, filename) {
        const downloadUrl = `${this.apiBase}/download-attachment?email_id=${emailId}&attachment_id=${attachmentId}`;

        // Create temporary link for download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    downloadImagesZip(emailId) {
        window.location.href = `${this.apiBase}/download-images-zip?email_id=${emailId}`;
    }

    getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const icons = {
            pdf: '📄',
            doc: '📝',
            docx: '📝',
            xls: '📊',
            xlsx: '📊',
            zip: '🗜️',
            jpg: '🖼️',
            png: '🖼️',
            eml: '✉️',
            msg: '✉️'
        };
        return icons[ext] || '📎';
    }

    formatFileSize(bytes) {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }

    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    generateId() {
        return 'id_' + Math.random().toString(36).substr(2, 9);
    }

    showLoading() {
        this.emailViewer.classList.remove('hidden');
        this.loadingSpinner.classList.remove('hidden');
        this.emailContent.style.display = 'none';
        this.errorMessage.classList.add('hidden');
    }

    hideLoading() {
        this.loadingSpinner.classList.add('hidden');
        this.emailContent.style.display = 'block';
    }

    showError(message) {
        this.errorMessage.textContent = message;
        this.errorMessage.classList.remove('hidden');
        this.emailViewer.classList.remove('hidden');
        this.loadingSpinner.classList.add('hidden');
        this.emailContent.style.display = 'none';

        setTimeout(() => {
            this.errorMessage.classList.add('hidden');
        }, 5000);
    }

    showSuccess(message) {
        // You can implement a success notification here
        console.log('Success:', message);
    }

    closeNestedEmailModal() {
        this.nestedEmailModal.classList.add('hidden');
    }

    openFullscreen() {
        if (!this.currentEmail) return;

        // Set the fullscreen content
        this.fullscreenContent.innerHTML = this.currentEmail.body.html ||
            this.escapeHtml(this.currentEmail.body.text) ||
            'No content available';

        // Reset zoom
        this.currentZoom = 100;
        this.updateZoom();

        // Show modal
        this.fullscreenModal.classList.remove('hidden');

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    closeFullscreen() {
        this.fullscreenModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    zoomIn() {
        if (this.currentZoom < 200) {
            this.currentZoom += 10;
            this.updateZoom();
        }
    }

    zoomOut() {
        if (this.currentZoom > 50) {
            this.currentZoom -= 10;
            this.updateZoom();
        }
    }

    updateZoom() {
        this.fullscreenContent.style.transform = `scale(${this.currentZoom / 100})`;
        this.fullscreenContent.style.transformOrigin = 'top left';
        this.zoomLevel.textContent = `${this.currentZoom}%`;
    }

    popoutEmail() {
        if (!this.currentEmail) return;

        const email = this.currentEmail;

        // Build the HTML for the popup window
        const popupHtml = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${this.escapeHtml(email.headers.subject || 'Email')}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Roboto", sans-serif;
            background: #f8fafc;
            padding: 20px;
        }
        .email-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .email-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .email-field {
            display: flex;
            margin-bottom: 12px;
            font-size: 14px;
        }
        .email-field label {
            font-weight: 600;
            color: #64748b;
            min-width: 80px;
            margin-right: 12px;
            text-transform: uppercase;
            font-size: 12px;
        }
        .email-field span {
            color: #1e293b;
            flex: 1;
        }
        .email-address {
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.2s ease;
            display: inline-block;
        }
        .email-address:hover {
            background: rgba(139, 92, 246, 0.1);
        }
        .email-address.copied {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }
        .email-body {
            padding: 20px 0;
        }
        .email-body h3 {
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .email-body-content {
            background: #fff;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            line-height: 1.7;
            color: #1e293b;
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }
        .email-body-content * {
            max-width: 100% !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
        }
        .email-body-content img {
            max-width: 100% !important;
            height: auto !important;
        }
        .email-list-toggle {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #8b5cf6;
            font-size: 0.875rem;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s ease;
        }
        .email-list-toggle:hover {
            background: rgba(139, 92, 246, 0.1);
        }
        .email-list-toggle-icon {
            width: 12px;
            height: 12px;
            transition: transform 0.2s ease;
        }
        .email-list-toggle.expanded .email-list-toggle-icon {
            transform: rotate(180deg);
        }
        .email-list-items {
            display: none;
            margin-top: 8px;
        }
        .email-list-items.expanded {
            display: block;
        }
        .email-address-item {
            display: block;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="email-field">
                <label>From:</label>
                <span>${this.makeEmailAddressClickable(email.headers.from || 'Unknown')}</span>
            </div>
            <div class="email-field">
                <label>To:</label>
                <span>${this.makeEmailAddressClickable(email.headers.to || 'Unknown')}</span>
            </div>
            ${email.headers.cc ? `
            <div class="email-field">
                <label>CC:</label>
                <span>${this.makeEmailAddressClickable(email.headers.cc)}</span>
            </div>
            ` : ''}
            <div class="email-field">
                <label>Subject:</label>
                <span>${this.escapeHtml(email.headers.subject || 'No Subject')}</span>
            </div>
            <div class="email-field">
                <label>Date:</label>
                <span>${this.escapeHtml(email.headers.date || 'Unknown')}</span>
            </div>
        </div>
        <div class="email-body">
            <h3>Message Content</h3>
            <div class="email-body-content">
                ${email.body.html || this.escapeHtml(email.body.text) || 'No content available'}
            </div>
        </div>
    </div>
    <script>
        function copyEmailAddress(email, event) {
            navigator.clipboard.writeText(email).then(() => {
                const element = event.target;
                element.classList.add('copied');
                setTimeout(() => {
                    element.classList.remove('copied');
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }

        function toggleEmailList(id) {
            const list = document.getElementById(id);
            const toggle = document.getElementById('toggle_' + id);

            if (list && toggle) {
                list.classList.toggle('expanded');
                toggle.classList.toggle('expanded');
            }
        }
    </script>
</body>
</html>
        `;

        // Open popup window (like SSO popup)
        const width = 800;
        const height = 600;
        const left = (screen.width - width) / 2;
        const top = (screen.height - height) / 2;

        const popup = window.open(
            '',
            `email_${email.id}`,
            `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,location=no`
        );

        if (popup) {
            // Fix onclick handlers to use local functions instead of emailViewer
            const fixedHtml = popupHtml
                .replace(/onclick="emailViewer\.toggleEmailList/g, 'onclick="toggleEmailList')
                .replace(/onclick="emailViewer\.copyEmailAddress/g, 'onclick="copyEmailAddress');

            popup.document.write(fixedHtml);
            popup.document.close();
        } else {
            alert('Please allow popups for this site to use the pop-out feature.');
        }
    }
}

// Initialize the application
let emailViewer;
document.addEventListener('DOMContentLoaded', () => {
    emailViewer = new EmailViewer();
});