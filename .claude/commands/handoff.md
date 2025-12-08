---
description: Generate a comprehensive handoff document for another developer
allowed-tools: ["Read", "Write", "Bash"]
---

# Generate Developer Handoff Document

Create a comprehensive handoff document by aggregating all project documentation.

## Steps

### 1. Gather Project Info
- Read package.json or equivalent for project metadata
- Run `!git remote -v` to get repository URL
- Run `!git log --oneline -10` for recent history

### 2. Compile Documentation

Read and synthesize:
- `.claude/docs/ARCHITECTURE.md`
- `.claude/docs/KNOWN_ISSUES.md`
- `.claude/docs/DECISIONS.md`
- `.claude/docs/CHANGELOG.md`
- All files in `.claude/docs/services/`
- All files in `.claude/docs/components/`
- `CLAUDE.md` for development context

### 3. Generate HANDOFF.md

Create a single document at project root:

```markdown
# Project Handoff Document
Generated: [DATE]

## Quick Start

### Prerequisites
- List required tools, versions, accounts

### Setup
1. Clone: `git clone [url]`
2. Install: `[command]`
3. Environment: Copy `.env.example` to `.env` and fill in...
4. Run: `[command]`

## Project Overview
[Brief description of what this project does]

## Architecture Summary
[Condensed version of ARCHITECTURE.md]

## Current State
- Last updated: [date of last commit]
- Active issues: [count from KNOWN_ISSUES.md]
- Recent changes: [summary of CHANGELOG.md]

## Critical Things to Know

### Known Issues (Priority Order)
[List from KNOWN_ISSUES.md, sorted by severity]

### Key Decisions
[Important decisions from DECISIONS.md that affect development]

### External Services
[Summary of all service integrations with links to full docs]

## Development Workflow

### Common Tasks
- How to run tests
- How to deploy
- How to add a new feature

### Code Organization
[Key directories and their purposes]

## Environment Variables
[List all required env vars without values]

## Contacts & Resources
- Repository: [url]
- Hosting Dashboard: [url]
- Documentation: [links]

## Next Steps / Roadmap
[Any planned features or known work remaining]
```

### 4. Output

Save to `HANDOFF.md` in project root and confirm generation.
