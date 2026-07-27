# Queue Assignment

MCP-сервис электронной очереди на Laravel 12. Код приложения расположен в
[`src`](src), продуктовые артефакты — в [`sdlc`](sdlc).

## Быстрый запуск

Требуются Docker с Compose plugin и `mkcert`.

Один раз установите локальный центр сертификации и создайте сертификат:

```bash
brew install mkcert
mkcert -install
mkdir -p .certs
mkcert -cert-file .certs/localhost.pem \
  -key-file .certs/localhost-key.pem \
  localhost 127.0.0.1 ::1
```

```bash
docker compose up -d --build
```

После первого запуска приложение автоматически:

1. дождётся MariaDB;
2. выполнит миграции;
3. создаст демонстрационных администратора и оператора;
4. запустит PHP-FPM и Laravel scheduler;
5. подключит доверенный локальный TLS-сертификат к Nginx.

Проверка:

```bash
curl https://localhost:8443/api/health
docker compose ps
```

HTTP на `localhost:8080` перенаправляется на HTTPS.

## Настройка

Compose имеет локальные значения по умолчанию. Для их изменения:

```bash
cp .env.example .env
```

Перед любым внешним развёртыванием обязательно замените:

- `APP_KEY`;
- `JWT_SECRET`;
- пароли MariaDB;
- демонстрационные пароли администратора и оператора;
- локальный сертификат на сертификат целевого домена.

Демонстрационные учётные записи по умолчанию:

| Роль | Email | Password |
|---|---|---|
| Administrator | `admin@example.test` | `change-me-admin` |
| Operator | `operator@example.test` | `change-me-operator` |

## Основные адреса

- REST API: `https://localhost:8443/api`
- MCP Streamable HTTP: `https://localhost:8443/api/mcp`
- Публичное табло очередей: `https://localhost:8443/queues-board` или локально
  без доверенного CA: `http://127.0.0.1:8080/queues-board`
- Health: `https://localhost:8443/api/health`

Локальный сертификат доверен после выполнения `mkcert -install`.

## Получение JWT

Постоянный пользователь:

```bash
curl https://localhost:8443/api/auth/register \
  -H 'Content-Type: application/json' \
  -d '{"name":"Alice","email":"alice@example.test","password":"password-123"}'
```

Гостевой JWT на 24 часа:

```bash
curl https://localhost:8443/api/auth/guest \
  -H 'Content-Type: application/json' \
  -d '{"name":"Guest"}'
```

Вход:

```bash
curl https://localhost:8443/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.test","password":"change-me-admin"}'
```

Защищённые запросы используют:

```text
Authorization: Bearer <accessToken>
```

Все изменяющие REST-запросы также требуют:

```text
Idempotency-Key: <unique-key>
```

## MCP

Получение списка tools:

```bash
curl https://localhost:8443/api/mcp \
  -H 'Authorization: Bearer <accessToken>' \
  -H 'Content-Type: application/json' \
  -H 'MCP-Agent-ID: example-agent' \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list"}'
```

Поддерживаются:

- `list_queues`;
- `join_queue`;
- `get_current_position`;
- `cancel_queue_entry`;
- `call_next_user`;
- `start_service`;
- `complete_service`.

Административных MCP-tools нет.

Для подключения одной URL-командой можно создать отзываемый долгоживущий
MCP-токен. Секрет показывается только один раз:

```bash
docker compose exec app php artisan mcp:token:create admin@example.test \
  --name=Codex
```

Подключение через legacy HTTP+SSE:

```bash
codex mcp add queue_assignment_mcp \
  --url 'http://127.0.0.1:8080/api/sse?token=<TOKEN>'
```

При подключении клиент открывает `GET /api/sse`, получает адрес
`POST /api/sse/messages` для отправки JSON-RPC и принимает ответы в исходном
SSE-соединении. Основной современный endpoint `POST /api/mcp` продолжает
работать через Streamable HTTP; `POST /api/sse` также сохранён как совместимый
alias.

Query string исключён из access log Nginx, однако URL с токеном сохраняется в
локальной конфигурации Codex. Plain HTTP endpoint доступен только через
loopback `127.0.0.1`; для удалённого доступа используйте
`https://<public-domain>/api/sse?token=<TOKEN>` с публично доверенным
сертификатом.

Отзыв токена:

```bash
docker compose exec app php artisan mcp:token:revoke <TOKEN_ID>
```

## Команды эксплуатации

```bash
docker compose logs -f app nginx
docker compose exec app php artisan migrate:status
docker compose exec app php artisan test
docker compose exec app php artisan queue-assignment:retention
docker compose down
```

Удаление локальных данных MariaDB/Redis:

```bash
docker compose down -v
```

Последняя команда необратимо удаляет локальные Docker volumes.

## Разработка без Docker

При наличии PHP 8.2+, Composer и SQLite:

```bash
cd src
composer install
cp .env.example .env
php artisan key:generate
```

Для локального SQLite измените `DB_CONNECTION=sqlite` и создайте
`database/database.sqlite`, затем:

```bash
php artisan migrate --seed
php artisan test
```
