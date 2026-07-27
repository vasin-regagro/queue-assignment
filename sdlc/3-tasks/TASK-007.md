# TASK-007 — Реализовать назначения операторов

## Карточка

- **Статус:** Ready
- **Приоритет:** P0
- **Зависимости:** TASK-002, TASK-004, TASK-005, TASK-006
- **Результат:** REST-управление OperatorAssignment

## Источники

- [PT-001](../1-business-tasks/planning/PT-001.md)
- [PT-008](../1-business-tasks/planning/PT-008.md)
- [ENT-003](../2-specs/entities/ENT-003-OPERATOR-ASSIGNMENT-IN-QUEUE.md)
- [EVT-003](../2-specs/events/EVT-003-OPERATOR-ASSIGNMENT-CHANGED-IN-QUEUE.md)
- [UC-003](../2-specs/use-cases/UC-003-ACTOR-003-EVT-003-ENT-003-ASSIGNMENT-CHANGED-IN-QUEUE.md)

## Цель

Позволить администратору назначать и снимать операторов в одной или нескольких
Queue.

## Объём

- REST assign/unassign;
- проверка роли Operator;
- уникальная активная пара Operator + Queue;
- список назначений для администрирования;
- идемпотентные повторы;
- немедленное применение области доступа.

## Требования реализации

1. Только Administrator и только REST.
2. Назначение не выдаёт роль автоматически.
3. Unassign сохраняет историю.
4. Несколько назначений одного Operator разрешены.
5. Снятие не меняет исторические QueueEntry.

## Критерии приёмки

1. Admin назначает Operator в несколько Queue.
2. Дубликат активной пары невозможен.
3. Unassign блокирует следующую операторскую команду.
4. MCP-инструмент назначения отсутствует.
5. Повтор с тем же ключом не создаёт дубль.

## Проверки

REST feature, constraint, policy и replay tests.

## Definition of Done

UC-003 реализован; policy TASK-004 использует активное назначение.
