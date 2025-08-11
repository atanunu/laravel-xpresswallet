# Changelog

All notable changes to this project will be documented in this file.

## [0.2.1] - 2025-08-10
### Added
- Psalm static analysis configuration and CI step.
- Dependabot configuration (Composer & GitHub Actions).
- Mutation score badge and public endpoints dashboard badge in README.

### Changed
- Disabled failing CodeQL workflow pending general PHP availability.
- Normalized line endings via `.gitattributes`.
- Removed explicit `version` from `composer.json`; tags now define releases.
- Updated README badges and static analysis section.

### Fixed
- Style inconsistencies resolved via Laravel Pint.

### Documentation
- Published public endpoints coverage dashboard (GitHub Pages) and linked in README.

## [0.3.0] - 2025-08-11
### Added
- Optional built-in API route layer with full Customers, Wallets, Transactions, Transfers, Cards, Merchant, Team proxy endpoints (config toggle, prefix & middleware configurable).
- Form Request validation classes for all write / parameterized operations (input sanitisation & 422 responses).
- README section documenting enabling & securing built-in routes.
- Comprehensive API Guide (`docs/api-guide.html`) enumerating each route, validation rules, sample requests & responses.
- PHPDoc updates for facade to include new endpoint groups (transfers, cards, merchant, team).
- Route feature tests (registration, validation failures, success passthrough stubs).

### Changed
- Service provider now conditionally loads routes based on config to avoid accidental exposure.

### Documentation
- Expanded API endpoints documentation with route layer mapping & examples.

### Tests
- Added feature tests ensuring route group prefix & middleware applied and 422 structure on invalid payloads.

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
