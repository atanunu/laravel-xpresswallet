# Atanunu Laravel XpressWallet — Internal Developer Docs

These markdown files are **internal development notes**. They are excluded from the distributed Composer archive via `.gitattributes` (`/dev-documentation export-ignore`) so end-users installing from Packagist do not receive them.

This package provides a **vendor-ready** Laravel 11/12 integration for the **Providus Xpress Wallet API**. It includes secure token handling, request/response logging, auditing, example controllers, publishable config & migrations, tests, CI, optional auto‑registered API proxy routes and an HTML API guide.

> Package: `atanunu/laravel-xpresswallet`  
> Namespace: `Atanunu\XpressWallet`  
> Default Base URL: `https://payment.xpress-wallet.com` (override with `XPRESSWALLET_BASE_URL`).

For the public HTML API Guide (auto‑registered routes) see `docs/api-guide.html` which is published to GitHub Pages. If the link is not visible on the Pages site ensure `api-guide.html` is present under the `docs/` folder (it is) and the site is configured to use the `docs` directory.