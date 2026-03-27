# CHANCHELOG

## 2026-03-27

- Нормализовано основное `src/` дерево под Symfony-oriented слои: `Command`, `Contract`, `Controller`, `DependencyInjection`, `EventSubscriber`, `Provider`, `Registry`, `Resetter`, `Service`, `ServiceInterface`, `Warmup`.
- Убраны альтернативные interface-слои `RuntimeInterface`, `InfraInterface`, `HttpInterface`; сервисные интерфейсы перенесены в `src/ServiceInterface/...`.
- Перенесены и переподключены контроллеры, команды, subscriber-ы, provider-ы, registry и warmup/resetter классы.
- Bundle и DI классы выровнены относительно корня `App\\ => src/`.
- Упрощены контроллеры: удалены лишние controller interfaces.
- Нормализован `config/` под `config/packages`, `config/routes`, `config/services`; убраны дубли `service|services`, `route`, `package` и старые `_`/`-` варианты.
- Исправлены route/service ссылки на новые namespace.
- Исправлен `resource/config/package.yaml`: корректный env default wiring через parameter-backed defaults.
- Добавлен полезный пакет `symfony/yaml`, необходимый для загрузки bundle YAML-конфигов и DI-тестов.
- Обновлён `phpunit/phpunit` до патч-версии `10.5.63`, устранена уязвимость из `composer audit`.
- Тестовый каталог нормализован: `Test/` перенесён в `tests/`, обновлены `composer.json` и `phpunit.xml.dist`.
- Fixture helper выведен из тест-сьюта и переименован в `RuntimeStateSafetyFixture`.
- Исправлены реальные дефекты, выявленные тестами:
- неверный путь к `resource/config` в extension;
- слишком жёсткая нормализация bool/int config input;
- fallback-логика `RuntimeEndpointGuard`;
- escaping в `RuntimePrometheusFormatter` и `RuntimePrometheusExporter`.

## Verification

- `composer dump-autoload`
- `php vendor/bin/phpunit -c phpunit.xml.dist`
- `composer audit`
