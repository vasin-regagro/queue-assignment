# Queue Assignment application

Laravel 12 backend электронной очереди. Запускаемый Docker-контур и основная
инструкция находятся в [`../README.md`](../README.md).

## Архитектурные границы

- `app/Services/JwtService.php` — выпуск и проверка HS256 JWT;
- `app/Services/AuthorizationService.php` — роли, владение и назначения;
- `app/Services/IdempotencyService.php` — общий replay/conflict механизм;
- `app/Services/QueueService.php` — единая бизнес-логика REST и MCP;
- `app/Http/Controllers` — REST/MCP adapters;
- `routes/console.php` — retention scheduler;
- `docker/` — PHP-FPM и Nginx/TLS.

## REST endpoints

### Без JWT

| Method | Path | Назначение |
|---|---|---|
| POST | `/api/auth/register` | Регистрация |
| POST | `/api/auth/login` | Вход |
| POST | `/api/auth/guest` | Гостевой JWT на 24 часа |
| GET | `/api/health` | DB/Redis readiness |
| GET | `/queues-board` | Публичное Blade-табло очередей и активных участников |

В Docker табло доступно по `https://localhost:8443/queues-board`. Для локальной
проверки без установки mkcert CA разрешён
`http://127.0.0.1:8080/queues-board`; HTTP-порт привязан только к loopback.

### С JWT

| Method | Path | Назначение |
|---|---|---|
| GET | `/api/queues` | Список очередей |
| GET | `/api/queues/{queue}` | Очередь |
| POST | `/api/queues` | Создать очередь, Admin |
| POST | `/api/queues/{queue}/open` | Открыть, Admin |
| POST | `/api/queues/{queue}/close` | Закрыть, Admin |
| POST | `/api/queues/{queue}/operators/{user}` | Назначить Operator, Admin |
| DELETE | `/api/queues/{queue}/operators/{user}` | Снять Operator, Admin |
| POST | `/api/queues/{queue}/entries` | Встать в очередь |
| GET | `/api/queues/{queue}/position` | Собственная позиция |
| POST | `/api/entries/{entry}/cancel` | Отменить `WAITING`/`CALLED` |
| POST | `/api/queues/{queue}/call-next` | Вызвать следующего, Operator |
| POST | `/api/entries/{entry}/start-service` | Начать обслуживание |
| POST | `/api/entries/{entry}/complete-service` | Завершить обслуживание |
| POST | `/api/mcp` | MCP JSON-RPC Streamable HTTP endpoint |
| GET | `/api/metrics` | Агрегированные метрики, Admin |

Изменяющие запросы требуют `Idempotency-Key`; изменяющие MCP tools —
`idempotencyKey`.

## Новое подключение Codex к MCP

Команды ниже выполняются из корня репозитория после запуска
`docker compose up -d`. Они создают отдельный долгоживущий и отзываемый
MCP access token для постоянного пользователя. Секрет показывается только при
создании.

### 1. Создать MCP-токен

```bash
MCP_TOKEN_JSON="$(
  docker compose exec -T app \
    php artisan mcp:token:create admin@example.test \
    --name=Codex \
    --machine
)"

export QUEUE_MCP_TOKEN="$(
  printf '%s' "$MCP_TOKEN_JSON" |
  php -r '$v=json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR); echo $v["token"];'
)"

export QUEUE_MCP_TOKEN_ID="$(
  printf '%s' "$MCP_TOKEN_JSON" |
  php -r '$v=json_decode(stream_get_contents(STDIN), true, 512, JSON_THROW_ON_ERROR); echo $v["id"];'
)"
```

`QUEUE_MCP_TOKEN_ID` следует сохранить отдельно: он понадобится для отзыва
токена. Значение `QUEUE_MCP_TOKEN` нельзя публиковать, отправлять в чат или
добавлять в Git.

### 2. Добавить MCP-сервер в Codex

Для локального Docker-контура:

```bash
codex mcp add queue_assignment_mcp \
  --url "http://127.0.0.1:8080/api/sse?token=${QUEUE_MCP_TOKEN}"
```

Если подключение с таким именем уже существует, сначала замените его:

```bash
codex mcp remove queue_assignment_mcp

codex mcp add queue_assignment_mcp \
  --url "http://127.0.0.1:8080/api/sse?token=${QUEUE_MCP_TOKEN}"
```

После добавления удалите секрет из текущего shell:

```bash
unset QUEUE_MCP_TOKEN MCP_TOKEN_JSON
```

`/api/sse` поддерживает legacy HTTP+SSE: клиент открывает GET-соединение и
получает отдельный `/api/sse/messages` для JSON-RPC. Этот же путь принимает
Streamable HTTP POST от актуального Codex. Основной Streamable HTTP endpoint
сервиса — `/api/mcp`.

### 3. Перезапустить и проверить Codex

Полностью перезапустите приложение Codex или IDE extension, затем откройте
новую задачу. В Codex можно проверить подключение запросом:

```text
Покажи список очередей через queue_assignment_mcp.
```

Ожидается доступ к следующим tools:

- `list_queues`;
- `join_queue`;
- `get_current_position`;
- `cancel_queue_entry`;
- `call_next_user`;
- `start_service`;
- `complete_service`.

В интерфейсе подключение добавляется через **Settings → MCP servers → Add
server**. Выберите **Streamable HTTP**, задайте имя `queue_assignment_mcp`,
укажите тот же URL с токеном, сохраните изменения и выполните **Restart**.

### Удалённое подключение

Для удалённого сервера замените локальный URL на публичный домен с сертификатом
доверенного CA:

```bash
codex mcp add queue_assignment_mcp \
  --url "https://queue.example.com/api/sse?token=${QUEUE_MCP_TOKEN}"
```

Порт `8080` локального Docker-контура привязан только к `127.0.0.1` и
недоступен с другого компьютера.

### Отозвать токен

```bash
docker compose exec app \
  php artisan mcp:token:revoke "$QUEUE_MCP_TOKEN_ID"
```

Удаление записи из Codex не отзывает серверный токен. Если подключение больше
не используется, выполните обе операции:

```bash
codex mcp remove queue_assignment_mcp
docker compose exec app php artisan mcp:token:revoke <TOKEN_ID>
```

## Инварианты

- одна активная запись пользователя в каждой очереди;
- активные записи в разных очередях разрешены;
- талон монотонен и не сбрасывается;
- FIFO по `joined_at`, затем `id`;
- отмена из `WAITING` и `CALLED`;
- одна `SERVING` запись на оператора;
- несколько операторов могут обслуживать одну очередь параллельно;
- оператор видит только талон, статус, время записи и display name;
- идемпотентность хранится 24 часа, QueueEntry — 90 дней, MCP audit — 30 дней.

## Проверки

```bash
composer test
./vendor/bin/pint --test
php artisan route:list --except-vendor
```
