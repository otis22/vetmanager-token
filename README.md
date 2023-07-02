![GitHub CI](https://github.com/otis22/vetmanager-token/workflows/CI/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/otis22/vetmanager-token/badge.svg?branch=main)](https://coveralls.io/github/otis22/vetmanager-token?branch=main)
# vetmanager-token

Vetmanager - CRM for veterinary with REST API. vetmanager-token is library for work with token auth in Vetmanager API.

[Vetmanager REST API Docs](https://vetmanager.ru/knowledgebase/rest-api-osnovnaya-informatsia)

[Vetmanager REST API in Postman](https://god.postman.co/run-collection/64d692ca1ea129218ccb)

## How to use 

```php
use function Otis22\VetmanagerToken\credentials;
use function Otis22\VetmanagerToken\token;

$credentials = credentials('login', 'password', 'app_name');
$domainName = 'myclinic'; // first part from programm url address
echo token($credentials, $domainName)->asString();
```


## Contributing

For run all tests
```shell
make all
```
or connect to terminal
```shell
make exec
```
*Dafault php version is 8.0*. Use PHP_VERSION= for using custom version. Project works only with 8.0 and 8.1 version.
```shell
make all PHP_VERSION=8.1
# run both 
make all PHP_VERSION=8.1 && make all
```

*For integration tests copy .env.example to .env and fill with yours values*

all commands
```shell
# security check
make security
# composer install
make install
# composer install with --no-dev
make install-no-dev
# check code style
make style
# run static analyze tools
make static-analyze
# run unit tests
make unit
#  check coverage
make coverage
# check integration, .env required
make integration
```
