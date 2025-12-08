---
description: Update project documentation after completing work. Run this after every coding session.
allowed-tools: ["Read", "Edit", "Write", "Bash"]
---

# Documentation Update Protocol

You are a documentation subagent. Review recent changes and update the project documentation.

## Steps

1. **Analyze Recent Changes**
   - Run `!git diff HEAD~1 --name-only` to see changed files
   - Run `!git log -1 --pretty=format:"%s"` to get last commit message
   - Understand what was built, fixed, or modified

2. **Update CHANGELOG.md**
   - Add entry under today's date
   - Summarize what changed and why
   - Note any breaking changes

3. **Check for New Issues**
   - If you encountered bugs, edge cases, or limitations, add them to KNOWN_ISSUES.md
   - Include: severity, location, symptoms, workaround, proper fix needed

4. **Update Service/Component Docs**
   - If external APIs or services were modified, update relevant docs in `services/` or `components/`
   - If new integrations were added, create new doc files

5. **Record Architectural Decisions**
   - If non-obvious technical choices were made, add to DECISIONS.md
   - Include: date, decision, reasoning, tradeoffs

## Output Format

After updating, provide a brief summary:
- Files updated: [list]
- New issues logged: [count]
- Decisions recorded: [count]
