# Ginkelsoft Laravel Data Right to Be Forgotten

[![Tests](https://github.com/ginkelsoft-development/laravel-data-right-to-be-forgotten/actions/workflows/tests.yml/badge.svg?branch=development)](https://github.com/ginkelsoft-development/laravel-data-right-to-be-forgotten/actions/workflows/tests.yml)
[![License](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-10--13-brightgreen?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20--%208.5-blue?style=flat-square&logo=php)](https://php.net)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen?style=flat-square)](phpstan.neon.dist)

## Overview

Implements **GDPR art. 17 — the right to be forgotten** for a Laravel
application. When a subject ("forget everything about me") files a request,
this package sweeps every model you have registered as containing personal
data for that subject and either deletes or anonymizes the matching records.
Every action is recorded in a tamper-evident `forget_log` chain built on the
shared `HashChain` from `ginkelsoft/laravel-compliance-core`.

This is the **right to be forgotten** member of the GinkelSoft compliance
family — install the hub to get the whole family in one go.

## The family

| Package | GDPR Article(s) | Role |
| ------- | --------------- | ---- |
| [`laravel-compliance-core`](https://github.com/ginkelsoft-development/laravel-compliance-core) | art. 5(2) | Shared primitives (hash chain, strategies, subject hash) |
| [`laravel-data-retention`](https://github.com/ginkelsoft-development/laravel-data-retention) | art. 5(1)(e) | Storage limitation / time-based sweeps |
| **`laravel-data-right-to-be-forgotten`** | **art. 17** | **Subject-driven erasure — this package** |
| [`laravel-data-subject-access`](https://github.com/ginkelsoft-development/laravel-data-subject-access) | art. 15 + 20 | Read-only subject export |
| [`laravel-data-consent`](https://github.com/ginkelsoft-development/laravel-data-consent) | art. 6(1)(a) + 7 | Consent registry |
| [`laravel-data-breach-registry`](https://github.com/ginkelsoft-development/laravel-data-breach-registry) | art. 33 + 34 | Personal-data breach register |
| [`laravel-compliance-hub`](https://github.com/ginkelsoft-development/laravel-compliance-hub) | art. 5(2) | Umbrella: installs the whole family, verifies every chain |

## Why a separate package from retention?

Time-based retention answers "is this data old enough to remove?". GDPR art.
17 answers a different question: "this specific person has asked me to
remove **everything** about them, today." The two controls share building
blocks (delete vs. anonymize, per-field strategies, append-only audit log)
but the trigger and the audit-chain are different — retention writes to
`retention_log`, forget writes to `forget_log`. Keeping them in separate
packages keeps each chain internally consistent and independently auditable.

## How it works

### 1. Declare which models hold subject data

An attribute for simple cases, a property for anonymize. Models must
additionally implement the `Forgettable` contract — the trait provides the
default implementation, the interface gives the orchestrator the type
safety it needs.

```php
use Ginkelsoft\DataRightToBeForgotten\Attributes\Forgettable;
use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable as ForgettableTrait;
use Ginkelsoft\DataRightToBeForgotten\Contracts\Forgettable as ForgettableContract;

#[Forgettable(column: 'id', action: 'delete')]
class User extends Model implements ForgettableContract
{
    use ForgettableTrait;
}

#[Forgettable(column: 'user_id', action: 'delete')]
class Order extends Model implements ForgettableContract
{
    use ForgettableTrait;
}

class Profile extends Model implements ForgettableContract
{
    use ForgettableTrait;

    protected array $forgettable = [
        'column'    => 'user_id',
        'action'    => 'anonymize',
        'anonymize' => [
            'first_name' => 'placeholder',
            'last_name'  => 'placeholder',
            'email'      => 'hash',
        ],
    ];
}
```

For complex subject mappings (subject can appear in either of two columns,
polymorphic relation, etc) override the static `forSubjectQuery` method on
the model. See `tests/Models/ForgetTicket.php` for an OR-across-two-columns
example.

### 2. Register the models

```php
// config/forget.php
return [
    'models' => [
        \App\Models\User::class,
        \App\Models\Profile::class,
        \App\Models\Order::class,
    ],
    'include_soft_deleted' => true,
];
```

### 3. Run the sweep

```bash
php artisan retention:forget 01HXYZ --dry-run
php artisan retention:forget 01HXYZ
```

The command name keeps the `retention:` prefix for BC with the v1.x
monolithic package. The first argument is the subject identifier: whatever
string consistently identifies the person across your models. The
orchestrator iterates every registered model and applies its policy to
records linked to that subject. Idempotent: a second run finds no new
records and writes no new log entries.

### 4. Verify the audit chain

```php
use Ginkelsoft\ComplianceCore\Config\LogSecret;
use Ginkelsoft\ComplianceCore\Support\HashChain;
use Illuminate\Support\Facades\DB;

$entries = DB::table('forget_log')->orderBy('id')->get()
    ->map(fn ($row) => (array) $row)->all();

$intact = HashChain::verify($entries, LogSecret::value());
```

Or run `php artisan compliance:verify` from the
[hub](https://github.com/ginkelsoft-development/laravel-compliance-hub) to
verify every chain in the family in one shot.

## What the log holds (and what it does not)

The `forget_log` rows contain `subject_hash` (an irreversible SHA-256 of the
subject identifier plus `compliance.log_secret`), the source model class and
primary key, the action (`forgotten_deleted` or `forgotten_anonymized`),
timestamps, and the chain hashes. **No subject identifier, no field values
— just the proof that the person was forgotten.**

## Compliance notes

* **GDPR art. 17** — Right to erasure ("right to be forgotten"). This
  package gives you a documented, automated mechanism to honour an erasure
  request, and a tamper-evident record of every action taken.
* **GDPR art. 5(2)** — Accountability. The `forget_log` hash chain is the
  evidence.

This package is **not legal advice**. The decision to delete vs. anonymize
per field belongs to your DPO.

## Installation

```bash
composer require ginkelsoft/laravel-data-right-to-be-forgotten
php artisan vendor:publish --tag=compliance-config
php artisan vendor:publish --tag=forget-config
php artisan vendor:publish --tag=forget-migrations
php artisan migrate
```

Then add a secret to `.env` (shared with the rest of the family):

```env
COMPLIANCE_LOG_SECRET="$(openssl rand -base64 32)"
```

## Gotchas

- **Not transitively cascaded.** Only models in `forget.models` are touched.
  If `Order` is forgotten but `OrderLine` is not in the list, the order
  lines remain — give them their own Forgettable policy if they hold
  personal data.
- **Trait conflict with subject-access.** If a model carries both
  `Forgettable` and `Exportable` (from `laravel-data-subject-access`), PHP
  requires explicit conflict resolution because both traits define
  `forSubjectQuery`. The two defaults are functionally identical when both
  policies use the same subject column, so picking one with `insteadof`
  suffices:
  ```php
  use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable;
  use Ginkelsoft\DataSubjectAccess\Concerns\Exportable;
  class User extends Model implements ExportableContract, ForgettableContract
  {
      use Exportable, Forgettable {
          Forgettable::forSubjectQuery insteadof Exportable;
      }
  }
  ```
  If the two policies need DIFFERENT columns, override `forSubjectQuery` on
  the model itself instead of using `insteadof`.
- **Backups and warehouse copies are out of scope.** Document that
  separately in your DPIA.
- **Re-creation after forget is application logic.** If your app re-fills a
  profile for the same subject after a forget, that is your bug to fix; the
  package only records the forget that happened.

## Testing

```bash
composer install
vendor/bin/pest
vendor/bin/phpstan analyse --memory-limit=1G
vendor/bin/pint --test
```

## Reporting bugs

Found a bug or unexpected behaviour? We want to hear about it.

**Preferred — open a GitHub issue:**
[https://github.com/ginkelsoft-development/laravel-data-right-to-be-forgotten/issues/new](https://github.com/ginkelsoft-development/laravel-data-right-to-be-forgotten/issues/new)

When opening an issue, please include:

1. **Versions** — PHP, Laravel, and the package version
   (`composer show ginkelsoft/laravel-data-right-to-be-forgotten`).
2. **What you did** — the artisan command, code snippet, or steps that
   triggered the bug.
3. **What you expected** vs **what actually happened** — include full
   error output or a stack trace if there is one.
4. **A minimal reproduction** if you can — a failing test or a small
   code sample beats a long description.

**Security-sensitive findings** (anything that could expose personal
data, break a hash-chain, or bypass an audit log) — please **do not**
open a public issue. E-mail **info@ginkelsoft.com** directly with
"SECURITY" in the subject line and we will respond privately.

**Not on GitHub?** You can also e-mail **info@ginkelsoft.com** with the
same information.

## Contact

For commercial support, integration questions, or anything that doesn't
fit a GitHub issue: **info@ginkelsoft.com** —
[https://ginkelsoft.com](https://ginkelsoft.com).

## License

MIT License — see [LICENSE](LICENSE).
(c) 2026 Ginkelsoft
