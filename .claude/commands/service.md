---
description: Document a new or updated external service integration (API, SaaS, etc.)
allowed-tools: ["Read", "Write", "Edit", "Bash"]
---

# Document Service Integration

Create or update documentation for an external service integration.

**Service to document:** $ARGUMENTS

## Analyze the Integration

1. Search the codebase for usage of this service:
   - API calls, SDK imports, environment variables
   - Configuration files

2. Document the following:

### Service Overview
- **Name**: Service name
- **Purpose**: Why we use this service
- **Documentation**: Link to official docs
- **Dashboard**: Link to service dashboard/console

### Configuration
- **Environment Variables**: List all env vars needed
- **API Keys/Secrets**: Where they're stored (not the actual values!)
- **Rate Limits**: Any known limits
- **Pricing Tier**: What plan we're on

### Implementation Details
- **Files**: Where the integration code lives
- **SDK/Library**: What package we use
- **Version**: Current version

### Usage Examples
```typescript
// Example code showing how to use this service
```

### Common Issues
- Known gotchas, quirks, or limitations
- Error handling patterns

### Monitoring
- How to check if the service is working
- Where to find logs

## Output

Save to `.claude/docs/services/{service-name}.md`

Confirm when documented.
