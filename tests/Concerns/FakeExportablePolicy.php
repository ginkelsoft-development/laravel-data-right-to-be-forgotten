<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Tests\Concerns;

use Ginkelsoft\ComplianceCore\Concerns\HasSubjectQuery;

/**
 * Stand-in for `Ginkelsoft\DataSubjectAccess\Concerns\Exportable`. That
 * trait lives in the sibling `ginkelsoft/laravel-data-subject-access`
 * package and cannot be depended on from here, but after its own
 * follow-up lands it will compose `HasSubjectQuery` exactly the way
 * `Forgettable` now does.
 *
 * Composing `HasSubjectQuery` here too — instead of hand-writing a
 * second `WHERE column = subject` implementation — proves the "no
 * `insteadof` needed" claim against real PHP trait resolution: both
 * `Forgettable` and this trait pull `forSubjectQuery` from the exact
 * same source, so combining them on one model is not a conflict.
 */
trait FakeExportablePolicy
{
    use HasSubjectQuery;

    public function exportablePolicyName(): string
    {
        return 'export';
    }
}
