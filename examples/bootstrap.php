<?php

/*
 * This file is part of Darkwood AI.
 *
 * (c) Mathieu Ledru <matyo91@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

require_once __DIR__.'/vendor/autoload.php';

if (is_file(__DIR__.'/.env')) {
    (new Dotenv())->loadEnv(__DIR__.'/.env');
}

function env(string $var): string
{
    if (isset($_SERVER[$var]) && '' !== $_SERVER[$var]) {
        return $_SERVER[$var];
    }

    output()->writeln(sprintf('<error>Please set the "%s" environment variable to run this example.</error>', $var));
    exit(1);
}

function output(): ConsoleOutput
{
    return new ConsoleOutput();
}

function http_client(): HttpClientInterface
{
    if (in_array('--mock', $_SERVER['argv'] ?? [], true)) {
        return new MockHttpClient([
            new MockResponse(json_encode([
                'model' => 'jev-1.13.0',
                'answers' => [
                    'is_urgent' => [
                        'type' => 'noul',
                        'noul' => 0.95,
                    ],
                    'department' => [
                        'type' => 'choice',
                        'choice' => 'billing',
                        'probabilities' => [
                            'billing' => 0.88,
                            'technical' => 0.12,
                            'sales' => 0.0,
                        ],
                        'confidence' => 0.81,
                    ],
                    'frustration' => [
                        'type' => 'score',
                        'score' => 1.05,
                        'legend' => [
                            '0' => 'Calm',
                            '1' => 'Frustrated',
                            '2' => 'Very angry',
                        ],
                        'probabilities' => [
                            '0' => 0.0,
                            '1' => 0.95,
                            '2' => 0.05,
                        ],
                        'confidence' => 0.92,
                    ],
                ],
                'usage' => [
                    'input_tokens' => 307,
                    'output_tokens' => 20,
                ],
            ], \JSON_THROW_ON_ERROR), [
                'http_code' => 200,
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);
    }

    return HttpClient::create();
}
