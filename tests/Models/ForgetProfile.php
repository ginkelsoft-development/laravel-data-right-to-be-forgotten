<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Tests\Models;

use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable;
use Ginkelsoft\DataRightToBeForgotten\Contracts\Forgettable as ForgettableContract;
use Ginkelsoft\DataRightToBeForgotten\Database\Factories\ForgetProfileFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Forgettable test model with an `anonymize` policy over three fields,
 * keyed on `user_id`. `internal_note` is deliberately not anonymized
 * (and not exported in the subject-access package's parallel fixture)
 * to illustrate that policies are opt-in per field.
 *
 * @method static ForgetProfileFactory factory(...$arguments)
 */
class ForgetProfile extends Model implements ForgettableContract
{
    use Forgettable;

    /** @use HasFactory<ForgetProfileFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'forget_profiles';

    /** @var list<string> */
    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'internal_note'];

    /** @var array<string, mixed> */
    protected $forgettable = [
        'column' => 'user_id',
        'action' => 'anonymize',
        'anonymize' => [
            'first_name' => 'placeholder',
            'last_name' => 'placeholder',
            'email' => 'hash',
        ],
    ];

    /**
     * @return Factory<ForgetProfile>
     */
    protected static function newFactory(): Factory
    {
        return ForgetProfileFactory::new();
    }
}
