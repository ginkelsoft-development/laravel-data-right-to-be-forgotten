<?php

declare(strict_types=1);

/**
 * -----------------------------------------------------------------------------
 * Ginkelsoft Laravel Data Right To Be Forgotten - Configuration
 * -----------------------------------------------------------------------------
 *
 * GDPR art. 17 ("right to be forgotten") configuration. List the
 * Eloquent models that contain personal data about subjects, and the
 * `retention:forget {subject}` command will sweep every one of them.
 *
 * The shared signing secret used to hash the audit log lives in the
 * `compliance` config provided by `ginkelsoft/laravel-compliance-core`.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Forgettable Models Registry
    |--------------------------------------------------------------------------
    |
    | List the Eloquent model classes that contain personal data and
    | participate in subject-driven erasure. The `retention:forget`
    | command iterates this list and applies each model's Forgettable
    | policy (delete or anonymize) to records belonging to the subject.
    |
    | Models in this list must use the Forgettable trait, implement
    | the Forgettable contract, and declare a policy via either the
    | #[Forgettable] attribute or a $forgettable array property.
    |
    */
    'models' => [],

    /*
    |--------------------------------------------------------------------------
    | Include Soft-Deleted Records
    |--------------------------------------------------------------------------
    |
    | When a model uses SoftDeletes, this flag controls whether already
    | soft-deleted records are also considered by the forget sweep.
    | Typically true: soft-deleted rows still hold personal data and
    | the subject's right to be forgotten applies to them too.
    |
    */
    'include_soft_deleted' => true,
];
