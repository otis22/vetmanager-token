![GitHub CI](https://github.com/otis22/vetmanager-token/workflows/CI/badge.svg)

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


## For contributors 

#### Run docker container
```
cd docker
docker-compose up
```

now you can connect to terminal

```
docker exec -it vetmanager-token /bin/bash
```

## Run tests

```
#validate composer json
composer check-composer

#static analyzes and codestyle 
composer static

#run unit tests
composer unit-tests

#run all tests
composer all-tests
```
