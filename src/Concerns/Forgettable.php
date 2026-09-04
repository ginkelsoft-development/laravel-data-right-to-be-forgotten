<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Concerns;

use Ginkelsoft\ComplianceCore\Concerns\HasSubjectQuery;
use Ginkelsoft\ComplianceCore\Contracts\ResolvesSubjectColumn;
use Ginkelsoft\DataRightToBeForgotten\Actions\ForgetSubject;
use Ginkelsoft\DataRightToBeForgotten\Support\ForgettableConfig;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait Forgettable
 *
 * Marks an Eloquent model as participating in GDPR art. 17 ("right to
 * be forgotten") sweeps. The trait itself is intentionally lightweight:
 * all decisioning lives in {@see ForgettableConfig} and the action
 * classes.
 *
 * Models declare their policy via either the `#[Forgettable]` class
 * attribute or a protected `$forgettable` array property (the property
 * form supports per-field anonymize strategies).
 *
 * `forSubjectQuery` (the default `WHERE column = subject` query) comes
 * from {@see HasSubjectQuery} in `ginkelsoft/laravel-compliance-core`.
 * This trait only has to answer {@see ResolvesSubjectColumn}'s
 * `subjectColumn()` question. Because every subject-driven trait in the
 * compliance family (this one, and subject-access's `Exportable`)
 * shares that same base trait, a model combining both no longer needs
 * an `insteadof` to resolve a `forSubjectQuery` conflict — there is
 * only one implementation to inherit.
 *
 * Models with a subject mapping more complex than `column = subject`
 * (multi-column, polymorphic, joined, etc) keep overriding
 * `forSubjectQuery` directly on the model (see ForgetTicket in the test
 * suite for an OR-across-two-columns example) — a model method always
 * wins over a trait method, so the override still takes effect without
 * any extra wiring.
 *
 * @mixin Model
 */
trait Forgettable
{
    use HasSubjectQuery;

    /**
     * Resolve the forget policy for this model.
     *
     * @return ForgettableConfig|null Null when no policy is declared.
     */
    public function forgettablePolicy(): ?ForgettableConfig
    {
        return ForgettableConfig::for(static::class);
    }

    /**
     * The column {@see HasSubjectQuery} filters on for its default
     * `WHERE column = subject` query.
     *
     * Deliberate behaviour decision: models that use this trait but
     * never declare a policy (no `#[Forgettable]` attribute, no
     * `$forgettable` property) now make `forSubjectQuery()` throw
     * instead of silently building a `WHERE 1=0` query (the pre-refactor
     * behaviour). Every caller inside this package ({@see ForgetSubject})
     * already resolves and checks {@see ForgettableConfig} before ever
     * calling `forSubjectQuery()`, so this only matters for code calling
     * the method directly on a misconfigured model — and failing loudly
     * there is preferable to a query that quietly matches nothing and
     * looks like "subject has no data" instead of "policy is missing".
     *
     * @throws \LogicException When no forget policy is declared.
     */
    public static function subjectColumn(): string
    {
        $policy = ForgettableConfig::for(static::class);

        if ($policy === null) {
            throw new \LogicException(
                static::class.' uses the Forgettable trait but declares no forget policy '
                .'(no #[Forgettable] attribute and no $forgettable property), so forSubjectQuery() '
                .'cannot resolve which column to filter on.'
            );
        }

        return $policy->column;
    }
}
