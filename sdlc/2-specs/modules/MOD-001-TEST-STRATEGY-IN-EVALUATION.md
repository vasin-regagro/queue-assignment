# MOD-001-TEST-STRATEGY-IN-EVALUATION

## Назначение

Определить обязательные уровни тестирования Queue Assignment, правила
изоляции, критерии прохождения и формат доказательств готовности MVP.

## Источники

- [PRD NFR-01–NFR-07, критерии 18 и 24, DEC-015](../../0-vibes/prd/prd.md)
- [PT-012](../../1-business-tasks/planning/PT-012.md)
- [ACTOR-008](../actors/ACTOR-008-QUALITY-REVIEWER-IN-EVALUATION.md)
- [ENT-009](../entities/ENT-009-ACCEPTANCE-REPORT-IN-EVALUATION.md)
- [EVT-015](../events/EVT-015-MVP-READINESS-EVALUATED-IN-EVALUATION.md)
- [UC-015](../use-cases/UC-015-ACTOR-008-EVT-015-ENT-009-READINESS-DECIDED-IN-EVALUATION.md)

## Уровни проверки

| Уровень | Среда | Обязательное покрытие | Gate |
|---|---|---|---|
| Static quality | PHP source | Pint, static analysis, syntax, dependency/secret scan | Любая ошибка блокирует CI |
| Unit | Без HTTP и внешних сервисов | JWT, чистые правила и преобразования | Все тесты проходят |
| Feature | Laravel + SQLite `:memory:` | REST, MCP, Blade, middleware, console, persistence, позитивные/негативные ветви | Все тесты проходят |
| UC acceptance | Laravel + изолированная БД | UC-001–UC-016, роли, состояния, идемпотентность, privacy | Каждый UC имеет статус и evidence |
| Contract/parity | REST + MCP adapters | HTTP/JSON-RPC схемы, коды ошибок, семь MCP tools, одинаковый результат общей операции | Нет расхождений |
| Integration | Одноразовые MariaDB + Redis + Nginx | Миграции, constraints/locks, Redis, HTTPS, Streamable HTTP и legacy SSE | Все зависимости проверены |
| Concurrency | Реальная MariaDB/Redis | join, ticket allocation, call-next, cancel/call, start-service, replay | Инварианты не нарушены |
| Load | Docker-like environment | 100 Queue, 1000 active Entry/Queue, 20 RPS, p95 | p95 ≤ 500 мс, нет потери целостности |
| Deployment smoke | Чистый Docker host | build/up, migration, seeder, health, REST, MCP, board, restart | Контур healthy и доступен |
| Security/privacy | CI + Docker | authn/authz, ownership, redaction, secret/dependency scan, public allowlist | Нет critical/high blocker |
| UI | Desktop и mobile browser | FIFO, статусы, empty state, auto-refresh, responsive layout, basic accessibility | Нет функциональных/privacy дефектов |

## Матрица UC acceptance

Каждый UC-001–UC-016 должен иметь как минимум основной сценарий и релевантную
негативную/граничную ветвь. Для UC-013–UC-015 допускается статус `INCOMPLETE`
до реализации соответствующих observation/evaluation механизмов, но такой
статус блокирует итоговое решение `READY`.

Имена тестов содержат UC id. Evidence связывает UC → EVT/ENT/ACTOR → PT → TASK
→ test result. Повторение feature-проверки в acceptance suite допустимо, когда
оно обеспечивает эту трассировку.

## Данные и изоляция

- production endpoints, credentials и базы запрещены;
- unit/feature/acceptance используют SQLite `:memory:` и `RefreshDatabase`;
- различия SQL, блокировок и конкурентности проверяются отдельно на MariaDB;
- Redis, очереди и MCP sessions очищаются между integration runs;
- fixtures детерминированы и не содержат реальных персональных данных;
- тест не зависит от порядка выполнения других тестов.

## Результаты и статусы

Допустимые статусы: `PASS`, `FAIL`, `INCOMPLETE`, `SKIPPED`. Только `PASS`
считается прохождением. Отчёт `test_results/YYYY-MM-DD_tests.md` содержит:

1. release/commit id и время;
2. версии PHP/Laravel/PHPUnit, БД, Redis и контейнеров;
3. точные команды и параметры окружения без секретов;
4. количество passed/failed/incomplete/skipped tests и assertions;
5. матрицу UC и PRD criteria;
6. concurrency/load метрики и параметры профиля;
7. дефекты, остаточные риски и ссылки на машинные CI artifacts.

## Критерии приёмки

1. Все уровни из таблицы имеют воспроизводимую команду и владельца.
2. Быстрый набор подходит для локального запуска и pull request CI.
3. MariaDB/Redis, concurrency/load и deployment smoke запускаются отдельным
   CI job или перед release.
4. Неуспешный обязательный gate блокирует `READY`.
5. Отсутствующее evidence трактуется как `INCOMPLETE`, а не `PASS`.
6. Отчёт не утверждает процент code coverage без Xdebug/PCOV и фактического
   измерения.
