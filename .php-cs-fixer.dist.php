<?php

/*
 * This file is part of Darkwood AI.
 *
 * (c) Mathieu Ledru <matyo91@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

$fileHeaderParts = [
    <<<'EOF'
        This file is part of Darkwood AI.

        (c) Mathieu Ledru <matyo91@gmail.com>

        EOF,
    <<<'EOF'

        For the full copyright and license information, please view the LICENSE
        file that was distributed with this source code.
        EOF,
];

return (new PhpCsFixer\Config())
    // @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/pull/7777
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'declare_strict_types' => false,
        'header_comment' => [
            'header' => implode('', $fileHeaderParts),
        ],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
    ])
    ->setRuleCustomisationPolicy(new class implements PhpCsFixer\Config\RuleCustomisationPolicyInterface {
        public function getPolicyVersionForCache(): string
        {
            return hash_file('xxh128', __FILE__);
        }

        public function getRuleCustomisers(): array
        {
            return [
                'void_return' => static function (SplFileInfo $file) {
                    // temporary hack due to bug: https://github.com/symfony/symfony/issues/62734
                    $path = strtolower(str_replace('\\', '/', $file->getRealPath() ?: $file->getPathname()));

                    if (str_contains($path, '/tests/') || str_contains($path, '/test/')) {
                        return false;
                    }

                    return true;
                },
                'header_comment' => static function (SplFileInfo $file) {
                    $contents = @file_get_contents($file->getRealPath() ?: $file->getPathname());

                    // Adapted Symfony AI files keep their original copyright header.
                    return !str_contains((string) $contents, 'This file is part of the Symfony package.');
                },
            ];
        }
    })
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__)
            ->exclude(['var', 'vendor'])
    )
;
