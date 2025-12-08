---
description: Check documentation status and health
allowed-tools: ["Read", "Bash"]
---

# Documentation Status Check

Analyze the current state of project documentation.

## Checks to Perform

### 1. Documentation Structure
- Verify `.claude/docs/` exists
- Check for required files:
  - CHANGELOG.md
  - KNOWN_ISSUES.md  
  - DECISIONS.md
  - ARCHITECTURE.md

### 2. Content Freshness
- Run `!git log -1 --format="%ar" -- .claude/docs/` to see when docs were last updated
- Compare to last code commit: `!git log -1 --format="%ar"`
- Flag if docs are more than 1 week older than code

### 3. Issue Count
- Count active issues in KNOWN_ISSUES.md by severity
- Highlight any CRITICAL or HIGH severity issues

### 4. Service Documentation
- List all files in `.claude/docs/services/`
- Cross-reference with external service usage in code

### 5. Missing Documentation
- Check for undocumented env vars (in code but not in ARCHITECTURE.md)
- Look for new files/features not reflected in docs

## Output Format

```markdown
## Documentation Status Report

**Generated**: [timestamp]
**Last Doc Update**: [relative time]
**Last Code Update**: [relative time]

### Structure ✅/⚠️
- [x] CHANGELOG.md
- [x] KNOWN_ISSUES.md
- [ ] DECISIONS.md (missing/empty)
- [x] ARCHITECTURE.md

### Active Issues
- CRITICAL: 0
- HIGH: 1
- MEDIUM: 3
- LOW: 2

### Service Docs
- cloudflare.md ✅
- supabase.md ⚠️ (outdated)

### Recommendations
1. [Specific action needed]
2. [Another action]
```

Provide this status summary.
