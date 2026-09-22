# Darkwood AI

Darkwood AI is a complementary extension ecosystem for [Symfony AI](https://github.com/symfony/ai).

It does **not** replace, fork, or reimplement Symfony AI. Packages in this
repository depend on Symfony AI components and add integrations that Symfony AI
does not currently provide, or that Darkwood maintains independently.

This monorepo reuses Symfony AI's development workflow: a tooling-only root
`composer.json`, workspace package discovery, PHP-CS-Fixer, PHPStan, Deptrac
and per-package PHPUnit.

```text
darkwood/ai
    |
    +-- darkwood/ai-jev-platform
            |
            +-- symfony/ai-platform
```

Use Symfony AI for platforms, agents, stores and the AI Bundle. Use Darkwood AI
when you need an extra bridge on top of that infrastructure.

The first package will be `darkwood/ai-jev-platform`, a TypeSafe Jev platform
bridge for `symfony/ai-platform`.

## Requirements

* PHP 8.2 or later
* [Composer](https://getcomposer.org/)
* The relevant [Symfony AI](https://github.com/symfony/ai) packages

## Development

```bash
composer update
vendor/bin/php-cs-fixer fix
```

Package tests and static analysis run after a workspace install, the same way
as in Symfony AI:

```bash
php .github/build-packages.php
php .github/build-workspace.php
composer update --no-scripts
```

## License

This project is licensed under the [MIT License](LICENSE).

Monorepo tooling is adapted from Symfony AI. See [NOTICE](NOTICE).

## Authors

Mathieu Ledru <matyo91@gmail.com>
