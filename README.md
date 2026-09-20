# Laravel Jev

[![Run Tests](https://github.com/i-priyanshuverma/laravel-jev/actions/workflows/run-tests.yml/badge.svg)](https://github.com/i-priyanshuverma/laravel-jev/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/i-priyanshuverma/laravel-jev.svg?style=flat-square)](https://packagist.org/packages/i-priyanshuverma/laravel-jev)
[![Total Downloads](https://img.shields.io/packagist/dt/i-priyanshuverma/laravel-jev.svg?style=flat-square)](https://packagist.org/packages/i-priyanshuverma/laravel-jev)
[![PHP Version](https://img.shields.io/badge/php-%5E8.2-777bb4.svg?style=flat-square)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

Laravel Jev provides an expressive, fluent API for fast semantic classification and decision-making in Laravel applications.

Whether you need to triage incoming webhooks, filter user submissions, route customer inquiries, or enforce custom validation rules, Jev gives you simple single-line methods (`is`, `choose`, `score`) without boilerplate.

- **Native Architecture:** Built directly on Laravel's native HTTP client with zero third-party package dependencies.
- **Fast Execution:** Fast response times (typically 70–500ms) with calibrated confidence scores.
- **Testing Ready:** Comprehensive offline testing fake (`Jev::fake()`) with assertion helpers.
- **Single Roundtrip Batching:** Chain multiple questions against a single payload in one network request.
- **Form Request Rules:** Custom validation rules for your form requests.
- **Response Caching:** Optional Redis or cache-store memoization for high-throughput routes.

---

## Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, or 12.x

---

## Installation

Install the package via Composer:

```bash
composer require i-priyanshuverma/laravel-jev
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="jev-config"
```

Add your TypeSafe API key to your `.env` file:

```env
JEV_API_KEY=your_api_key_here
```

---

## Usage

### Boolean Checks (`Jev::is` / `Jev::isNot`)

Determine if a string matches a specific criteria:

```php
use Priyanshu\LaravelJev\Facades\Jev;

// Check if content matches criteria (default confidence threshold: 0.80)
if (Jev::is($comment->body, 'promotional spam or link dumping')) {
    $comment->markAsSpam();
}

// Inverted check with custom threshold
if (Jev::isNot($webhookPayload, 'an actionable billing event', threshold: 0.85)) {
    return response()->noContent();
}
```

### Categorical Selection (`Jev::choose`)

Select the single best matching category from a list of options:

```php
use Priyanshu\LaravelJev\Facades\Jev;

$category = Jev::choose($inquiryText, [
    'billing',
    'technical_support',
    'sales',
    'general',
], default: 'general');
```

### Numerical Scoring (`Jev::score`)

Score an input across ordered levels:

```php
use Priyanshu\LaravelJev\Facades\Jev;

$urgency = Jev::score($ticketText, 'urgency', levels: ['low', 'medium', 'high', 'critical']);

if ($urgency >= 0.75) {
    $ticket->escalate();
}
```

### Multi-Question Batching (`Jev::analyze`)

Run multiple evaluations against the same payload in a single HTTP roundtrip:

```php
use Priyanshu\LaravelJev\Facades\Jev;

$analysis = Jev::analyze($ticket->body)
    ->is('urgent', 'Is this request urgent?')
    ->choose('department', ['billing', 'hardware', 'software'])
    ->score('sentiment', ['negative', 'neutral', 'positive'])
    ->run();

$isUrgent    = $analysis->is('urgent');
$department  = $analysis->choice('department');
$sentiment   = $analysis->score('sentiment');
```

### Global Helper Function

You can also use the `jev()` helper function:

```php
// Boolean check
if (jev()->is($text, 'spam')) {
    // ...
}

// Fluent batching shorthand
$results = jev($input)
    ->is('is_urgent')
    ->choose('category', ['bug', 'feature_request', 'billing'])
    ->run();
```

---

## Validation Rules

Laravel Jev includes custom validation rules for your form requests.

### Using `JevNot`

Reject submissions that match unwanted criteria:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Priyanshu\LaravelJev\Rules\JevNot;

class ContactRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string'],
            'email'   => ['required', 'email'],
            'message' => ['required', 'string', new JevNot('spam or advertisement')],
        ];
    }
}
```

### Using Fluent `JevRule`

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Priyanshu\LaravelJev\Rules\JevRule;

class FeedbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'feedback' => [
                'required',
                'string',
                JevRule::not('profanity or harassment')->message('Please keep feedback respectful.'),
                JevRule::is('constructive product feedback'),
            ],
        ];
    }
}
```

---

## Testing

Use `Jev::fake()` to mock responses without making network requests:

```php
namespace Tests\Feature;

use Priyanshu\LaravelJev\Facades\Jev;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    public function test_tickets_are_routed_to_billing(): void
    {
        Jev::fake([
            'choose' => 'billing',
        ]);

        $response = $this->postJson('/tickets', [
            'message' => 'Where is my latest invoice?',
        ]);

        $response->assertCreated();
        Jev::assertChosen('billing');
    }

    public function test_spam_submissions_are_rejected(): void
    {
        Jev::fake([
            'is:spam' => true,
        ]);

        $response = $this->post('/comments', [
            'body' => 'Buy coins now!',
        ]);

        $response->assertSessionHasErrors('body');
        Jev::assertChecked('spam');
    }
}
```

### Available Assertions

- `Jev::assertChecked(string $criteria, ?callable $callback = null)`
- `Jev::assertNotChecked(string $criteria)`
- `Jev::assertChosen(string $option)`
- `Jev::assertNothingClassified()`

---

## Configuration

The published `config/jev.php` configuration file:

```php
return [
    // API Authentication Key
    'api_key' => env('JEV_API_KEY'),

    // Base Endpoint URL
    'base_url' => env('JEV_BASE_URL', 'https://api.typesafe.ai/v1'),

    // Default confidence threshold (0.00 to 1.00)
    'threshold' => (float) env('JEV_DEFAULT_THRESHOLD', 0.80),

    // Request timeout and transient retry count
    'timeout' => (int) env('JEV_TIMEOUT', 5),
    'retries' => (int) env('JEV_RETRIES', 2),

    // Optional response caching
    'cache' => [
        'enabled' => (bool) env('JEV_CACHE_ENABLED', false),
        'ttl'     => (int) env('JEV_CACHE_TTL', 3600),
        'store'   => env('JEV_CACHE_STORE'),
    ],
];
```

---

## Response Caching

To cache identical evaluations (useful for high-volume endpoints with repeated payloads), enable caching in your `.env`:

```env
JEV_CACHE_ENABLED=true
JEV_CACHE_TTL=86400
```

When enabled, matching requests are retrieved directly from your configured cache store without outbound API calls.

---

## Running Tests

Run the package test suite with PHPUnit:

```bash
composer test
```

---

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
