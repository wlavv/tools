# LSG AI Gateway Admin Backups

The Laravel Webtools side expects the AI Gateway to expose protected admin endpoints for backup management.

Required AI Server environment:

```env
LSG_AI_ADMIN_TOKEN=token_admin_muito_forte
```

Authentication header:

```http
x-lsg-ai-admin-token: TOKEN_ADMIN
```

Expected endpoints:

```txt
GET    /api/admin/backups
POST   /api/admin/backups/create
GET    /api/admin/backups/{filename}
GET    /api/admin/backups/{filename}/download
GET    /api/admin/backups/{filename}/checksum
GET    /api/admin/backups/{filename}/manifest
GET    /api/admin/backups/logs?type=backup&lines=100
DELETE /api/admin/backups/{filename}
```

Server paths:

```txt
/opt/lsg-ai-stack/scripts/backup.sh
/opt/lsg-ai-stack/backups
/opt/lsg-ai-stack/logs/backup.log
/opt/lsg-ai-stack/logs/cron-backup.log
```

Security rules:

- Validate filenames with a strict allow-list.
- Accept only names starting with `lsg-ai-stack-backup-`.
- Accept only `.tar.gz`, `.tar.gz.sha256`, and `.manifest.txt`.
- Reject paths, absolute paths, `../`, slash and backslash characters.
- Use `FileResponse` only after validation.
- Use `sha256sum -c` or the `.sha256` file for checksum validation.
- Limit logs to a maximum number of lines.
- Do not implement restore from Webtools in this phase.

FastAPI authentication sketch:

```python
from fastapi import Header, HTTPException

def require_admin_token(x_lsg_ai_admin_token: str | None = Header(default=None)):
    if not x_lsg_ai_admin_token or x_lsg_ai_admin_token != settings.LSG_AI_ADMIN_TOKEN:
        raise HTTPException(status_code=403, detail="Invalid admin token")
```
