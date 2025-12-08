---
description: Quickly log an issue or bug discovered during development
allowed-tools: ["Read", "Write", "Edit"]
---

# Log Issue

Add a new issue to KNOWN_ISSUES.md with the following details:

**Issue to log:** $ARGUMENTS

## Required Information

Gather and document:
1. **Severity**: [CRITICAL/HIGH/MEDIUM/LOW]
2. **Location**: File path and function/component name
3. **Symptom**: What happens when this issue occurs
4. **Root Cause**: Why it happens (if known)
5. **Workaround**: Temporary fix (if any)
6. **Proper Fix**: What needs to be done to resolve it permanently
7. **Reproduction**: Steps to reproduce (if applicable)

## Format

Add to `.claude/docs/KNOWN_ISSUES.md` under "## Active Issues":

```markdown
### [SEVERITY] Brief Title
- **Location**: `path/to/file.ts` - `functionName()`
- **Symptom**: Description of what goes wrong
- **Root Cause**: Why this happens
- **Workaround**: Temporary solution
- **Proper Fix**: What needs to be built
- **Added**: YYYY-MM-DD
```

Confirm when logged.
