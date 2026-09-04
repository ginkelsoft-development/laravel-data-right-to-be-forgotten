<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Tests\Models;

use Ginkelsoft\ComplianceCore\Contracts\ResolvesSubjectColumn;
use Ginkelsoft\DataRightToBeForgotten\Attributes\Forgettable;
use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable as ForgettableTrait;
use Ginkelsoft\DataRightToBeForgotten\Contracts\Forgettable as ForgettableContract;
use Ginkelsoft\DataRightToBeForgotten\Tests\Concerns\FakeExportablePolicy;
use Illuminate\Database\Eloquent\Model;

/**
 * Multi-policy fixture: combines `Forgettable` with a stand-in for
 * subject-access's `Exportable` ({@see FakeExportablePolicy}) on one
 * model, without an `insteadof`.
 *
 * Before both traits composed `HasSubjectQuery` from
 * `ginkelsoft/laravel-compliance-core`, this combination required:
 *
 *     use Forgettable, Exportable {
 *         Forgettable::forSubjectQuery insteadof Exportable;
 *     }
 *
 * because PHP saw two independent `forSubjectQuery` implementations
 * declared on this class and refused to pick one on its own — even
 * though both bodies were byte-identical. Now that both traits pull
 * `forSubjectQuery` from the same `HasSubjectQuery` source, PHP does
 * not see a conflict at all, so the plain `use` below is enough.
 */
#[Forgettable(column: 'user_id', action: 'delete')]
class ForgetAndExportRecord extends Model implements ForgettableContract, ResolvesSubjectColumn
{
    use FakeExportablePolicy, ForgettableTrait;

    /** @var string */
    protected $table = 'forget_and_export_records';

    /** @var list<string> */
    protected $fillable = ['user_id', 'note'];
}
