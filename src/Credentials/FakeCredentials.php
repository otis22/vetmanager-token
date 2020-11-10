<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use Otis22\VetmanagerToken\Credentials;

final class FakeCredentials implements Credentials
{
    public function asAssoc(): array
    {
        return [
            'login' => 'test',
            'password' => 'test',
            'app_name' => 'test'
        ];
    }
}
