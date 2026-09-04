<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Tests\Models;

use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable as ForgettableTrait;
use Ginkelsoft\DataRightToBeForgotten\Contracts\Forgettable as ForgettableContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Uses the `Forgettable` trait but deliberately declares no policy at
 * all — no `#[Forgettable]` attribute, no `$forgettable` property.
 *
 * Exercises two things:
 *  - `ForgettableConfig::for()` returning null for a model that is a
 *    real, existing class (as opposed to a class name that simply does
 *    not exist).
 *  - The explicit decision in {@see ForgettableTrait::subjectColumn()}:
 *    calling `forSubjectQuery()` directly on a model like this now
 *    throws instead of silently building a `WHERE 1=0` query.
 */
class UnpolicyedModel extends Model implements ForgettableContract
{
    use ForgettableTrait;

    /** @var string */
    protected $table = 'unpolicyed_models';

    /** @var list<string> */
    protected $fillable = ['user_id'];
}
