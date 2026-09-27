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
            |
            +-- TypeSafe Jev API
```

Use Symfony AI for platforms, agents, stores and the AI Bundle. Use Darkwood AI
when you need an extra bridge on top of that infrastructure.

## Requirements

* PHP 8.2 or later
* [Composer](https://getcomposer.org/)
* [symfony/ai-platform](https://github.com/symfony/ai) `^0.13`

## Packages

### `darkwood/ai-jev-platform`

TypeSafe Jev (System One) platform bridge. Jev evaluates typed questions
against a state and returns structured answers with probabilities.

```bash
composer require darkwood/ai-jev-platform
```

```php
use Darkwood\AI\Platform\Bridge\Jev\Factory;
use Darkwood\AI\Platform\Bridge\Jev\Output\EvaluationResult;

$platform = Factory::createPlatform($_ENV['TYPESAFE_API_KEY']);

$result = $platform->invoke('jev-latest', 'I was charged twice.', [
    'questions' => [
        'urgent' => [
            'type' => 'noul',
            'instructions' => 'Escalate to a human now?',
        ],
    ],
])->asObject();

assert($result instanceof EvaluationResult);
$result->getNoul('urgent');
```

See [src/platform/src/Bridge/Jev/README.md](src/platform/src/Bridge/Jev/README.md)
for authentication, typed questions, Symfony service configuration and
limitations.

```bash
cd examples
composer update
php jev/evaluate.php --mock
```

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
find src -name phpunit.xml.dist -not -path '*/vendor/*' | .github/run-in-packages.sh vendor/bin/phpunit -c '{}'
vendor/bin/deptrac
```

## License

This project is licensed under the [MIT License](LICENSE).

Monorepo tooling is adapted from Symfony AI. See [NOTICE](NOTICE).

## Authors

Mathieu Ledru <matyo91@gmail.com>
