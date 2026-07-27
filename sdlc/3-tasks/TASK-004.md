# TASK-004 — Реализовать роли и политики доступа

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-002, TASK-003
- **Результат:** единая авторизация REST и MCP

## Источники

- [PT-008](../1-business-tasks/planning/PT-008.md)
- [ACTOR-001](../2-specs/actors/ACTOR-001-USER-IN-QUEUE.md)
- [ACTOR-002](../2-specs/actors/ACTOR-002-OPERATOR-IN-SERVICE.md)
- [ACTOR-003](../2-specs/actors/ACTOR-003-ADMINISTRATOR-IN-QUEUE.md)
- [ACTOR-004](../2-specs/actors/ACTOR-004-AI-AGENT-IN-MCP.md)
- [ENT-003](../2-specs/entities/ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md)

## Цель

Создать централизованные policies/guards для ролей, владения записью и
назначения оператора.

## Объём

- роли `USER`, `OPERATOR`, `ADMINISTRATOR`;
- владение QueueEntry;
- проверка активного OperatorAssignment;
- запрет административных операций в MCP;
- field-level projection данных для оператора;
- единые unauthorized/forbidden ошибки.

## Требования реализации

1. `user_id` из payload не определяет действующего пользователя.
2. Оператор видит только талон, статус, joined time и display name.
3. Администратор не наследует операторские права автоматически.
4. Совмещение ролей поддерживается.
5. Прикладные actions вызывают policy независимо от интерфейса.

## Критерии приёмки

1. Пользователь не читает и не меняет чужую QueueEntry.
2. Оператор ограничен назначенными Queue.
3. AI Agent не расширяет права User.
4. Admin-only route недоступен через MCP.
5. Ошибки не раскрывают существование чужого ресурса сверх контракта.

## Проверки

Матрица позитивных/негативных authorization tests для всех ролей и интерфейсов.

## Definition of Done

Все прикладные задачи могут использовать единый authorization API.
