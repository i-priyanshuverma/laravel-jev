# Contributing to Laravel Jev

Thank you for considering contributing to `laravel-jev`!

## Development Setup

1. Clone the repository:
   ```bash
   git clone git@github.com:i-priyanshuverma/laravel-jev.git
   cd laravel-jev
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run the test suite:
   ```bash
   composer test
   # or
   vendor/bin/phpunit
   ```

## Code Style

Please ensure your code conforms to PSR-12 and Laravel coding standards using Laravel Pint:

```bash
vendor/bin/pint
```

## Pull Request Process

1. Create a feature branch for your changes (`git checkout -b feature/my-new-feature`).
2. Add comprehensive tests for any new behavior or bug fixes.
3. Ensure all tests pass (`vendor/bin/phpunit`).
4. Submit a Pull Request with a clear description of the problem and your proposed solution.
