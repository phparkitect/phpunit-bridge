# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- `ArchRuleCheckerConstraintAdapter`, extracted from `phparkitect/phparkitect`
  ([arkitect#661](https://github.com/phparkitect/arkitect/issues/661)). The class name and the
  `Arkitect\PHPUnit` namespace are unchanged, so existing usages keep working.
- An optional target PHP version argument on the constraint constructor, so the analyzer can parse
  code for a PHP version other than the one running the tests.
- `ArchRuleAsserts`, a trait providing `assertArchRule()`. It is the single entry point of the
  package. The helper used to live in the core repository's end-to-end tests, where nobody could
  install it. It is a trait rather than a base test case so that it also works when the parent
  class is already taken by a framework.
- Support for PHPUnit 12, alongside 9.6, 10 and 11.

### Changed

- PHPUnit is now a real dependency rather than a dev-only one. The core package could only declare
  it as a dev dependency, which left `PHPUnit\Framework\Constraint\Constraint` potentially missing
  at runtime and needed a Psalm suppression to paper over it.
