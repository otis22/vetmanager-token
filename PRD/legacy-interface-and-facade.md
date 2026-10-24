# PRD: совместимость php-interfaces 1.0 и наблюдаемый token()

## Цель и границы

Сохранить установку линии 0.2.x у потребителя, закрепившего `otis22/php-interfaces:1.0.*`, и проверить кеширование и повтор после ошибки через объект, возвращённый публичным `token()`. База: чистый HEAD `7fa25c60446f330beea4fdd879ba1277f043c161`, PR #4. Релиз, merge и deploy не входят в работу.

## Подтверждённые факты и открытые вопросы

- Изолированный потребитель с `vetmanager-token:0.2.3` из path-репозитория и `php-interfaces:1.0.*`: `composer update --dry-run --no-scripts --prefer-dist --no-interaction --no-progress` на PHP 8.0.30/Composer 2.10.3 завершился с exit 2 из-за требования библиотеки `^1.1.1`.
- В отдельной копии замена ограничения на `^1.0` и уточнение `JsonResponseInterface::asKeyValue()` как `array<string,mixed>` дали `composer update --with otis22/php-interfaces:1.0` и `composer check-static-analyze` с exit 0. Это пока проверка варианта, не итоговый код. В 1.0 `KeyValue::asKeyValue()` имеет PHPDoc `array<string,string>`, в установленной 1.1.1 — `array<string,mixed>`; PHP-сигнатура в обеих версиях — `array`. Вариант с прежним PHPDoc 1.0 даёт пять ошибок PHPStan на вложенном JSON.
- `token()` возвращает `FromGateway`, но создаёт два `new Client()` внутри. `MockHandler` невозможно передать через его публичные аргументы. В тесте можно заменить приватный HTTP-клиент возвращённого объекта через Reflection; это проверит наблюдаемое кеширование и повтор после ошибки token POST через объект публичного фасада, но не стандартное разрешение Billing URL. Подмена URL не даёт права утверждать, что проверен оригинальный Billing resolver.

## Совместимость и риски

Вернуть допустимый диапазон `php-interfaces:^1.0`, сохранив наследование `JsonResponseInterface` от `KeyValue` и публичные PHP-сигнатуры. Уточнить только PHPDoc JSON-массива. PHPDoc дочернего интерфейса шире ошибочного описания базового 1.0, поскольку существующий JSON реально содержит вложенный массив; это расхождение статической документации базовой версии нельзя устранить в зависимости. Проверить оба конца диапазона и зафиксировать предел гарантии. Не менять публичные функции и конструктор. Reflection в тесте привязан к внутренним свойствам; при рефакторинге тест потребует обновления. Он не докажет транспорт Billing, создаваемый внутри `token()`; этот остаточный риск обозначить отдельно.

## План и критерии приёмки

1. Получить независимое ревью этого PRD до изменения исполняемого кода.
2. Вернуть Composer-диапазон `^1.0`, обновить lock штатным Composer; добавить PHPDoc для JSON-контракта. Проверить установку отдельного потребителя с 1.0 и static-analysis/unit на 1.0 и 1.1.1.
3. Добавить офлайн тест: создать токен через `token()`, подтвердить `FromGateway`, через Reflection с `setAccessible(true)` контролируемо подменить его приватные URL/HTTP зависимости на MockHandler, вызвать `asString()` после ошибки token POST, затем после успешного ответа повторно. Счётчики запросов должны показать один POST на попытку и отсутствие новых запросов после успеха. Billing retry остаётся покрытым нижележащим `TokenBillingRetryTest`, а не этим тестом фасада.
4. Выполнить `make all PHP_VERSION=8.0` и относящиеся PHP 7.3/7.4 проверки; проверить Composer-разрешение потребителя. Зафиксировать точные результаты и ограничения в этом PRD. Отдельно проверить static-analysis с `php-interfaces` 1.0 и 1.1.1.
5. Получить независимое ревью полного diff, разобрать замечания, обновить Roadmap/Tasks, зафиксировать и отправить в существующий PR; проверить CI итогового SHA и доступность ссылки.

## Проверки

| Команда и окружение | Exit code | Результат и ограничение |
| --- | ---: | --- |
| Проба Composer потребителя на исходном SHA | 2 | Конфликт `1.0.*` против `^1.1.1`. |
| Проба `composer update --with otis22/php-interfaces:1.0.0` и `composer check-static-analyze` в копии | 0, 0 | Вариант `^1.0` плюс PHPDoc проходит; остальные проверки впереди. |
| `docker run ... composer update --lock --no-install --no-scripts --prefer-dist --no-interaction --no-progress` на рабочем дереве | 0 | В `composer.lock` обновился только `content-hash`; остаётся `php-interfaces` 1.1.1. |
| Изолированный потребитель с `vetmanager-token:0.2.3` через локальный path и `php-interfaces:1.0.*`: `composer update --dry-run --no-scripts --prefer-dist --no-interaction --no-progress` после правки | 0 | Разрешились token 0.2.3 и interfaces 1.0. Path-версия 0.2.3 задана в тестовом проекте; это проверка Composer-совместимости текущего дерева, не опубликованного тега. |
| `make all PHP_VERSION=7.3`, `7.4`, `8.0` | 0, 0, 0 | Docker PHP 7.3.33/7.4.33/8.0.30, Composer 2.10.3; lock URL 0.3.2, interfaces 1.1.1. В каждой ячейке style/static-analysis и 45 тестов, 153 assertions, 3 skipped; покрытие 94.51%. |
| Копия с `php-interfaces:1.0`, URL 1.1.1, Guzzle 7.16.0, PHPStan 0.12.100 на PHP 8.0: `composer check-static-analyze`; PHPUnit с Xdebug и coverage-checker | 0, 0 | 45 тестов, 167 assertions, 1 skipped; покрытие 94.45%. |
| Чистая копия на PHP 7.3: `composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction --no-progress`; `composer check-static-analyze`; PHPUnit с Xdebug и coverage-checker | 0, 0, 0 | interfaces 1.0, URL 0.3.2, Guzzle 7.15.2, PHPStan 0.12.82; 45 тестов, 153 assertions, 3 skipped, покрытие 93.93%. |

Чистые копии создавались `case_dir=$(mktemp -d /tmp/vt-XXXXXX); rsync -a --exclude=.git --exclude=vendor --exclude=.env --exclude=build "$PWD/" "$case_dir/"`. Для unit применялась команда `docker run --rm -v "$case_dir":/app -w /app vetmanager-token:<PHP> sh -c 'php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --testsuite main && vendor/bin/php-coverage-checker build/logs/clover.xml 90'`. `make integration` не запускался: этот этап не меняет интеграционное поведение и повторяет уже выполненный devtr6 gate предыдущего SHA. Контролируемый тест фасада подменяет приватные зависимости только в тесте, поэтому остаётся риск непроверенного Billing resolver непосредственно внутри `token()`. CI и итоговый SHA заполняются после публикации.

## Независимое ревью

Заключение до кода — [`2026-10-24-legacy-interface-prd.md`](reviews/2026-10-24-legacy-interface-prd.md); заключение по полному diff — [`2026-10-24-legacy-interface-diff.md`](reviews/2026-10-24-legacy-interface-diff.md). Оба выполнены независимым Claude Sonnet с конкретными текстами и без инструментов. Первоначальные замечания PRD исправлены до кода; ревью полного diff вернуло `[]`.
