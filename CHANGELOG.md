# Changelog

All notable changes to `laravel-jev` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-09-20

### Added
- Initial release of `laravel-jev`.
- Fluent facade `Jev` supporting `is()`, `isNot()`, `choose()`, `score()`, and `evaluate()`.
- Multi-question batching via `Jev::analyze()`.
- Global `jev()` helper function.
- Form request validation rules: `JevRule` and `JevNot`.
- Testing fake `Jev::fake()` with assertions (`assertChecked`, `assertNotChecked`, `assertChosen`, `assertNothingClassified`).
- Optional Redis / Cache-store response memoization.
- Support for Laravel 10.x, 11.x, and 12.x on PHP 8.2+.
