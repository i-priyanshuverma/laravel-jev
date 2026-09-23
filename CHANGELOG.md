# Changelog

All notable changes to `laravel-jev` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-09-24

### Added
- Added `Priyanshu\LaravelJev\Contracts\Jev` interface defining semantic classification contract.
- Added `Priyanshu\LaravelJev\Contracts\ClientInterface` for transport decoupling.
- Added `Priyanshu\LaravelJev\Exceptions\JevException` marker interface for package-level exception handling.
- Added `Priyanshu\LaravelJev\Enums\QuestionType` backed enum (`noul`, `choice`, `score`).
- Added `Priyanshu\LaravelJev\Events\DecisionEvaluated` domain event for observability and telemetry.
- Added `phpstan.neon.dist` with Level 8 static analysis configuration.
- Added GitHub community health files: `SECURITY.md`, `CONTRIBUTING.md`, PR template, and Issue templates.

### Changed
- Refactored `JevFake` to implement `Contracts\Jev` and swap directly in the service container.
- Refactored `JevNot` validation rule to inherit from `JevRule` (DRY).
- Encapsulated answer parsing in `JevDecision::fromAnswer()` and simplified `BatchResult::decision()`.
- Unified caching logic via clean `remember()` closure helper in `JevManager`.
- Updated `jev()` helper function to return `Contracts\Jev|BatchAnalysis`.

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
