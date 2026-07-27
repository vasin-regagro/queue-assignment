<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="15">
    <title>Электронная очередь</title>
    <style>
        :root {
            color-scheme: light;
            --ink: #15283b;
            --muted: #66778a;
            --line: #dbe4ec;
            --surface: #ffffff;
            --canvas: #f3f7fa;
            --blue: #1769aa;
            --blue-soft: #e9f3fb;
            --green: #17865d;
            --green-soft: #e8f7f0;
            --amber: #a96500;
            --amber-soft: #fff4da;
            --violet: #6b4bb6;
            --violet-soft: #f0ebfb;
            --closed: #687582;
            --closed-soft: #eef1f4;
            --shadow: 0 14px 40px rgba(37, 61, 83, .08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-width: 320px;
            color: var(--ink);
            background:
                radial-gradient(circle at 8% 0%, rgba(36, 132, 194, .12), transparent 32rem),
                var(--canvas);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .page {
            width: min(1440px, calc(100% - 32px));
            margin: 0 auto;
            padding: 44px 0 56px;
        }

        .hero {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 28px;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--blue);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(32px, 5vw, 56px);
            line-height: .98;
            letter-spacing: -.045em;
        }

        .subtitle {
            max-width: 660px;
            margin: 14px 0 0;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.55;
        }

        .updated {
            flex: 0 0 auto;
            padding: 11px 14px;
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--muted);
            background: rgba(255, 255, 255, .72);
            font-size: 13px;
            white-space: nowrap;
            backdrop-filter: blur(10px);
        }

        .board {
            display: grid;
            gap: 20px;
        }

        .queue {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .queue__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px 22px;
            border-bottom: 1px solid var(--line);
        }

        .queue__title {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .queue__title h2 {
            overflow: hidden;
            margin: 0;
            font-size: 21px;
            letter-spacing: -.02em;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .status,
        .count {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .status--open {
            color: var(--green);
            background: var(--green-soft);
        }

        .status--closed {
            color: var(--closed);
            background: var(--closed-soft);
        }

        .status--waiting {
            color: var(--amber);
            background: var(--amber-soft);
        }

        .status--called {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .status--serving {
            color: var(--violet);
            background: var(--violet-soft);
        }

        .count {
            color: var(--blue);
            background: var(--blue-soft);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 15px 22px;
            text-align: left;
            white-space: nowrap;
        }

        th {
            color: var(--muted);
            background: #fbfcfd;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        td {
            border-top: 1px solid #edf1f4;
            font-size: 14px;
        }

        .ticket {
            color: var(--blue);
            font-size: 16px;
            font-weight: 850;
        }

        .person {
            font-weight: 720;
        }

        .time {
            color: var(--muted);
            font-variant-numeric: tabular-nums;
        }

        .empty {
            padding: 40px 22px;
            color: var(--muted);
            text-align: center;
        }

        .empty strong {
            display: block;
            margin-bottom: 6px;
            color: var(--ink);
            font-size: 17px;
        }

        .empty-board {
            padding: 72px 24px;
            border: 1px dashed #bdcad5;
            border-radius: 20px;
            text-align: center;
            background: rgba(255, 255, 255, .58);
        }

        .empty-board h2 {
            margin: 0 0 8px;
        }

        .empty-board p {
            margin: 0;
            color: var(--muted);
        }

        footer {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            margin-top: 22px;
            color: var(--muted);
            font-size: 12px;
        }

        @media (max-width: 720px) {
            .page {
                width: min(100% - 20px, 1440px);
                padding-top: 28px;
            }

            .hero {
                align-items: flex-start;
                flex-direction: column;
            }

            .queue__header {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .queue__title {
                width: 100%;
            }

            th,
            td {
                padding-right: 16px;
                padding-left: 16px;
            }

            footer {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
@php
    $queueStatusLabels = ['OPEN' => 'Открыта', 'CLOSED' => 'Закрыта'];
    $entryStatusLabels = ['WAITING' => 'Ожидает', 'CALLED' => 'Вызван', 'SERVING' => 'Обслуживается'];
@endphp
<main class="page">
    <header class="hero">
        <div>
            <p class="eyebrow">Текущее состояние</p>
            <h1>Электронная очередь</h1>
            <p class="subtitle">
                Все очереди и активные участники. Страница обновляется автоматически
                каждые 15 секунд.
            </p>
        </div>
        <div class="updated">
            Обновлено {{ $generatedAt->format('d.m.Y, H:i:s') }}
        </div>
    </header>

    @if (count($queues) === 0)
        <section class="empty-board">
            <h2>Очередей пока нет</h2>
            <p>Они появятся здесь сразу после создания.</p>
        </section>
    @else
        <section class="board" aria-label="Список очередей">
            @foreach ($queues as $queue)
                <article class="queue">
                    <header class="queue__header">
                        <div class="queue__title">
                            <h2>{{ $queue['name'] }}</h2>
                            <span class="status status--{{ strtolower($queue['status']) }}">
                                {{ $queueStatusLabels[$queue['status']] }}
                            </span>
                        </div>
                        <span class="count">
                            Активных: {{ $queue['activeCount'] }}
                        </span>
                    </header>

                    @if ($queue['activeCount'] === 0)
                        <div class="empty">
                            <strong>В этой очереди пока никого нет</strong>
                            Активные участники появятся здесь автоматически.
                        </div>
                    @else
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th scope="col">Талон</th>
                                    <th scope="col">Участник</th>
                                    <th scope="col">Статус</th>
                                    <th scope="col">В очереди с</th>
                                    <th scope="col">Вызван</th>
                                    <th scope="col">Обслуживание</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($queue['entries'] as $entry)
                                    <tr>
                                        <td class="ticket">№ {{ $entry['ticketNumber'] }}</td>
                                        <td class="person">{{ $entry['displayName'] }}</td>
                                        <td>
                                            <span class="status status--{{ strtolower($entry['status']) }}">
                                                {{ $entryStatusLabels[$entry['status']] }}
                                            </span>
                                        </td>
                                        <td class="time">{{ $entry['joinedAt']->format('d.m.Y H:i') }}</td>
                                        <td class="time">{{ $entry['calledAt']?->format('H:i') ?? '—' }}</td>
                                        <td class="time">{{ $entry['serviceStartedAt']?->format('H:i') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    <footer>
        <span>Отображаются только активные записи.</span>
        <span>Персональные контакты и история обслуживания не публикуются.</span>
    </footer>
</main>
</body>
</html>
