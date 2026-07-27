# TASK-005 — Реализовать идемпотентность изменяющих операций

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-002, TASK-003
- **Результат:** единичный эффект повторяемых REST/MCP-команд

## Источники

- [PT-009](../1-business-tasks/planning/PT-009.md)
- [ENT-005](../2-specs/entities/ENT-005-IDEMPOTENCY-RECORD-IN-PLATFORM.md)
- [EVT-011](../2-specs/events/EVT-011-IDEMPOTENT-REQUEST-RESOLVED-IN-PLATFORM.md)
- [UC-011](../2-specs/use-cases/UC-011-ACTOR-001-EVT-011-ENT-005-IDEMPOTENT-RESULT-RETURNED-IN-PLATFORM.md)

## Цель

Создать общий сервис разрешения `Idempotency-Key`/`idempotencyKey` для всех
изменяющих операций.

## Объём

- канонизация бизнес-параметров и fingerprint;
- область User + operation + key;
- состояния `IN_PROGRESS`, `SUCCEEDED`, `FAILED`;
- атомарное резервирование;
- replay сохранённого ответа;
- конфликт отличающихся параметров;
- TTL 24 часа.

## Требования реализации

1. Резервирование происходит до предметного эффекта.
2. Параллельный повтор не запускает второй action.
3. REST header и MCP argument приводятся к общей модели.
4. Response snapshot не содержит секретов.
5. Очистка записи не удаляет доменный результат.

## Критерии приёмки

1. Эквивалентный повтор возвращает исходный результат.
2. Отличающийся fingerprint даёт conflict.
3. `IN_PROGRESS` обрабатывается предсказуемо.
4. Через 24 часа запись доступна retention job.
5. Механизм работает одинаково для REST и MCP.

## Проверки

Unit fingerprint tests, feature replay tests и параллельные integration tests.

## Definition of Done

Сервис интегрируем во все последующие изменяющие actions без копирования логики.
