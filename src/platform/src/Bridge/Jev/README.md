# Jev Platform

TypeSafe System One (Jev) platform bridge for [Symfony AI](https://github.com/symfony/ai).

Jev evaluates typed questions against a state and returns structured answers
with probabilities. It is not a chat-completion model.

This package is part of [Darkwood AI](https://github.com/darkwood-com/ai). It
depends on `symfony/ai-platform` and does not reimplement Platform contracts.

## Installation

```bash
composer require darkwood/ai-jev-platform
```

Until the package is published on Packagist, require it from this repository
with a Composer path or VCS repository.

## Authentication

Create an API key in the [TypeSafe console](https://console.typesafe.ai/) and
expose it as `TYPESAFE_API_KEY`. Requests use `Authorization: Bearer`.

The default endpoint is `https://api.typesafe.ai/v1/systemone`.

## Basic invocation

```php
use Darkwood\AI\Platform\Bridge\Jev\Factory;
use Darkwood\AI\Platform\Bridge\Jev\Output\EvaluationResult;

$platform = Factory::createPlatform($_ENV['TYPESAFE_API_KEY']);

$result = $platform->invoke('jev-latest', 'Customer: I was charged twice and I am furious.', [
    'questions' => [
        'topic' => [
            'type' => 'choice',
            'instructions' => 'What is the issue about?',
            'criteria' => [
                'billing' => 'money problems',
                'bug' => 'broken product',
            ],
        ],
        'urgent' => [
            'type' => 'noul',
            'instructions' => 'Escalate to a human now?',
        ],
    ],
])->asObject();

assert($result instanceof EvaluationResult);
$result->getChoice('topic');
$result->getNoul('urgent');
```

The `questions` option is required. `state` (the invoke input) may be a string
or a JSON object/array.

## Typed questions

| Type | Meaning | Accessor |
| --- | --- | --- |
| `noul` | Yes/no probability from 0 to 1 | `getNoul($id)` |
| `choice` | One option from `criteria` | `getChoice($id)` |
| `score` | Weighted score across ordered rubric levels | `getScore($id)` |

See the [TypeSafe API reference](https://docs.typesafe.ai/api) for the full
question schema.

## Probabilities and confidence

```php
$result->getProbabilities('topic'); // ['billing' => 0.88, 'bug' => 0.12]
$result->getConfidence('topic');    // 0.81 or null
$result->getAnswer('topic');        // ChoiceAnswer|NoulAnswer|ScoreAnswer
$result->getModel();                // e.g. jev-1.13.0
```

When the API reports token usage, Symfony AI attaches a `TokenUsage` object
to the result metadata (`input_tokens` / `output_tokens`).

## Model identifiers

| Identifier | Notes |
| --- | --- |
| `jev-latest` | Current production model |
| `jev-preview` | Preview channel |
| `jev-1.13.0` | Pinned version |

Capabilities are `INPUT_TEXT` only. Jev does not generate chat completions.

## Error handling

| HTTP status | Exception |
| --- | --- |
| 401 / 403 | `Symfony\AI\Platform\Exception\AuthenticationException` |
| 422 | `Symfony\AI\Platform\Exception\BadRequestException` |
| 404 | `Symfony\AI\Platform\Exception\ModelNotFoundException` |
| Other 4xx/5xx | `Symfony\AI\Platform\Exception\RuntimeException` |
| Missing `questions` | `Symfony\AI\Platform\Exception\InvalidArgumentException` |
| Malformed payload | `Symfony\AI\Platform\Exception\RuntimeException` |

## Symfony service configuration

The first-party `framework.ai.platform.jev` key is wired inside the Symfony AI
Bundle. A third-party package cannot register that key without changing
Symfony AI core.

Register the platform as a regular service instead:

```yaml
# config/services.yaml
services:
    Darkwood\AI\Platform\Bridge\Jev\ModelCatalog: ~

    ai.platform.jev:
        class: Symfony\AI\Platform\Platform
        factory: ['Darkwood\AI\Platform\Bridge\Jev\Factory', 'createPlatform']
        arguments:
            $apiKey: '%env(TYPESAFE_API_KEY)%'
            $httpClient: '@?http_client'
            $modelCatalog: '@Darkwood\AI\Platform\Bridge\Jev\ModelCatalog'
            $eventDispatcher: '@event_dispatcher'
        lazy: true
        tags: ['ai.platform']
```

Inject `ai.platform.jev` or `Symfony\AI\Platform\PlatformInterface`.

Do not copy or fork `symfony/ai-bundle` only to add a `jev:` config node.

## Current limitations

* Jev is an evaluator, not a chat or tool-calling model.
* `questions` must be a non-empty map on every invoke.
* Automatic `framework.ai.platform.jev` bundle configuration is not available
  until Symfony AI adds a third-party platform extension point.
* This package is not published on Packagist yet.

## Example

See [`examples/jev/evaluate.php`](../../../../../examples/jev/evaluate.php):

```bash
cd examples
composer update
php jev/evaluate.php --mock
TYPESAFE_API_KEY=... php jev/evaluate.php
```

## TypeSafe documentation

* [API reference](https://docs.typesafe.ai/api)
* [System One](https://docs.typesafe.ai/concepts/system-one)
* [Models](https://docs.typesafe.ai/models)

## License

MIT. Adapted Jev client files retain their Symfony copyright notices.
