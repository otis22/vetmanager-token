# PRD: Офлайн-контракт token и подтверждённого потребителя

## Цель и границы

Зафиксировать тестами совместимость с `vetmanager-rest-api` и наблюдаемое поведение получения токена без сетевого доступа. База: `docs/agent-development-roadmap` `0acdf20cf89d00a4cc434f6670bcf9d1465e229e`; дерево совпадает с `origin/main` `f6d8db8565e04193d54bdd75312436dc942f569f`. Исходники `src/`, Composer constraints, README, CI и интеграционный контур не меняются.

## Подтверждённые факты и открытые вопросы

- `vetmanager-rest-api/composer.json` требует `otis22/vetmanager-token:^0.2.0`; его `composer.lock` фиксирует 0.2.3, SHA `ad2bac2fccfa75a1ae38c77ba110626aed721d61`.
- Потребитель напрямую конструирует `Credentials\AppName` и `Token\Concrete`, передаёт последний как `Token` в `Headers\Auth\ByToken`; результат — заголовки `X-APP-NAME` и `X-USER-TOKEN`.
- `git diff --quiet ad2bac2 HEAD -- src` завершился с exit 0: `src/` идентичен опубликованному 0.2.3. Тест в этом репозитории проверяет только экспортируемые типы и значения token-пакета; построение заголовков находится в `vetmanager-rest-api` и здесь не имитируется.
- `src/functions.php` предоставляет три фасада. Существующие MockHandler-тесты частично проверяют POST, cache и Billing, но прямой контракт потребителя и упорядоченные повторы HTTP/JSON по обеим реализациям не закреплены вместе.
- Конструкторы `GatewayResponse`, `JsonResponse`, `ValidJsonResponse`, `FromResponse` только сохраняют зависимости; отправка запроса происходит в `asString()`. `token()` создаёт собственные клиенты, поэтому его конструирование и тип проверяются без сети, а Billing-запрос через этот фасад не мокается. HTTP-поведение его реализации проверяется через прямой `FromGateway` с MockHandler и `Url\Lazy`.
- Полный список внешних потребителей и поддерживаемая PHP-матрица не установлены. README, CI и Composer дают разные сигналы по PHP. Решение владельца требуется отдельно.

## Совместимость и риски

Тесты должны сохранить `Credentials::asKeyValue()`, `Token::asString()`, `Token\Concrete(string)`, `Credentials\AppName(string)`, трёхаргументный `Token\FromGateway`, фасады, URI/form/headers, отложенность, cache и повтор после ошибки. Использовать только синтаксис PHP 7.3. MockHandler не подтверждает реальную интеграцию или весь диапазон зависимостей. Точные тексты ошибок, `TypeError` от некорректного ответа и внутреннюю цепочку `previous` не закрепляем: это не установленный контракт потребителя.

| Сценарий MockHandler, обе реализации | После конструирования | Первый `asString()` | Второй `asString()` |
| --- | --- | --- | --- |
| Успешный JSON token | 0 POST | token, 1 POST | тот же token, всего 1 POST |
| HTTP 401 с валидным JSON ошибки, затем успех | 0 POST | `Exception`, 1 POST | token, всего 2 POST |
| HTTP 200 с невалидным JSON, затем успех | 0 POST | `Exception`, 1 POST | token, всего 2 POST |

Для прямого `FromGateway` и `token_with_client()` с `Url\Concrete('https://clinic.example')` ожидается один и тот же `POST https://clinic.example/token_auth.php`, form `login`, `password`, `app_name` и сохранённый header клиента. Billing-ошибки и повтор через `Url\Lazy` уже покрыты `TokenBillingRetryTest`; их отдельное усиление допускается только при выявленном пробеле.

## План и критерии приёмки

1. Проверить `docker/Dockerfile` и воспроизвести `make all PHP_VERSION=8.0`; записать точный exit code, версии и число тестов или точный блокер. Это E1.
2. После независимого ревью данного PRD добавить офлайн-тест экспортируемых типов потребителя и MockHandler-сценарии для `FromGateway` и `token_with_client()`: ноль запросов при конструировании, один POST после первого `asString()`, cache, точный form/URI/headers, повтор после HTTP и JSON ошибок. Это C1/C2 в пределах поручения.
3. Проверить доступные ячейки текущего CI: lock на PHP 7.3/7.4/8.0, а URL 0.3.2 и 1.1.0 на PHP 7.4/8.0. Для каждой выполненной ячейки записать фактический URI и команду; недоступные отметить «не проверено». Это результат теста, а не новая декларация поддержки. Записать exit codes, версии, число тестов и coverage.
4. Провести независимое ревью полного diff после тестов, разобрать замечания. Документировать открытые гейты и исходный/финальный SHA.

## Проверки

| Команда и окружение | Exit code | Результат и ограничение |
| --- | ---: | --- |
| `git diff --quiet HEAD origin/main` | 0 | Деревья одинаковы на старте; это не равенство SHA. |
| `docker info --format '{{.ServerVersion}}'` | 0 | Docker 29.8.2 на старте. |
| `make all PHP_VERSION=8.0` до тестов | 0 | PHP 8.0.30, Composer 2.10.3, URL 0.3.2, Guzzle 7.3.0; 36 тестов, 96 assertions, 3 skipped, покрытие 91.67%. |
| `git archive HEAD | tar -x -C /tmp/vetmanager_token_baseline_20261024` и `make -C /tmp/vetmanager_token_baseline_20261024 all PHP_VERSION=8.0` | 0 | Чистая копия базового HEAD: 36 тестов, 96 assertions, 3 skipped, покрытие 91.67%; E1 выполнен на исходном срезе. |
| `make all PHP_VERSION=8.0` после тестов | 0 | Те же версии; стиль и анализ успешны, 44 теста, 145 assertions, 3 skipped, покрытие 94.51%. |
| `composer update --with otis22/vetmanager-url:1.1.0 --prefer-dist --no-interaction` в отдельной копии под `vetmanager-token:8.0` | 0 | URL 1.1.0, Guzzle 7.16.0; основной `composer.lock` сохранён. |
| `composer check-style`, `composer check-static-analyze`, `phpunit --testsuite main`, `php-coverage-checker ... 90` в отдельной копии под PHP 8.0.30 | 0 каждый | URL 1.1.0: 44 теста, 159 assertions, 1 skipped, покрытие 94.45%; прямые URI assertions прошли. |
| `make build PHP_VERSION=7.4` | 2 | Dockerfile запрашивает текущий Xdebug 3.5.3, который требует PHP ≥8.0. |
| `make build PHP_VERSION=7.3` | 2 | В Alpine v3.15 нет `icu-data-full`; до тестов не дошло. |
| `composer validate --no-check-publish` под PHP 8.0.30 | 0 | `composer.json` валиден. |

Для проверки существующих ячеек CI без изменения репозитория временные Dockerfile в `/tmp/vetmanager-token-docker-7.3/` и `/tmp/vetmanager-token-docker-7.4/` заменили `pecl install xdebug` на `pecl install xdebug-3.1.6`; вариант 7.3 также удалил установку отсутствующего `icu-data-full`. Оба временных образа собраны (exit 0), PHP 7.3.33/7.4.33 и Composer 2.10.3. Для каждой ячейки использована отдельная копия `composer.json`, `composer.lock`, `phpunit.xml`, `src/`, `tests/` в `/tmp`; основной lock не изменён. Последовательность команд в каждом контейнере: `composer install --no-interaction` для URL 0.3.2 или `composer update --with otis22/vetmanager-url:1.1.0 --prefer-dist --no-interaction`, затем `composer check-style`, `composer check-static-analyze`, `php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --testsuite main`, `vendor/bin/php-coverage-checker build/logs/clover.xml 90`. Все перечисленные команды в нижних ячейках завершились с exit 0.

Точные команды запуска для каждой пары: `docker run --rm -v <абсолютный временный каталог>:/app -w /app <образ> <команда>`, где образы `vetmanager-token-matrix:7.3`, `vetmanager-token-matrix:7.4`, `vetmanager-token:8.0`; каталоги соответственно `/tmp/vetmanager_token_73_url03`, `/tmp/vetmanager_token_74_url03`, `/tmp/vetmanager_token_74_url11`, основной checkout для 8.0/0.3.2 и `/tmp/vetmanager-token-matrix.wqVXeT` для 8.0/1.1.0. Сборка временных образов: `docker build --build-arg VERSION=7.3 --tag vetmanager-token-matrix:7.3 /tmp/vetmanager-token-docker-7.3` и аналогично `7.4`. Логи запусков находятся в `/tmp/vetmanager-token-{73-url03,74-url03,74-url11}.log`; файлы `/tmp` не входят в репозиторий.

| PHP | URL / Guzzle | Тесты / assertions / skipped | Coverage | Ожидаемый URI в новых тестах |
| --- | --- | --- | ---: | --- |
| 7.3.33 | 0.3.2 / 7.3.0 | 44 / 145 / 3 | 94.51% | `https://clinic.example/token_auth.php`, assertion пройден |
| 7.4.33 | 0.3.2 / 7.3.0 | 44 / 145 / 3 | 94.51% | тот же, assertion пройден |
| 7.4.33 | 1.1.0 / 7.16.0 | 44 / 159 / 1 | 94.45% | тот же, assertion пройден |
| 8.0.30 | 0.3.2 / 7.3.0 | 44 / 145 / 3 | 94.51% | тот же, assertion пройден |
| 8.0.30 | 1.1.0 / 7.16.0 | 44 / 159 / 1 | 94.45% | тот же, assertion пройден |

Штатный `make all` для PHP 7.3/7.4 не проходит на этапе сборки исходного Dockerfile. Успех обходного локального контейнера не означает успеха штатного `make` и не устанавливает официальную матрицу поддержки. Не проверены minimum dependency resolutions, production install и сетевой integration gate. Нынешние тесты не доказывают запрос Billing через фасад `token()`, потому что он создаёт клиент внутри себя; существующие тесты проверяют Billing через `BillingUrl`/`Url\Lazy` и MockHandler. Интеграцию не запускали без подтверждённой тестовой клиники.

Коммит проверенных тестов и плана: `dd1d0a2fd2e978623e2da8a2aa86fb398e9a7f29`. Локальные проверки выше выполнены на его содержимом до фиксации; `git show --check --oneline dd1d0a2` — exit 0, рабочая копия после коммита была чистой. CI для этого SHA не запускался. Следующее изменение данного PRD только записывает доказательства и не меняет тесты.

## Независимое ревью

PRD проверен до кода: [`2026-10-24-consumer-contract-prd.md`](reviews/2026-10-24-consumer-contract-prd.md). Полный diff после кода: [`2026-10-24-consumer-contract-diff.md`](reviews/2026-10-24-consumer-contract-diff.md). Оба заключения Claude Sonnet после исправления замечаний — `[]`; предмет, база SHA и ограничения указаны в файлах ревью. Это не проверка интеграции и не CI финального SHA.
