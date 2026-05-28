<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Tests\Models;

use Ginkelsoft\DataRightToBeForgotten\Attributes\Forgettable;
use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable as ForgettableTrait;
use Ginkelsoft\DataRightToBeForgotten\Contracts\Forgettable as ForgettableContract;
use Ginkelsoft\DataRightToBeForgotten\Database\Factories\ForgetUserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Test model representing the subject themselves. Forgettable with a
 * `delete` action keyed on the model's primary key.
 *
 * In v1.x of the monolithic data-retention package, this fixture also
 * carried an `Exportable` policy to demonstrate the trait-conflict
 * resolution between `Forgettable::forSubjectQuery` and
 * `Exportable::forSubjectQuery`. That demonstration lives in the
 * `ginkelsoft/laravel-data-subject-access` package's test suite now
 * (where it is the relevant cross-package gotcha to verify).
 *
 * @method static ForgetUserFactory factory(...$arguments)
 */
#[Forgettable(column: 'id', action: 'delete')]
class ForgetUser extends Model implements ForgettableContract
{
    use ForgettableTrait;

    /** @use HasFactory<ForgetUserFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'forget_users';

    /** @var list<string> */
    protected $fillable = ['id', 'email'];

    /** @var bool */
    public $incrementing = false;

    /** @var string */
    protected $keyType = 'string';

    /**
     * @return Factory<ForgetUser>
     */
    protected static function newFactory(): Factory
    {
        return ForgetUserFactory::new();
    }
}
