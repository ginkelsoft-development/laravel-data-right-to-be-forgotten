<?php

declare(strict_types=1);

use Ginkelsoft\DataRightToBeForgotten\Tests\Models\ForgetAndExportRecord;
use Ginkelsoft\DataRightToBeForgotten\Tests\Models\ForgetOrder;
use Ginkelsoft\DataRightToBeForgotten\Tests\Models\UnpolicyedModel;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('forget_orders', function ($table): void {
        $table->id();
        $table->string('user_id', 64)->index();
        $table->string('reference');
        $table->integer('amount_cents');
        $table->timestamps();
    });

    Schema::create('unpolicyed_models', function ($table): void {
        $table->id();
        $table->string('user_id', 64)->nullable();
        $table->timestamps();
    });

    Schema::create('forget_and_export_records', function ($table): void {
        $table->id();
        $table->string('user_id', 64)->index();
        $table->string('note')->nullable();
        $table->timestamps();
    });
});

it('builds the default WHERE column = subject query via HasSubjectQuery', function (): void {
    ForgetOrder::create(['user_id' => 'user-1', 'reference' => 'ORD-1', 'amount_cents' => 100]);
    ForgetOrder::create(['user_id' => 'user-2', 'reference' => 'ORD-2', 'amount_cents' => 200]);

    $rows = ForgetOrder::forSubjectQuery('user-1')->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->reference)->toBe('ORD-1');
});

it('throws instead of silently matching nothing when no forget policy is declared', function (): void {
    UnpolicyedModel::create(['user_id' => 'user-1']);

    expect(fn () => UnpolicyedModel::forSubjectQuery('user-1'))
        ->toThrow(LogicException::class, 'declares no forget policy');
});

it('combines Forgettable with another subject-driven trait without an insteadof', function (): void {
    ForgetAndExportRecord::create(['user_id' => 'user-1', 'note' => 'mine']);
    ForgetAndExportRecord::create(['user_id' => 'user-2', 'note' => 'not mine']);

    $model = new ForgetAndExportRecord;

    expect($model->exportablePolicyName())->toBe('export')
        ->and($model->forgettablePolicy()->column)->toBe('user_id');

    $rows = ForgetAndExportRecord::forSubjectQuery('user-1')->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->note)->toBe('mine');
});
