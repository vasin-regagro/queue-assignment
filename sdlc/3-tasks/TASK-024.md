# TASK-024 — Реализовать публичное табло очередей

## Карточка

- **Статус:** Done
- **Приоритет:** P1
- **Зависимости:** TASK-002, TASK-008, TASK-011, TASK-012
- **Результат:** публичная read-only Blade-страница очередей

## Источники

- [PRD 0.5, FR-14 и DEC-014](../0-vibes/prd/prd.md)
- [PT-014](../1-business-tasks/planning/PT-014.md)
- [ACTOR-009](../2-specs/actors/ACTOR-009-PUBLIC-VIEWER-IN-QUEUE.md)
- [ENT-002](../2-specs/entities/ENT-002-QUEUE-IN-QUEUE.md)
- [ENT-004](../2-specs/entities/ENT-004-QUEUE-ENTRY-IN-QUEUE.md)
- [EVT-016](../2-specs/events/EVT-016-PUBLIC-QUEUE-BOARD-VIEWED-IN-QUEUE.md)
- [UC-016](../2-specs/use-cases/UC-016-ACTOR-009-EVT-016-ENT-004-BOARD-RETURNED-IN-QUEUE.md)

## Цель

Предоставить без JWT безопасное и адаптивное HTML-табло всех очередей и
активных участников.

## Объём

- публичный маршрут `GET /queues-board`;
- controller и публичная read model;
- eager loading QueueEntry + User без N+1;
- фильтрация по `WAITING`, `CALLED`, `SERVING`;
- FIFO-сортировка;
- Blade-layout с карточками очередей, статусами и пустыми состояниями;
- автоматическое обновление раз в 15 секунд;
- отсутствие изменяющих форм и закрытых полей;
- feature-тесты позитивного, пустого и privacy-сценариев;
- обновление README и датированного тестового отчёта.

## Критерии приёмки

1. Страница имеет HTTP 200 без Authorization.
2. Все Queue видны с названием и статусом.
3. Активные записи показаны по FIFO.
4. `COMPLETED` и `CANCELLED` отсутствуют.
5. Для участника видны только display name, талон, статус и времена.
6. Email, `user_id`, роли и токены отсутствуют.
7. Пустые состояния отображаются корректно.
8. Нет POST/PUT/PATCH/DELETE действий.
9. Страница адаптивна и обновляется через 15 секунд.
10. Автоматические тесты и Pint проходят.

## Definition of Done

UC-016 воспроизводим в Docker, TASK имеет тестовое доказательство, а PRD и
спецификации согласованы с реализованным публичным контрактом.

## Результат

- реализован `GET /queues-board`;
- добавлена публичная read model без N+1;
- создана адаптивная Blade-страница;
- добавлен loopback HTTP endpoint для локальной проверки без доверенного CA;
- исправлен некорректный default `APP_KEY` Docker-контура;
- добавлены privacy/FIFO/empty-state feature-тесты;
- desktop и mobile представления проверены в браузере.
