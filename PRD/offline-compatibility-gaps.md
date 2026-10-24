# PRD: оставшиеся офлайн-риски совместимости

## Цель и границы

Восстановить штатную сборку Docker для PHP 7.3/7.4, проверить минимальное и production-разрешение Composer и оценить возможность офлайн-проверки Billing через фасад `token()`. База: `docs/agent-development-roadmap` `fba9bcd434260a4cfa93cb147380637bee6c4ee8`. Публичный API, `src/`, PHP policy, README, Composer constraints, production и клиентские данные не меняются.

## Подтверждённые факты и открытые вопросы

- Рабочая копия на базе указанного SHA была чистой; ветка опережала `origin/docs/agent-development-roadmap` на три коммита.
- `make build PHP_VERSION=7.4` ранее остановился: текущий Xdebug требует PHP ≥8.0. `make build PHP_VERSION=7.3` ранее остановился: в Alpine v3.15 нет `icu-data-full`. Временные Dockerfile с Xdebug 3.1.6 и без этого пакета прошли локальную матрицу; результаты и версии приведены в `PRD/consumer-contract-offline.md`.
- `docker/Dockerfile` устанавливает latest Xdebug для всех PHP и `icu-data-full` для всех образов. Менять только эти условия; PHP 8.0 остаётся на текущем варианте Xdebug.
- `token()` создаёт два `new Client()` внутри функции. MockHandler в текущем тесте можно передать `FromGateway` или `token_with_client()`, но не этому фасаду. Доказана только отложенность конструирования `token()`; прямой офлайн Billing через фасад остаётся вопросом реализуемости без тестового крюка или сетевой подмены.
- README утверждает PHP 8.0/8.1; CI тестирует 7.3/7.4/8.0; Composer не задаёт PHP constraint. Это открытое решение владельца.

## Совместимость и риски

Сохраняются `credentials()`, `token()`, `token_with_client()`, оба интерфейса, трёхаргументный `FromGateway`, `asKeyValue()`/`asString()`, отложенность, кеш и повтор после ошибки. Изменение Dockerfile может сломать сборку на одном из базовых образов; это поймает `make all` на 7.3/7.4/8.0. Минимальный набор Composer может быть неразрешим из-за транзитивных constraints; не подменять его частичным успехом. Production install нужно проверять в чистой копии, чтобы не удалять dev-зависимости из рабочего checkout.

## План и критерии приёмки

1. После независимого ревью PRD условно устанавливать Xdebug 3.1.6 для PHP ниже 8.0, оставив текущий Xdebug для 8.0, и не запрашивать `icu-data-full` в Alpine-образе PHP 7.3. Проверить `make all PHP_VERSION=7.3`, `7.4`, `8.0` с результатами unit и coverage.
2. В отдельных чистых копиях проверить `composer update --prefer-lowest` и `composer install --no-dev` без внешнего API. Записать точные команды, версии PHP/Composer/URL/Guzzle, exit codes и применимость unit/coverage. Для production проверить загрузку публичных функций и построение объекта `token()` без сети.
3. Изучить офлайн-возможность Billing через `token()` без изменения публичного поведения. Если она требует замены глобального HTTP-транспорта или production-крюка только ради теста, оставить C2 открытым и записать техническую причину; иначе добавить контрактный тест с проверкой запросов и cache/retry.
4. Провести независимое ревью полного diff, разобрать замечания, выполнить документальные проверки, сохранить доказательства в PRD и ревью. Не запускать интеграцию без подтверждённой тестовой клиники.

## Проверки

Локальные проверки выполнены на содержимом diff от `fba9bcd434260a4cfa93cb147380637bee6c4ee8` до коммита. Docker 29.8.2, PHP 7.3.33/7.4.33/8.0.30, Composer 2.10.3. Основной lock: URL 0.3.2, Guzzle 7.3.0. Полные логи команд в `/tmp/vetmanager-token-*.log` на `otis-home`; `/tmp` не является артефактом репозитория.

| Команда и окружение | Exit code | Результат и ограничение |
| --- | ---: | --- |
| `make all PHP_VERSION=7.3` | 0 | Штатная сборка, install, style, static-analysis, unit; 44 теста, 145 assertions, 3 skipped, покрытие 94.51%. |
| `make all PHP_VERSION=7.4` | 0 | Те же стадии и результат: 44/145/3, 94.51%. |
| `make all PHP_VERSION=8.0` | 0 | Те же стадии и результат: 44/145/3, 94.51%. |
| `git archive HEAD \| tar -x -C /tmp/vetmanager-token-minimum-73-20261024`; затем `docker run --rm -v /tmp/vetmanager-token-minimum-73-20261024:/app -w /app vetmanager-token:7.3 composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction` | 0 | URL 0.3.2, Guzzle 7.15.2, PHPStan 0.12.19; чистое dev-разрешение. |
| Аналогичный `git archive HEAD` в `/tmp/vetmanager-token-minimum-20261024`; `docker run --rm -v /tmp/vetmanager-token-minimum-20261024:/app -w /app vetmanager-token:8.0 composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction` | 0 | URL 0.3.2, Guzzle 7.15.2, PHPStan 0.12.33; чистое dev-разрешение. |
| В обеих minimum-копиях: `docker run --rm -v <копия>:/app -w /app vetmanager-token:<PHP> php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --testsuite main`; затем аналогично `vendor/bin/php-coverage-checker build/logs/clover.xml 90` | 0 каждая | 44/145/3; покрытие 93.93% на 7.3 и 8.0. |
| `docker run --rm -v /tmp/vetmanager-token-minimum-73-20261024:/app -w /app vetmanager-token:7.3 composer check-static-analyze` | 1 | PHPStan 0.12.19 сообщил 20 ошибок, в том числе ложные ошибки загрузки классов тестов. Minimum dev gate открыт. |
| `docker run --rm -v /tmp/vetmanager-token-minimum-20261024:/app -w /app vetmanager-token:8.0 composer check-all` | 255 | `composer validate` и style прошли; PHPStan 0.12.33 упал с `TypeError` в собственной зависимости Nette до unit. Unit и coverage отдельно прошли, как указано выше. |
| `git archive HEAD \| tar -x -C /tmp/vetmanager-token-production-20261024`; затем `docker run --rm -v /tmp/vetmanager-token-production-20261024:/app -w /app vetmanager-token:8.0 composer install --no-dev --prefer-dist --no-interaction` | 0 | Production install из lock: URL 0.3.2, Guzzle 7.3.0. Autoload и создание `token()` без сети прошли. |
| В отдельных чистых копиях `/tmp/vetmanager-token-production-{73-,}update-20261024`: `docker run --rm -v <копия>:/app -w /app vetmanager-token:<PHP> composer update --no-dev --prefer-dist --no-interaction` | 0 каждая | PHP 7.3: URL 0.3.2 / Guzzle 7.16.0; PHP 8.0: URL 1.1.1 / Guzzle 7.16.0. Autoload и создание `token()` без сети прошли в обеих копиях. |

Smoke-команда для production-копий: `php -r 'require "vendor/autoload.php"; $credentials = Otis22\VetmanagerToken\credentials("test", "test-password", "test-app"); $token = Otis22\VetmanagerToken\token($credentials, "test-clinic"); if (!$token instanceof Otis22\VetmanagerToken\Token) { exit(1); } echo "production autoload OK\n";'` внутри соответствующего `docker run`; все три вызова exit 0. Никакого сетевого запроса эта команда не делает. Для minimum на 8.0 первоначальный `composer unit` без Xdebug завершился 255 после успешных 44 тестов, потому что не создался Clover; повтор с включённым Xdebug и отдельным coverage-checker завершился 0.

Прямой C2 для фасада `token()` не добавлен. Фасад вызывает два `new Client()` без параметров; передать ему Guzzle MockHandler невозможно. Тест через подмену глобального HTTP-транспорта зависел бы от выбранного Guzzle handler и расширений PHP (curl/streams), а production-крюк или изменение сигнатуры затрагивали бы реализацию/контракт без потребительской причины. Существующий `TokenBillingRetryTest` проверяет Billing и повтор через `BillingUrl`/`Lazy` с инъекцией клиента; `ConsumerContractTest` отдельно проверяет создание фасада без запроса. Это оставляет конкретный пробел C2, а не утверждение о полной проверке фасада.

Интеграция не запускалась: тестовая клиника не подтверждена. Эти офлайн-результаты не определяют PHP policy. Финальный SHA и статус CI относятся к коммиту и указываются при передаче результата; SHA не может быть записан в содержащий его же коммит.

## Независимое ревью

PRD проверен до изменения Dockerfile: [`2026-10-24-offline-compatibility-prd.md`](reviews/2026-10-24-offline-compatibility-prd.md). Полный diff проверен после реализации: [`2026-10-24-offline-compatibility-diff.md`](reviews/2026-10-24-offline-compatibility-diff.md). Оба независимых заключения — `[]`; предмет, базовый SHA и ограничения записаны в файлах ревью.
