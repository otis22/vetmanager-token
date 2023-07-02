<?php

declare(strict_types=1);

namespace Otis22\VetmanagerToken\Credentials;

use Otis22\VetmanagerToken\Credentials;

final class FakeCredentials implements Credentials
{
    public function asKeyValue(): array
    {
        return [
            'login' => 'test',
            'password' => 'testPass111',
            'app_name' => 'test'
        ];
    }
}
