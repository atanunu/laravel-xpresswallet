# Troubleshooting

### 401/403 Unauthorized
- Run `php artisan xpress:login` again.
- Verify `.env` credentials and `XPRESSWALLET_BASE_URL`.

### Missing tokens: "Call login() first."
- Ensure `xpress_tokens` table exists and has rows (`php artisan migrate`).
- Make sure cache store is configured (e.g., Redis).

### API errors from provider
- Check `api_call_logs` for full request/response context.
- Enable `XPRESSWALLET_LOG_BODIES=true` temporarily (avoid in prod).

### SSL / base URL issues
- Ensure `XPRESSWALLET_BASE_URL` includes protocol and no trailing slash (the client normalizes it).