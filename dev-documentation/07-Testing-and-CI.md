# Testing & CI

## Local tests
```bash
composer install
composer test
```

- Uses **Pest** + **Orchestra Testbench**.
- Feature test stubs the HTTP layer with Guzzle's MockHandler.

## CI
A ready-to-use GitHub Actions workflow lives at `.github/workflows/ci.yml`:
- PHP 8.3
- Composer install
- Run `vendor/bin/pest -v`