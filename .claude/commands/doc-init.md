---
description: Initialize the documentation structure for a new project
allowed-tools: ["Read", "Write", "Bash"]
---

# Initialize Documentation Structure

Set up the `.claude/docs/` folder structure for this project.

## Create Folder Structure

```bash
mkdir -p .claude/docs/services
mkdir -p .claude/docs/components
```

## Create Core Documentation Files

### 1. CHANGELOG.md
```markdown
# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Added
- Initial project setup

### Changed

### Fixed

### Removed

---

## Format Guide
- **Added**: New features
- **Changed**: Changes to existing functionality
- **Fixed**: Bug fixes
- **Removed**: Removed features
- **Security**: Security fixes
- **Deprecated**: Soon-to-be removed features
```

### 2. KNOWN_ISSUES.md
```markdown
# Known Issues

Track bugs, edge cases, and technical debt.

## Active Issues

_No active issues yet._

## Resolved Issues

_Issues that have been fixed are moved here for reference._

---

## Severity Guide
- **CRITICAL**: System unusable, data loss risk
- **HIGH**: Major feature broken, no workaround
- **MEDIUM**: Feature impaired, workaround exists
- **LOW**: Minor inconvenience, cosmetic issues
```

### 3. DECISIONS.md
```markdown
# Architectural Decisions

Record of significant technical decisions and their context.

## Decisions

_No decisions recorded yet._

---

## Template

When adding a decision, include:
- Date and status
- Context (what problem needed solving)
- Decision (what was chosen)
- Alternatives considered
- Consequences (tradeoffs)
```

### 4. ARCHITECTURE.md
```markdown
# Architecture Overview

High-level system architecture and design.

## Tech Stack

| Layer | Technology | Purpose |
|-------|------------|---------|
| Frontend | | |
| Backend | | |
| Database | | |
| Hosting | | |

## System Diagram

```
[Add diagram here]
```

## Key Components

### Component 1
- Purpose:
- Location:
- Dependencies:

## Data Flow

Describe how data moves through the system.

## External Services

List of third-party services and integrations.
See individual docs in `services/` folder.
```

## Update CLAUDE.md

Add the documentation protocol to the project's CLAUDE.md file (create if doesn't exist).

## Confirmation

After setup, list all created files and confirm the structure is ready.
