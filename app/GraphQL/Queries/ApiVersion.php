<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

class ApiVersion
{
    public function __invoke($_, array $args): string
    {
        $composerContents   = file_get_contents(dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'composer.json');
        $composerAttributes = json_decode($composerContents, true);

        return $composerAttributes['version'] ?? 'onbekend';
    }
}
