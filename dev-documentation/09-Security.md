# Security Notes

- Keep `.env` secrets out of version control.
- Prefer **Redis** cache to share tokens across workers/instances.
- Consider masking PII before enabling response body logging.
- Rotate credentials regularly; `xpress:refresh` rotates tokens safely.
- Use Laravel's rate limiting / circuit breakers for your public endpoints that invoke the package.

## Password & verification handling

- Catch `PasswordChangeException` or `PasswordResetException` to return a safe validation error to clients instead of leaking raw messages.
- On merchant verification flows, handle `VerificationException` and throttle repeated attempts to mitigate brute force on codes.
- Avoid logging raw reset / verification codes; mask them if you must audit attempts.

## Recommended password policy

Enforce (application-side) before calling `changePassword`:
- Minimum length 12 characters
- At least 1 upper, 1 lower, 1 digit, 1 symbol
- Reject commonly breached passwords (use HaveIBeenPwned k-anonymity or a local list).

## Transport & storage

- Always use HTTPS (the SDK assumes TLS; do not override `base_uri` to plain HTTP).
- Tokens are masked in logs by default; keep `xpresswallet.mask_tokens=true`.
- Rotate access keys via `merchant()->generateAccessKeys()` when staff changes occur.