# Email Viewer Pro

A professional web-based email parser application that allows users to view `.eml` and `.msg` files directly in their browser. Features a premium glassmorphic UI design with full attachment handling, nested email support, and powerful user features.

## ✨ Features

### Email Parsing
- **File Support**: Parse both .eml (standard email) and .msg (Outlook) files
- **Bulk Upload**: Upload and process multiple email files simultaneously
- **Nested Emails**: Open .eml/.msg attachments directly in the app
- **Attachment Handling**: Download all attachment types

### User Interface
- **Glassmorphic Design**: Premium dark theme with animated gradients
- **Drag & Drop**: Intuitive file upload interface
- **Email List**: Searchable sidebar for easy navigation
- **Fullscreen Viewer**: Expand emails with zoom controls (50-200%)
- **Pop-Out Windows**: Open emails in separate windows for multitasking
- **Collapsible Recipients**: Auto-collapse for 3+ email addresses

### Power User Features
- **Copy Email Addresses**: Click any email address to copy to clipboard
- **Visual Feedback**: Hover highlights and copy confirmations
- **Responsive Design**: Works on desktop and mobile
- **Auto-Collapse Upload**: Upload section minimizes after first email

### Security
- **HTML Sanitization**: BeautifulSoup removes malicious content
- **XSS Protection**: All user input escaped
- **CORS Enabled**: Secure cross-origin requests
- **File Validation**: Type and size checks

## 🚀 Tech Stack

### Frontend
- HTML5, CSS3 (Glassmorphic Design)
- Vanilla JavaScript (ES6+)
- No framework dependencies

### Backend
- Python 3.13
- Flask 3.0
- extract-msg (reliable MSG parsing)
- BeautifulSoup4 (HTML sanitization)

## 📦 Local Development

### Requirements
- Python 3.11+
- pip or pip3

### Setup

1. **Clone the repository**
```bash
git clone https://github.com/edwinlov3tt/email-viewer.git
cd email-viewer
```

2. **Create virtual environment**
```bash
python3 -m venv venv
source venv/bin/activate  # On Windows: venv\Scripts\activate
```

3. **Install dependencies**
```bash
pip install -r requirements.txt
```

4. **Run the application**
```bash
# Terminal 1: Run Flask backend
python app.py

# Terminal 2: Run frontend server
python -m http.server 8080
```

5. **Open in browser**
```
http://localhost:8080
```

## 🌐 Deployment (Railway)

### Prerequisites
- GitHub account
- Railway account (free tier available)

### Deploy Steps

1. **Push to GitHub**
```bash
git add .
git commit -m "Initial commit"
git push -u origin main
```

2. **Deploy to Railway**
```bash
# Using Railway CLI
railway login
railway init
railway up
```

Or connect your GitHub repo in the Railway dashboard.

3. **Set Environment Variables**
In Railway dashboard, add:
```
FLASK_ENV=production
```

4. **Access Your App**
Railway will provide a URL like: `https://your-app.up.railway.app`

## 📁 Project Structure

```
email-viewer/
├── app.py                 # Flask API backend
├── index.html            # Main application UI
├── js/
│   └── app.js           # Frontend logic
├── uploads/             # Temporary file storage
├── requirements.txt     # Python dependencies
├── Procfile            # Railway deployment config
├── runtime.txt         # Python version
├── .gitignore          # Git ignore rules
└── README.md           # This file
```

## 🎯 Usage

### Single File Upload
1. Drag and drop an .eml or .msg file
2. Or click "Browse Files"
3. Email displays instantly

### Bulk Upload
1. Click "Bulk Upload" or select multiple files
2. Click "Upload All" to process
3. Navigate using the sidebar

### Power Features
- **Copy Emails**: Click any email address to copy
- **Expand**: Click "Expand" for fullscreen view with zoom
- **Pop Out**: Open email in separate window
- **Collapse Recipients**: Click count badge to expand 3+ recipients

## 🔧 Configuration

### Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| `PORT` | 5000 | Flask server port |
| `FLASK_ENV` | development | Set to `production` for deployment |
| `MAX_UPLOAD_SIZE` | 10MB | Maximum file upload size |

### File Limits
- Maximum file size: 10MB per file
- Supported formats: .eml, .msg
- No limit on number of attachments

## 🐛 Troubleshooting

### MSG Files Not Parsing
- The Python `extract-msg` library is very reliable
- If issues persist, check file corruption

### Uploads Failing
- Check file size (10MB limit)
- Verify file extension (.eml or .msg)
- Check browser console for errors

### API Connection Issues
- Ensure Flask backend is running on port 5000
- Check CORS settings in app.py
- Verify firewall isn't blocking connections

## 🌟 Browser Compatibility

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers supported

## 📝 API Endpoints

### `POST /api/parse-email`
Parse an uploaded email file
- **Input**: FormData with `email` file
- **Output**: Email headers, body, attachments

### `GET /api/download-attachment`
Download an attachment
- **Params**: `email_id`, `attachment_id`
- **Output**: File download

### `POST /api/parse-nested-email`
Parse a nested email attachment
- **Input**: `email_id`, `attachment_id`
- **Output**: Parsed nested email data

### `GET /health`
Health check endpoint
- **Output**: `{"status": "healthy"}`

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This project is provided as-is for educational and commercial use.

## 🙏 Credits

Built with:
- [extract-msg](https://github.com/TeamMsgExtractor/msg-extractor) - Reliable MSG parsing
- [Flask](https://flask.palletsprojects.com/) - Python web framework
- [BeautifulSoup4](https://www.crummy.com/software/BeautifulSoup/) - HTML sanitization

---

**Deployed with Railway** 🚂
