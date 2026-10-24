# Независимое ревью PRD: php-interfaces 1.0 и token() — 2026-10-24

Рецензент: Claude Sonnet через локальный CLI, `--tools ""`, без доступа к shell и изменений файлов. База `7fa25c60446f330beea4fdd879ba1277f043c161`. Предмет: полный текст `PRD/legacy-interface-and-facade.md` и релевантные `src/functions.php`, `src/Token/FromGateway.php`, `src/Token/FromGateway/JsonResponseInterface.php`; переданы результаты проб Composer/static-analysis.

Первое заключение: два блокирующих замечания. (1) Подмена URL через Reflection не проверяет оригинальный Billing resolver фасада, поэтому нельзя заявлять покрытие повтора Billing. (2) Нужно указать PHPDoc `KeyValue::asKeyValue()` в 1.0 и 1.1.1 и проверить статический анализ на обоих концах диапазона.

Исправление: PRD сузил фасадный тест до token POST/cache/retry, Billing отмечен остаточным риском и покрытием нижнего слоя. Типы родительского интерфейса установлены из двух установленных версий: `array<string,string>` в 1.0, `array<string,mixed>` в 1.1.1; PHP-сигнатура `array` одинакова. Обязательная проверка обеих версий внесена в план. Повторное заключение Sonnet по обновлённому полному PRD: `[]` (блокеров нет). Gate PRD закрыт до правки исполняемого кода; полный diff требует отдельного ревью.
