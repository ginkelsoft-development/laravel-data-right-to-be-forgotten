<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract every Eloquent model registered under
 * `forget.models` must implement.
 *
 * The {@see \Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable} trait
 * provides a default implementation that satisfies this contract via
 * `WHERE {policy.column} = :subject`. Models with more complex subject
 * mappings can override the method while still implementing this
 * contract, so the orchestrator can keep its dispatch fully typed.
 */
interface Forgettable
{
    /**
     * Build the query that selects every record of this model belonging
     * to the given subject identifier.
     *
     * @return Builder<Model>
     */
    public static function forSubjectQuery(string $subject): Builder;
}
