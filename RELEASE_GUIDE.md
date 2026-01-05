# Release Guide

This guide covers all the steps required to release a new version for all packages. The order of packages must be followed but packages that do not have a new version to be released can be skipped (keeping the rest in order).

## 1. collaborar/wpemerge

1. Update and commit WPEMERGE_VERSION in `config.php` with the latest version.
2. Create a new release: https://github.com/collaborar/wpemerge/releases/new

## 2. collaborar/wpemerge-cli

1. Update and commit `composer.json` with the latest version of this package (otherwise packagist.org will not update).
2. Create a new release: https://github.com/collaborar/wpemerge-cli/releases/new

## 3. collaborar/wpemerge-blade

1. Update and commit `composer.json` with the latest `collaborar/wpemerge` version requirement.
2. Create a new release: https://github.com/collaborar/wpemerge-blade/releases/new

## 4. collaborar/wpemerge-twig

1. Update and commit `composer.json` with the latest `collaborar/wpemerge` version requirement.
2. Create a new release: https://github.com/collaborar/wpemerge-twig/releases/new

## 5. collaborar/wpemerge-app-core

1. Update and commit `composer.json` with the latest `collaborar/wpemerge` version requirement.
2. Create a new release: https://github.com/collaborar/wpemerge-app-core/releases/new

## 6. collaborar/wpemerge-theme

1. Run `yarn i18n`.
2. Update `composer.json` with the latest version requirements for:
    - `collaborar/wpemerge`
    - `collaborar/wpemerge-app-core`
    - `collaborar/wpemerge-cli`
3. Update `composer.json` with the latest version of this package (otherwise packagist.org will not update).
4. Update call to `my_app_should_load_wpemerge()` with the latest minimum version required.
5. Commit.
6. Create a new release: https://github.com/collaborar/wpemerge-theme/releases/new
7. Update the composer example in the Quickstart docs for Bedrock with the exact new version number.

## 7. collaborar/wpemerge-plugin

1. Run `yarn i18n`.
2. Update `composer.json` with the latest version requirements for:
    - `collaborar/wpemerge`
    - `collaborar/wpemerge-app-core`
    - `collaborar/wpemerge-cli`
3. Update `composer.json` with the latest version of this package (otherwise packagist.org will not update).
4. Update call to `my_app_should_load_wpemerge()` with the latest minimum version required.
5. Commit.
6. Create a new release: https://github.com/collaborar/wpemerge-plugin/releases/new
