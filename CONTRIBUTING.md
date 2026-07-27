# Contributing

Thanks for wanting to help out.

## Getting started

```bash
composer install
make test
```

`make help` lists everything available. The full build — coding standard, static analysis and
tests — is:

```bash
make build
```

## Before opening a pull request

- `make csfix` — applies the coding standard (CI runs `make cs`, which only checks).
- `make psalm` — static analysis; it must stay clean.
- `make test` — the test suite.

## Testing against other PHPUnit versions

The package supports PHPUnit 9.6 through 12, and CI runs all of them. To reproduce a specific
combination locally:

```bash
composer update --with "phpunit/phpunit:^9.6"
make test
```

Anything touching `ArchRuleCheckerConstraintAdapter` deserves a run against the oldest and the
newest supported PHPUnit, since the `Constraint` base class changed signatures between majors.
That is also why `matches()` and `failureDescription()` take an untyped `$other`: it is the only
declaration compatible with both PHPUnit 9 and PHPUnit 10+.

## Scope

This repository holds only the PHPUnit integration. Rules, expressions and the analyzer live in
[phparkitect/arkitect](https://github.com/phparkitect/arkitect) — new rules belong there.
