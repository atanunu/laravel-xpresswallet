# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]
### Added
- Merchant endpoint suite (password change, verification, activation resend, registration completion, profile, access keys, account mode, summary, wallet).
- Transfers endpoints (banks list/resolve, bank transfer single/customer/batch, wallet transfer).
- Cards endpoints (setup, list, activate, balance, fund).
- Team endpoints (invitations CRUD + resend + accept, members, merchants list/switch, permissions, roles).
- User password change endpoint method.
- Dedicated exceptions: `PasswordChangeException`, `PasswordResetException`, `VerificationException` with automatic mapping in client.
- Coverage dashboard updates for all new endpoints and statuses.
- Documentation: usage examples for password & verification flows; expanded security guidance.
- Mutation testing config enhancements (additional mutator, ignores for trivial exception wrappers).

### Changed
- Client error mapping now inspects URI to throw domain-specific exceptions before generic ApiException.
- Added generic `patch()` helper and accessors for new endpoint groups.

### Tests
- Added feature test for user password change.
- (Planned) Exception mapping tests (pending).

### Security
- Documented password policy & verification code handling best practices.

### Next
- Add unit tests for new exception mappings (422/400 scenarios) & remove ignores from Infection config.
- Expand webhook signature strategies (multiple algorithms) and add Merchant-specific event examples.
