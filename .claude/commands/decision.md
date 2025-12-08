---
description: Record an architectural or technical decision with context
allowed-tools: ["Read", "Write", "Edit"]
---

# Record Decision

Document a technical or architectural decision in DECISIONS.md.

**Decision to record:** $ARGUMENTS

## Gather Context

For every decision, capture:

1. **Title**: Brief name for the decision
2. **Date**: Today's date
3. **Context**: What situation required this decision
4. **Decision**: What was chosen
5. **Alternatives Considered**: What other options existed
6. **Reasoning**: Why this option was selected
7. **Tradeoffs**: What we gained and what we gave up
8. **Consequences**: What this means for future development

## Format

Add to `.claude/docs/DECISIONS.md`:

```markdown
## [Title]
- **Date**: YYYY-MM-DD
- **Status**: Accepted

### Context
What situation or problem required a decision.

### Decision
We will use [X] because [Y].

### Alternatives Considered
1. **Option A**: Description - rejected because...
2. **Option B**: Description - rejected because...

### Consequences
- Positive: What we gain
- Negative: What we give up or must handle
- Neutral: Other implications
```

Confirm when recorded.
