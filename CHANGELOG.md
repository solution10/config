# Changelog

## 3.x

### 3.0.0

- **Config Class**
- [BC Break] Updated to PHP 7.0 as base version
- [BC Break] Added scalar type hints
- [BC Break] Accessors follow more standard `setXXX` and `getXXX` pattern.
- [BC Break] Refactored `__construct()` to not require an environment
- [BC Break] Config paths accessors changed:
    - `addBasePath` becomes `addConfigPath`
    - `basePaths` becomes `getConfigPaths`
- [BC Break] Split `addConfigPath` into single (`addConfigPath`) and multi (`addConfigPaths`) setters.
- Now uses `array_replace_recursive` rather than in-class replace.
- Config paths are now optional in `__construct()`


## 2.x

**PHP support**: 5.4 - 5.6.

### 2.1.0

- **Last release supporting PHP 5.4 - 5.6**
- Added support for multiple config base paths. 

### 2.0.0

- Added `requiredFiles()` API call
- Removed PHP 5.3 support
- Added PHP 7 into build matrix
- Removed HHVM from allowed fails

## 1.x

**PHP support**: 5.0 - 5.3

## 1.2.0

- **Last release supporting PHP 5.3**
- Passing 'null' to construct now assumes production

## 1.1.0

- Migrating to PSR4

## 1.0.0

- Initial version
