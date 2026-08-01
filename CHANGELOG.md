# Changelog

All notable changes to `fb-message` will be documented in this file.

## 5.1.0 - 2026-08-01

### Added

- Tests for the `FbMessage` service, policy, observer, and reply/forward pages.
- `phpstan.neon.dist` (level 8) with justified ignores for dynamic Filament/global macros.
- `.github/CONTRIBUTING.md`, `.github/SECURITY.md`, `pint.json`, and `composer` `pint`/`analyse` scripts.

### Fixed

- `ViewMessage` fatal: `use` conflict between the `FbMessage` facade and model imports.
- `MessageEvent::$sender` is nullable; the observer can fire outside an authenticated request.
- Forward/Reply recipients: save reads the raw `to` state instead of a re-dehydrated no-op sync.
- Migration pivot keys now match the auth model (`uuid`/bigint) via the `HasUuids` pattern.
- Typo `forwatd()` → `forward()` in the policy; removed dead tenant branch, unused assets, and stale scaffolding.

### Changed

- `FbMessage` service methods and table/infolist closures type their records as `FbMessage` instead of the base `Model`.
- `HasCreateNotificationMessage` unions `cc` and `bcc` correctly.

## 5.0.0 - 2026-07-09

- Upgrade to Filament v5.
