# Changelog

All notable changes to `filament-single-record-resource` will be documented in this file.

## v1.1.0 - 2026-04-14

### Added

- add the explicit `SingleRecordResolvableResource` contract for static-analysis-friendly Resource typing

### Changed

- HasSingleRecord now prefers the explicit Resource contract while remaining compatible with legacy Resources that expose the same methods manually
- workbench examples, tests, comments, README and AGENTS were updated to reflect the new contract

### Validation

- full Pest suite passed
- PHPStan passed
- Pint passed

## v1.0.4 - 2026-04-14

### Fixes

- allow root single-record resources to be accessed with `view` on the resolved record even when `viewAny` is denied
- centralize default single-record resolution hooks on the Resource for authorization fallback
- document the authorization behavior in README and AGENTS

### Validation

- full Pest suite passed
- PHPStan passed
- Pint passed

## v1.0.3 - 2026-04-08

**Full Changelog**: https://github.com/CoringaWc/filament-single-record-resource/compare/v1.0.2...v1.0.3

## v1.0.2 - 2026-04-07

**Full Changelog**: https://github.com/CoringaWc/filament-single-record-resource/compare/v1.0.1...v1.0.2

## v1.0.1 - 2026-04-07

**Full Changelog**: https://github.com/CoringaWc/filament-single-record-resource/compare/v1.0.0...v1.0.1

## v1.0.0 - 2026-03-06

**Full Changelog**: https://github.com/CoringaWc/filament-single-record-resource/commits/v1.0.0

## 1.0.0 - 2026-03-06

- initial release
- relaxed Composer constraints to support Filament 5 and Laravel 12 in consumer projects
