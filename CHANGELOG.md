# Changelog

All notable changes to `ginkelsoft/laravel-data-right-to-be-forgotten` are
documented in this file. This project follows
[Semantic Versioning](https://semver.org/).

## [Unreleased] — 2026-09-04

### Changed

- **README** — removed the "trait conflict with subject-access" Gotcha and
  replaced it with a worked example that combines `Forgettable` with
  `laravel-data-subject-access`'s `Exportable` on one model without an
  `insteadof` block. Both traits build `forSubjectQuery` on the shared
  `HasSubjectQuery` trait from `ginkelsoft/laravel-compliance-core`
  (added there in `ginkelsoft/laravel-compliance-core` 1.1, see its
  CHANGELOG), so PHP no longer sees a method collision between them once
  `Forgettable` composes it too — that trait update is tracked as
  issue #1 in this repo and lands in a separate PR; this documentation
  change reflects the intended, tested API of that upcoming trait.

  Note: combining both traits still requires the model to declare a
  single `subjectColumn()` when the two policies use the same column —
  each trait's own default falls back to its own policy's column, and
  two traits declaring the *same* method name with *different* bodies
  is a genuine PHP collision (proven with a standalone reproduction), so
  `insteadof` is traded for one explicit `subjectColumn()` method on the
  model, not eliminated outright. Documented as such in the README.

## [1.0.0] — 2026-05-28

Initial release.
