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


## URL library compatibility

Both `vetmanager-url` 0.x (`~0.1`) and 1.x starting from 1.1 are supported.
Existing token APIs are unchanged; creating a token with `token()` does not perform
network requests until `asString()` is called. Applications opting into URL 1.1 need PHP 7.4 or newer.

Use `token_with_client()` to obtain a token with your own HTTP client and a prepared
clinic URL. It works with both supported URL versions and sends the token request only
when `asString()` is called. Reusing the returned token object reuses the obtained token.

With `vetmanager-url:^1.1`, configure Billing discovery separately:

```php
use GuzzleHttp\Client;
use function Otis22\VetmanagerUrl\url;
use function Otis22\VetmanagerToken\credentials;
use function Otis22\VetmanagerToken\token_with_client;

$billingClient = new Client(['headers' => ['User-Agent' => 'my-service/1.0']]);
$crmClient = new Client(['headers' => ['User-Agent' => 'my-service/1.0']]);
$clinicUrl = url('myclinic', $billingClient); // Billing request happens here.
$token = token_with_client(credentials('login', 'ExamplePassword123', 'app_name'), $clinicUrl, $crmClient);
echo $token->asString(); // Token request happens here.
```

Keep CRM authorization headers on the CRM client, separate from the Billing client.
The existing `token($credentials, $domainName)` helper retains default Guzzle clients
and deferred URL discovery. Updating the dependency alone does not add a project User-Agent.

The default Billing host follows the installed URL library: `billing-api.vetmanager.cloud`
with 0.3.2 and `billing-api.vetmanager.ru` with 1.1.

Billing URL resolution failures propagate separately from token endpoint responses.

Compatibility with the released token 0.2.2 API is preserved: the three-argument
`Token\FromGateway` constructor still accepts credentials, a clinic URL and a client.
Login and password values are passed through unchanged; authentication is validated by CRM.
The newer response-based implementation is available separately as `Token\FromResponse`.
A failed Billing response is not cached by the legacy helper: using the same token again
retries URL discovery.

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
