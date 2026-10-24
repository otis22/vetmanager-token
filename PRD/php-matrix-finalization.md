# PRD: подтверждение PHP-матрицы и оставшихся контрактных проверок

## Цель и границы

Для линии 0.2.x зафиксировать утверждённую владельцем поддержку PHP 7.3, 7.4 и 8.0, проверить потребительский вызов `token()`, ручную интеграцию только с devtr6 и минимальное разрешение зависимостей. База: чистый `docs/agent-development-roadmap` `8f92648c017ee1138706ab1619bee8b2802919d9` (2026-10-24). Релиз и production deploy не входят в задачу.

## Подтверждённые факты и открытые вопросы

- Владелец утвердил поддержку PHP 7.3/7.4/8.0 для 0.2.x; PHP 8.1 не подтверждён. Ручная интеграция разрешена только с изолированной devtr6. Значения учётных данных в артефакты не переносить.
- `composer.json` не содержит требования `php`; README утверждает 8.0/8.1; CI уже проверяет 7.3/7.4/8.0. Предыдущий локальный `make all` прошёл во всех трёх версиях, но minimum PHPStan не прошёл. Подробности — `PRD/offline-compatibility-gaps.md`.
- `token()` сам создаёт Guzzle-клиенты. В текущих тестах MockHandler доказывает работу нижележащих объектов, но не фактический запрос через этот фасад. Ручная devtr6-интеграция вызывает фасад, однако не доказывает все варианты офлайн.
- Интеграционный тест неверного пароля может завершиться без assertion, если исключение не выброшено. Исправить это как отдельный тестовый дефект.
- В локальном игнорируемом `.env` обнаружены необходимые имена переменных и подтверждено, что домен равен devtr6, без чтения значений в вывод. Перед запуском ещё раз проверить это условие.

## Совместимость и риски

Сохранить функции `credentials()`, `token()`, `token_with_client()`, интерфейсы и конструктор `FromGateway`, lazy/cache/retry. Добавление `php: >=7.3 <8.1` исключит непроверенные PHP. Допустимые URL-пары: PHP 7.3 × URL 0.3.2; PHP 7.4/8.0 × URL 0.3.2 и 1.1.0. Минимальные и production-разрешения проверяются отдельно. `php-interfaces` 1.0 документирует `KeyValue::asKeyValue()` как `array<string,string>`, хотя наследующий `JsonResponseInterface` фактически возвращает вложенный JSON-массив. В 1.1.1 контракт допускает `mixed`, как требует этот код. Повышение нижней версии устраняет реальное расхождение статического контракта, но может создать Composer-конфликт у существующего потребителя, закрепившего 1.0; это оставшийся риск 0.2.x. При этом старые unit с 1.0 проходили, поэтому runtime-несовместимость 1.0 не утверждается. Минимальный PHPStan также имел ошибки собственного инструментария; отделить их от реальной ошибки в `src/`. Интеграцию запускать локально только с подтверждённой devtr6 и без видимого вывода секретов/ответов API.

## План и критерии приёмки

1. Получить независимое ревью этого PRD до правки исполняемого кода.
2. Согласовать Roadmap, Tasks, README и Composer с утверждённой матрицей; сохранить CI 7.3/7.4/8.0 и отдельно отметить границу PHP 8.1.
3. Исправить негативный интеграционный assertion: без исключения тест обязан падать. `make integration PHP_VERSION=8.0` с `.env`, предварительно проверенным на devtr6, должен подтвердить успешный потребительский `token()` и отказ для неверного пароля. Кеш/retry для прямого фасада оставить явным ограничением C2, если контролируемый транспорт недоступен без изменения публичной реализации.
4. Проверить `make all PHP_VERSION=7.3`, `7.4`, `8.0`; в отдельных временных копиях `composer update --with otis22/vetmanager-url:<version>` и `composer unit` для пяти допустимых пар; отдельно `composer update --prefer-lowest --prefer-stable` и `composer update --no-dev` для 7.3/8.0. Для minimum запустить `composer check-static-analyze` и отличить ошибки `src/` от сбоя инструментария. Остановиться при ошибке базовой матрицы до публикации. Проверить CI на итоговом SHA, если доступен.
5. Получить независимое ревью полного diff, разобрать замечания и сохранить точные результаты, SHA, ссылки и границы доказательств.

## Проверки

| Команда и окружение | Exit code | Результат и ограничение |
| --- | ---: | --- |
| `docker run --rm -v "$case_dir":/app -w /app vetmanager-token:7.3 composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction --no-progress`; затем там же `composer check-static-analyze` в копии исходного HEAD | 0, 1 | Исходный минимум: `php-interfaces` 1.0, PHPStan 0.12.19, detector 2.0.1. PHPStan дал 20 ошибок: 3 из-за неверного статического типа JSON, 17 при загрузке тестовых классов. |
| Проба PHP 7.3 с `php-interfaces` 1.0, PHPStan 0.12.82 и detector 2.0.25: те же `composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction --no-progress` и `composer check-static-analyze` | 0, 1 | Остались 5 ошибок типов вложенного JSON в `src/` и тестах. Это показывает, что одного обновления анализатора недостаточно. |
| То же с пробными нижними границами `php-interfaces:^1.1.1`, PHPStan `^0.12.82`, detector `^2.0.25`, PHP 7.3 | 0, 0 | Минимальный static-analysis прошёл. Промежуточная проба с PHPStan 1.12.0 выявила 9 дополнительных предупреждений о `mixed`, но это не минимальная поддерживаемая версия анализатора. |
| `make all PHP_VERSION=7.3`, `7.4`, `8.0` | 0, 0, 0 | Docker PHP 7.3.33/7.4.33/8.0.30, Composer 2.10.3; lock URL 0.3.2, Guzzle 7.3.0. В каждой ячейке style, static-analysis, 44 теста, 145 assertions, 3 skipped, покрытие 94.51%. |
| `composer update --with otis22/vetmanager-url:<версия> --prefer-dist --no-interaction --no-progress`, затем PHPUnit с Xdebug, coverage-checker и `composer check-static-analyze` в пяти отдельных копиях | 0 во всех пяти для update/unit/static | PHP 7.3×URL 0.3.2, PHP 7.4/8.0×URL 0.3.2/1.1.0. Guzzle 7.16.0. Для URL 0.3.2: 44 теста, 145 assertions, 3 skipped, покрытие 93.93%. Для URL 1.1.0: 44/159/1, 94.45%. |
| `composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction --no-progress`, `composer check-static-analyze`, PHPUnit с Xdebug и coverage-checker в двух отдельных копиях PHP 7.3/8.0 | 0 для всех команд | URL 0.3.2, Guzzle 7.15.2, `php-interfaces` 1.1.1, PHPStan 0.12.82, detector 2.0.25. В обеих ячейках 44/145/3, покрытие 93.93%; static-analysis проходит. |
| `composer update --no-dev --prefer-dist --no-interaction --no-progress`, затем autoload и создание `token()` без сети в отдельных копиях PHP 7.3/8.0 | 0 в обеих | PHP 7.3: URL 0.3.2/Guzzle 7.16.0; PHP 8.0: URL 1.1.1/Guzzle 7.16.0. Smoke подтверждает только autoload и конструирование фасада. |
| Guard `.env` на непустые `TEST_*` и точное равенство домена `devtr6`, затем `make integration PHP_VERSION=8.0` | 0, 0 | 2 теста, 3 assertions. Успешный `token()` и отказ для неверного пароля подтверждены на devtr6. Секреты и ответы API в артефакты не записывались. Кеш/retry фасада этим не доказаны. |

Для каждой чистой копии использовалась команда `case_dir=$(mktemp -d /tmp/vt-XXXXXX); rsync -a --exclude=.git --exclude=vendor --exclude=.env --exclude=build "$PWD/" "$case_dir/"`. На пяти парах `(PHP_VERSION, URL_VERSION)=(7.3,0.3.2),(7.4,0.3.2),(7.4,1.1.0),(8.0,0.3.2),(8.0,1.1.0)` после этого выполнялись точно:

```sh
docker run --rm -v "$case_dir":/app -w /app "vetmanager-token:$PHP_VERSION" composer update --with "otis22/vetmanager-url:$URL_VERSION" --prefer-dist --no-interaction --no-progress
docker run --rm -v "$case_dir":/app -w /app "vetmanager-token:$PHP_VERSION" sh -c 'php -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit --testsuite main && vendor/bin/php-coverage-checker build/logs/clover.xml 90'
docker run --rm -v "$case_dir":/app -w /app "vetmanager-token:$PHP_VERSION" composer check-static-analyze
```

Для minimum вместо первого `composer update` использовался `composer update --prefer-lowest --prefer-stable --prefer-dist --no-interaction --no-progress`, затем те же unit и static-analysis. Для production — `composer update --no-dev --prefer-dist --no-interaction --no-progress`, затем `php -r 'require "vendor/autoload.php"; $c=Otis22\VetmanagerToken\credentials("test", "test", "test"); $t=Otis22\VetmanagerToken\token($c, "test-clinic"); exit($t instanceof Otis22\VetmanagerToken\Token ? 0 : 1);'` в контейнере. Команды выполнены на рабочем дереве относительно базового SHA `8f92648c017ee1138706ab1619bee8b2802919d9`; финальный commit SHA и CI run следует привязать после фиксации дерева. Указанный SHA является базой diff, а не SHA результата проверки.

## Независимое ревью

PRD: [`2026-10-24-php-matrix-prd.md`](reviews/2026-10-24-php-matrix-prd.md), замечания устранены до кода. Полный diff: [`2026-10-24-php-matrix-diff.md`](reviews/2026-10-24-php-matrix-diff.md), замечания разобраны после реализации до коммита.
