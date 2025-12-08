---
description: Full project audit - analyze codebase and populate all documentation
allowed-tools: ["Read", "Write", "Edit", "Bash", "Grep", "Glob"]
---

# Full Project Audit

Perform a comprehensive analysis of the entire codebase and populate all documentation.

## Phase 1: Discovery

### 1.1 Project Metadata
- Read `package.json`, `requirements.txt`, `Cargo.toml`, or equivalent
- Identify project name, version, dependencies
- Run `!git remote -v` for repository info

### 1.2 Directory Structure
- Map the full directory tree (excluding node_modules, .git, dist, build)
- Identify key directories: src, api, workers, components, utils, etc.

### 1.3 Tech Stack Detection
Scan for:
- **Frontend**: React, Vue, Svelte, vanilla JS (check for .jsx, .tsx, .vue, .svelte)
- **Backend**: Express, FastAPI, Hono, Next.js API routes
- **Database**: Supabase, PostgreSQL, MongoDB, D1 (check env vars, imports)
- **Hosting**: Vercel (vercel.json), Cloudflare (wrangler.toml), Railway, etc.
- **Build tools**: Vite, Webpack, esbuild, Turbopack

### 1.4 External Services
Search for:
- API keys and service URLs in env files (.env.example, .env.local)
- SDK imports (supabase, stripe, openai, cloudflare, etc.)
- Configuration files (wrangler.toml, vercel.json, railway.json)

---

## Phase 2: Code Analysis

### 2.1 Entry Points
- Find main entry files (index.ts, main.tsx, app.py, etc.)
- Trace the application startup flow

### 2.2 API Routes
- Find all API endpoints (routes/, api/, pages/api/, etc.)
- Document: method, path, purpose, request/response shape

### 2.3 Key Components/Modules
- Identify core business logic files
- Document major components and their responsibilities

### 2.4 Environment Variables
- Compile ALL env vars used across the codebase
- Categorize: required vs optional, by service

### 2.5 Potential Issues
Scan for:
- TODO/FIXME/HACK/XXX comments
- console.log/print statements (potential debug leftovers)
- Empty catch blocks
- Hardcoded values that should be env vars
- Missing error handling
- Large functions (>100 lines)
- Deprecated package warnings

---

## Phase 3: Git History Analysis

### 3.1 Recent Changes
- Run `!git log --oneline -30` for recent commits
- Summarize major features/fixes from past month

### 3.2 Contributors
- Run `!git shortlog -sn --all` for contributor list

### 3.3 Active Areas
- Run `!git log --pretty=format: --name-only -30 | sort | uniq -c | sort -rn | head -20`
- Identify most frequently changed files

---

## Phase 4: Documentation Generation

### 4.1 Update ARCHITECTURE.md
Fill in:
- Actual tech stack table
- Real directory structure
- Component descriptions
- Data flow based on code analysis
- Environment variables list

### 4.2 Update KNOWN_ISSUES.md
Add entries for:
- All TODO/FIXME comments found (with file locations)
- Potential code smells identified
- Missing error handling
- Any hardcoded values

### 4.3 Update CHANGELOG.md
Based on git history:
- Group recent commits by feature/fix
- Create entries for the past 2-4 weeks

### 4.4 Create Service Documentation
For each external service detected:
- Create `.claude/docs/services/{service}.md`
- Fill in: purpose, env vars, usage locations, links

### 4.5 Create Component Documentation
For major components:
- Create `.claude/docs/components/{component}.md`
- Document purpose, key functions, dependencies

### 4.6 Update DECISIONS.md
If obvious architectural decisions are visible:
- Framework choices
- Database design patterns
- Deployment strategy

---

## Phase 5: Summary Report

After completing all updates, provide:

```markdown
# Project Audit Complete

## Project Overview
- **Name**: [name]
- **Type**: [web app/API/extension/etc]
- **Tech Stack**: [summary]

## Documentation Created/Updated
- [x] ARCHITECTURE.md - [status]
- [x] KNOWN_ISSUES.md - [X issues logged]
- [x] CHANGELOG.md - [entries added]
- [x] DECISIONS.md - [X decisions recorded]
- [x] services/*.md - [list created]
- [x] components/*.md - [list created]

## Key Findings
1. [Important finding]
2. [Another finding]

## Immediate Attention Needed
- [Critical issues or missing pieces]

## Recommended Next Steps
1. [Action item]
2. [Action item]
```
