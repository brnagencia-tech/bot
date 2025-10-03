<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — BRN Pixel</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <nav class="bg-slate-900 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold">BRN Pixel</h1>
                <p class="text-xs text-slate-400">Plataforma de Tracking Multi-tenant</p>
            </div>
            <a href="/logout" class="text-sm text-slate-300 hover:text-white">Sair</a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10 space-y-8">
        <section>
            <h2 class="text-2xl font-semibold mb-4">Visão Geral</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
                    <div class="text-xs uppercase text-slate-400">Eventos (24h)</div>
                    <div class="text-3xl font-bold mt-2"><?=intval($metrics['events_last_24h'] ?? 0)?></div>
                    <div class="text-xs text-slate-500 mt-1">Eventos coletados nas últimas 24 horas.</div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
                    <div class="text-xs uppercase text-slate-400">Total de Eventos</div>
                    <div class="text-3xl font-bold mt-2"><?=intval($metrics['total'] ?? 0)?></div>
                    <div class="text-xs text-slate-500 mt-1">Eventos registrados desde o início.</div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
                    <div class="text-xs uppercase text-slate-400">Último Evento</div>
                    <div class="text-lg font-medium mt-2"><?=e($metrics['last_event_time'] ?? '—')?></div>
                    <div class="text-xs text-slate-500 mt-1">Horário do último evento recebido.</div>
                </div>
                <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
                    <div class="text-xs uppercase text-slate-400">Status Entrega</div>
                    <div class="mt-2" id="status-summary"></div>
                </div>
            </div>
        </section>

        <section class="bg-slate-900 border border-slate-800 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold">Eventos Recentes</h3>
                    <p class="text-xs text-slate-500">Filtros básicos aplicados direto via API.</p>
                </div>
                <div class="flex gap-2 text-xs text-slate-400">
                    <span>Status: <?=e($_GET['status'] ?? 'todos')?></span>
                    <span>Pixel: <?=e($_GET['pixel_public_id'] ?? 'todos')?></span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-slate-400 text-xs uppercase">
                        <tr class="border-b border-slate-800">
                            <th class="text-left py-2">ID</th>
                            <th class="text-left py-2">Evento</th>
                            <th class="text-left py-2">Pixel</th>
                            <th class="text-left py-2">Status</th>
                            <th class="text-left py-2">Hora</th>
                        </tr>
                    </thead>
                    <tbody id="events-table" class="divide-y divide-slate-800">
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">Carregando eventos...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid lg:grid-cols-2 gap-6">
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Status dos Eventos</h3>
                <canvas id="statusChart" height="200"></canvas>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Feed de Logs</h3>
                <div id="logs" class="space-y-3 text-xs text-slate-400 max-h-72 overflow-y-auto">
                    <p class="text-slate-500">Aguardando eventos...</p>
                </div>
            </div>
        </section>
    </main>

    <script>
    const API_TOKEN = '<?=e($_SESSION['api_token'] ?? '')?>';
    document.addEventListener('DOMContentLoaded', () => {
        renderStatusSummary();
        renderStatusChart();
        if (!API_TOKEN) {
            const tbody = document.getElementById('events-table');
            tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-500">Faça login novamente para visualizar eventos recentes.</td></tr>';
            document.getElementById('logs').innerHTML = '<p class="text-slate-500">Token de sessão indisponível.</p>';
            return;
        }
        fetchEvents();
    });

    function authHeaders() {
        const tenant = '<?=e($tenant->id)?>';
        return {
            'Authorization': `Bearer ${API_TOKEN}`,
            'X-BRN-Tenant': tenant
        };
    }

    async function fetchEvents() {
        try {
            const params = new URLSearchParams({ per_page: 10 });
            const res = await fetch('/api/events?' + params, { headers: authHeaders() });
            if (!res.ok) throw new Error('Falha ao carregar eventos');
            const data = await res.json();
            renderEvents(data.data || []);
            renderLogs(data.data || []);
        } catch (error) {
            console.error(error);
        }
    }

    function renderEvents(events) {
        const tbody = document.getElementById('events-table');
        if (!events.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-500">Nenhum evento encontrado.</td></tr>';
            return;
        }
        tbody.innerHTML = events.map(event => `
            <tr class="hover:bg-slate-800/40 transition">
                <td class="py-2">${event.event_idempotency}</td>
                <td class="py-2 text-slate-100">${event.event_name}</td>
                <td class="py-2 text-slate-400">${event.pixel?.pixel_id ?? '—'}</td>
                <td class="py-2"><span class="px-2 py-1 rounded bg-slate-800 text-xs uppercase">${event.status}</span></td>
                <td class="py-2 text-slate-400">${event.event_time}</td>
            </tr>
        `).join('');
    }

    function renderLogs(events) {
        const container = document.getElementById('logs');
        if (!events.length) {
            container.innerHTML = '<p class="text-slate-500">Sem registros recentes.</p>';
            return;
        }
        container.innerHTML = events.map(event => `
            <div>
                <div class="text-slate-300">${event.event_name} · ${event.event_idempotency}</div>
                <div class="text-slate-500">Pixel: ${event.pixel?.pixel_id ?? '—'} · Status: ${event.status}</div>
            </div>
        `).join('');
    }

    function renderStatusSummary() {
        const statusContainer = document.getElementById('status-summary');
        const byStatus = <?=json_encode($metrics['by_status'] ?? [], JSON_UNESCAPED_UNICODE)?>;
        const entries = Object.entries(byStatus).filter(([_, value]) => value > 0);
        if (!entries.length) {
            statusContainer.innerHTML = '<span class="text-xs text-slate-500">Nenhum evento registrado.</span>';
            return;
        }
        statusContainer.innerHTML = entries.map(([status, count]) => `
            <div class="flex items-center gap-2 text-xs text-slate-300">
                <span class="inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                <span class="uppercase">${status}</span>
                <span class="text-slate-400">${count}</span>
            </div>
        `).join('');
    }

    function renderStatusChart() {
        const ctx = document.getElementById('statusChart');
        if (!ctx) return;
        const byStatus = <?=json_encode($metrics['by_status'] ?? [], JSON_UNESCAPED_UNICODE)?>;
        const labels = Object.keys(byStatus);
        const data = Object.values(byStatus);
        if (!data.some(v => v > 0)) {
            ctx.parentElement.innerHTML += '<p class="text-xs text-slate-500 mt-3">Sem dados suficientes para o gráfico.</p>';
            return;
        }
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    label: 'Eventos',
                    data,
                    backgroundColor: ['#6366f1','#22d3ee','#f97316','#ef4444','#22c55e'],
                    borderColor: '#0f172a'
                }]
            },
            options: {
                plugins: {
                    legend: {
                        labels: { color: '#cbd5f5', font: { size: 12 } }
                    }
                }
            }
        });
    }
    </script>
</body>
</html>
