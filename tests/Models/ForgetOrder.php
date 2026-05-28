<?php

declare(strict_types=1);

namespace Ginkelsoft\DataRightToBeForgotten\Tests\Models;

use Ginkelsoft\DataRightToBeForgotten\Attributes\Forgettable;
use Ginkelsoft\DataRightToBeForgotten\Concerns\Forgettable as ForgettableTrait;
use Ginkelsoft\DataRightToBeForgotten\Contracts\Forgettable as ForgettableContract;
use Ginkelsoft\DataRightToBeForgotten\Database\Factories\ForgetOrderFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Test model with the attribute-form policy: delete records linked
 * to the user_id when the subject is forgotten.
 *
 * @method static ForgetOrderFactory factory(...$arguments)
 */
#[Forgettable(column: 'user_id', action: 'delete')]
class ForgetOrder extends Model implements ForgettableContract
{
    use ForgettableTrait;

    /** @use HasFactory<ForgetOrderFactory> */
    use HasFactory;

    /** @var string */
    protected $table = 'forget_orders';

    /** @var list<string> */
    protected $fillable = ['user_id', 'reference', 'amount_cents'];

    /**
     * @return Factory<ForgetOrder>
     */
    protected static function newFactory(): Factory
    {
        return ForgetOrderFactory::new();
    }
}
