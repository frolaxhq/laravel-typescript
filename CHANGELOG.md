# Changelog

All notable changes to `laravel-typescript` will be documented in this file.

## v0.0.3 - 2026-04-14

### What's Changed

* Add model `$interfaces` object override support with `type`, `import`, and `nullable` keys.
* Apply forced overrides to both columns and accessors with full resolver precedence.
* Add import propagation through pipeline and render `import type` statements in generated output.
* Deduplicate imports so each `type + import` pair is emitted only once per generated file.
* Add unit/integration coverage for override extraction, resolver import metadata, and writer dedup behavior.
* Update README and docs with advanced override examples and configuration reference.

**Full Changelog**: https://github.com/frolaxhq/laravel-typescript/compare/v0.0.2...v0.0.3

## v0.0.2 - 2026-04-01

### What's Changed

* Bump ramsey/composer-install from 3 to 4 by @dependabot[bot] in https://github.com/frolaxhq/laravel-typescript/pull/5
* Update orchestra/testbench requirement from ^10.0.0||^9.0.0 to ^11.0.0 by @dependabot[bot] in https://github.com/frolaxhq/laravel-typescript/pull/6

**Full Changelog**: https://github.com/frolaxhq/laravel-typescript/compare/v0.0.1...v0.0.2

## v0.0.1 - 2026-04-01

### What's Changed

* Bump stefanzweifel/git-auto-commit-action from 5 to 7 by @dependabot[bot] in https://github.com/frolaxhq/laravel-typescript/pull/1
* Bump actions/checkout from 4 to 6 by @dependabot[bot] in https://github.com/frolaxhq/laravel-typescript/pull/2
* Fix/phpstan errors by @bishwajitcadhikary in https://github.com/frolaxhq/laravel-typescript/pull/3
* Fix/phpstan errors by @bishwajitcadhikary in https://github.com/frolaxhq/laravel-typescript/pull/4

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/frolaxhq/laravel-typescript/pull/1
* @bishwajitcadhikary made their first contribution in https://github.com/frolaxhq/laravel-typescript/pull/3

**Full Changelog**: https://github.com/frolaxhq/laravel-typescript/commits/v0.0.1
