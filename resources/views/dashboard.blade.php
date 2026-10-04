<x-app-layout>
    <x-slot name="header">
        <div class="ecd-header">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Executive Content Dashboard
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Ringkasan konten CMS Polda Papua Tengah yang sedang tayang, menunggu terbit, dan aktivitas publikasi terbaru.
                </p>
            </div>

            <span class="ecd-updated">
                Diperbarui {{ $generatedAt->copy()->locale('id')->translatedFormat('d M Y, H:i') }} WIT
            </span>
        </div>
    </x-slot>

    @php
        $kpiCards = [
            [
                'label' => 'Berita Tayang',
                'value' => $kpi['news_live'],
                'note' => 'Kategori editorial berstatus terbit',
                'tone' => 'article',
                'url' => route('news.index'),
            ],
            [
                'label' => 'Video Berita Tayang',
                'value' => $kpi['video_live'],
                'note' => 'Kategori Berita Video',
                'tone' => 'video',
                'url' => route('news.index', ['category' => 'video']),
            ],
            [
                'label' => 'Popup Tayang',
                'value' => $kpi['popup_live'],
                'note' => 'Aktif dalam periode tayang',
                'tone' => 'popup',
                'url' => route('announcements.index', ['type' => 'popup']),
            ],
            [
                'label' => 'Pengumuman Tayang',
                'value' => $kpi['announcement_live'],
                'note' => 'Semua tipe, sesuai periode',
                'tone' => 'announcement',
                'url' => route('announcements.index'),
            ],
        ];

        $series = [
            'article' => 'Berita Artikel',
            'video' => 'Video Berita',
            'announcement' => 'Pengumuman',
        ];

        $trendTotal = array_sum($trend['totals']);
    @endphp

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 ecd">

            {{-- KPI UTAMA --}}
            <section aria-label="Konten tayang" class="ecd-kpi-grid">
                @foreach($kpiCards as $card)
                    <a href="{{ $card['url'] }}" class="ecd-card ecd-kpi ecd-tone-{{ $card['tone'] }}">
                        <span class="ecd-kpi-label">{{ $card['label'] }}</span>
                        <span class="ecd-kpi-value">{{ number_format($card['value'], 0, ',', '.') }}</span>
                        <span class="ecd-kpi-note">{{ $card['note'] }}</span>
                    </a>
                @endforeach
            </section>

            {{-- KPI STATUS --}}
            <section aria-label="Status konten" class="ecd-status-grid">
                <div class="ecd-card ecd-status">
                    <span class="ecd-status-label">Draft</span>
                    <span class="ecd-status-value">{{ number_format($status['draft'], 0, ',', '.') }}</span>
                    <span class="ecd-status-split">
                        Berita {{ $status['draft_news'] }} &middot; Pengumuman {{ $status['draft_announcement'] }}
                    </span>
                </div>

                <div class="ecd-card ecd-status">
                    <span class="ecd-status-label">Terbit Hari Ini</span>
                    <span class="ecd-status-value">{{ number_format($status['today'], 0, ',', '.') }}</span>
                    <span class="ecd-status-split">
                        Berita {{ $status['today_news'] }} &middot; Pengumuman {{ $status['today_announcement'] }}
                    </span>
                </div>

                <div class="ecd-card ecd-status">
                    <span class="ecd-status-label">Total Konten</span>
                    <span class="ecd-status-value">{{ number_format($status['total'], 0, ',', '.') }}</span>
                    <span class="ecd-status-split">
                        Artikel {{ $status['total_article'] }} &middot; Video {{ $status['total_video'] }} &middot; Pengumuman {{ $status['total_announcement'] }}
                    </span>
                </div>
            </section>

            {{-- GRAFIK TREN --}}
            <section class="ecd-card ecd-panel" aria-labelledby="ecd-trend-title">
                <div class="ecd-panel-head">
                    <div>
                        <h3 id="ecd-trend-title" class="ecd-panel-title">Tren Publikasi Konten</h3>
                        <p class="ecd-panel-sub">
                            {{ $trend['days']->count() }} hari terakhir
                            ({{ $trend['start']->copy()->locale('id')->translatedFormat('d M') }} &ndash; {{ $generatedAt->copy()->locale('id')->translatedFormat('d M Y') }})
                            &middot; {{ $trendTotal }} konten terbit
                        </p>
                    </div>

                    <ul class="ecd-legend">
                        @foreach($series as $key => $label)
                            <li>
                                <span class="ecd-swatch ecd-bg-{{ $key }}"></span>
                                {{ $label }}
                                <strong>{{ $trend['totals'][$key] }}</strong>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="ecd-chart-scroll">
                    <div class="ecd-chart" role="img"
                         aria-label="Grafik batang tumpuk jumlah konten terbit per hari: berita artikel {{ $trend['totals']['article'] }}, video berita {{ $trend['totals']['video'] }}, pengumuman {{ $trend['totals']['announcement'] }}">
                        <div class="ecd-chart-axis">
                            <span>{{ $trend['max'] }}</span>
                            <span>0</span>
                        </div>

                        <div class="ecd-chart-bars">
                            @foreach($trend['days'] as $day)
                                @php
                                    $isToday = $day['date']->isSameDay($generatedAt);
                                    $tooltip = $day['date']->copy()->locale('id')->translatedFormat('l, d M Y')
                                        . ' - Artikel: ' . $day['article']
                                        . ', Video: ' . $day['video']
                                        . ', Pengumuman: ' . $day['announcement'];
                                @endphp

                                <div class="ecd-col" title="{{ $tooltip }}">
                                    <span class="ecd-col-total">{{ $day['total'] ?: '' }}</span>

                                    <div class="ecd-col-track">
                                        @foreach(['announcement', 'video', 'article'] as $key)
                                            @if($day[$key] > 0)
                                                <span class="ecd-seg ecd-bg-{{ $key }}"
                                                      style="height: {{ round($day[$key] / $trend['max'] * 100, 2) }}%"></span>
                                            @endif
                                        @endforeach
                                    </div>

                                    <span class="ecd-col-label {{ $isToday ? 'ecd-col-today' : '' }}">
                                        {{ $isToday ? 'Hari ini' : $day['date']->copy()->locale('id')->translatedFormat('d/m') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($trendTotal === 0)
                    <p class="ecd-empty">Belum ada konten yang terbit dalam periode ini.</p>
                @endif
            </section>

            <div class="ecd-bottom-grid">

                {{-- AKTIVITAS TERBARU --}}
                <section class="ecd-card ecd-panel" aria-labelledby="ecd-activity-title">
                    <div class="ecd-panel-head">
                        <div>
                            <h3 id="ecd-activity-title" class="ecd-panel-title">Aktivitas Terbaru</h3>
                            <p class="ecd-panel-sub">Konten terakhir yang dipublikasikan</p>
                        </div>
                    </div>

                    @if($recentActivities->isEmpty())
                        <p class="ecd-empty">Belum ada konten yang dipublikasikan.</p>
                    @else
                        <div class="ecd-table-scroll">
                            <table class="ecd-table">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Tipe Konten</th>
                                        <th>Kategori / Tipe</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivities as $item)
                                        <tr>
                                            <td class="ecd-cell-title">
                                                <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                                            </td>
                                            <td>
                                                <span class="ecd-kind">
                                                    <span class="ecd-swatch ecd-bg-{{ $item['kind'] }}"></span>
                                                    {{ $item['kind_label'] }}
                                                </span>
                                            </td>
                                            <td class="ecd-muted">{{ $item['detail'] }}</td>
                                            <td class="ecd-muted ecd-nowrap">
                                                {{ $item['date']?->copy()->locale('id')->translatedFormat('d M Y, H:i') ?? '-' }}
                                            </td>
                                            <td>
                                                <span class="ecd-badge ecd-badge-{{ $item['status'] }}">
                                                    {{ $item['status_label'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>

                {{-- MENU CEPAT --}}
                <section class="ecd-card ecd-panel" aria-labelledby="ecd-quick-title">
                    <div class="ecd-panel-head">
                        <div>
                            <h3 id="ecd-quick-title" class="ecd-panel-title">Menu Cepat</h3>
                            <p class="ecd-panel-sub">Halo, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>
                        </div>
                    </div>

                    <div class="ecd-quick">
                        <a href="{{ route('news.create') }}" class="ecd-quick-primary">+ Tulis Berita</a>
                        <a href="{{ route('news.create', ['category' => 'video']) }}">+ Tambah Video Berita</a>
                        <a href="{{ route('announcements.create', ['type' => 'popup']) }}">+ Buat Popup</a>
                        <a href="{{ route('news.index') }}">Kelola Berita</a>
                        <a href="{{ route('announcements.index') }}">Kelola Pengumuman</a>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <style>
        .ecd {
            --ecd-article: #2563eb;
            --ecd-video: #7c3aed;
            --ecd-announcement: #d97706;
            --ecd-popup: #0d9488;
            --ecd-border: #e5e7eb;
            --ecd-muted: #6b7280;
            --ecd-ink: #111827;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .ecd-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: .75rem;
        }

        .ecd-updated {
            font-size: .75rem;
            color: #6b7280;
            white-space: nowrap;
        }

        .ecd-card {
            background: #fff;
            border-radius: .75rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .05);
            border: 1px solid var(--ecd-border);
        }

        /* KPI utama */
        .ecd-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .ecd-kpi {
            display: flex;
            flex-direction: column;
            gap: .25rem;
            padding: 1.25rem;
            border-top: 4px solid var(--ecd-tone);
            transition: box-shadow .15s ease, transform .15s ease;
        }

        .ecd-kpi:hover {
            box-shadow: 0 6px 16px rgba(17, 24, 39, .08);
            transform: translateY(-1px);
        }

        .ecd-tone-article { --ecd-tone: var(--ecd-article); }
        .ecd-tone-video { --ecd-tone: var(--ecd-video); }
        .ecd-tone-popup { --ecd-tone: var(--ecd-popup); }
        .ecd-tone-announcement { --ecd-tone: var(--ecd-announcement); }

        .ecd-kpi-label {
            font-size: .875rem;
            font-weight: 600;
            color: #374151;
        }

        .ecd-kpi-value {
            font-size: 2.25rem;
            line-height: 1.1;
            font-weight: 700;
            color: var(--ecd-ink);
            font-variant-numeric: tabular-nums;
        }

        .ecd-kpi-note {
            font-size: .75rem;
            color: var(--ecd-muted);
        }

        /* KPI status */
        .ecd-status-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .ecd-status {
            display: grid;
            grid-template-columns: auto 1fr;
            grid-template-areas: "label value" "split split";
            align-items: center;
            gap: .25rem .75rem;
            padding: .875rem 1.25rem;
        }

        .ecd-status-label {
            grid-area: label;
            font-size: .8125rem;
            font-weight: 600;
            color: #4b5563;
            text-transform: uppercase;
            letter-spacing: .03em;
        }

        .ecd-status-value {
            grid-area: value;
            justify-self: end;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--ecd-ink);
            font-variant-numeric: tabular-nums;
        }

        .ecd-status-split {
            grid-area: split;
            font-size: .75rem;
            color: var(--ecd-muted);
        }

        /* Panel */
        .ecd-panel {
            padding: 1.25rem;
            min-width: 0;
        }

        .ecd-panel-head {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .ecd-panel-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ecd-ink);
        }

        .ecd-panel-sub {
            margin-top: .125rem;
            font-size: .8125rem;
            color: var(--ecd-muted);
        }

        .ecd-empty {
            margin-top: .75rem;
            font-size: .875rem;
            color: var(--ecd-muted);
        }

        /* Legend dan warna seri */
        .ecd-legend {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem 1rem;
            font-size: .8125rem;
            color: #374151;
        }

        .ecd-legend li {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
        }

        .ecd-swatch {
            display: inline-block;
            width: .625rem;
            height: .625rem;
            border-radius: 2px;
            flex: none;
        }

        .ecd-bg-article { background: var(--ecd-article); }
        .ecd-bg-video { background: var(--ecd-video); }
        .ecd-bg-announcement { background: var(--ecd-announcement); }

        /* Grafik batang tumpuk */
        .ecd-chart-scroll {
            overflow-x: auto;
        }

        .ecd-chart {
            display: flex;
            gap: .5rem;
            min-width: 560px;
        }

        .ecd-chart-axis {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 200px;
            margin-top: 1.25rem;
            font-size: .6875rem;
            color: #9ca3af;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .ecd-chart-bars {
            flex: 1;
            display: grid;
            grid-auto-flow: column;
            grid-auto-columns: minmax(0, 1fr);
            gap: .375rem;
        }

        .ecd-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: .25rem;
        }

        .ecd-col-total {
            height: 1rem;
            font-size: .6875rem;
            font-weight: 600;
            color: #374151;
            font-variant-numeric: tabular-nums;
        }

        .ecd-col-track {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            gap: 2px;
            width: 100%;
            max-width: 2.25rem;
            height: 200px;
            border-bottom: 1px solid var(--ecd-border);
            background: repeating-linear-gradient(to top, transparent 0, transparent 49.5px, #f3f4f6 49.5px, #f3f4f6 50px);
        }

        .ecd-seg {
            display: block;
            width: 100%;
            min-height: 3px;
        }

        .ecd-seg:first-child {
            border-radius: 4px 4px 0 0;
        }

        .ecd-col-label {
            font-size: .6875rem;
            color: var(--ecd-muted);
            white-space: nowrap;
        }

        .ecd-col-today {
            font-weight: 700;
            color: var(--ecd-ink);
        }

        /* Bagian bawah */
        .ecd-bottom-grid {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            gap: 1.25rem;
            align-items: start;
        }

        .ecd-table-scroll {
            overflow-x: auto;
            margin: 0 -1.25rem -1.25rem;
        }

        .ecd-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .875rem;
        }

        .ecd-table th {
            padding: .625rem 1.25rem;
            background: #f9fafb;
            text-align: left;
            font-size: .6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--ecd-muted);
            white-space: nowrap;
        }

        .ecd-table td {
            padding: .75rem 1.25rem;
            border-top: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .ecd-cell-title {
            min-width: 14rem;
            font-weight: 600;
            color: var(--ecd-ink);
        }

        .ecd-cell-title a:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .ecd-muted {
            color: var(--ecd-muted);
        }

        .ecd-nowrap,
        .ecd-kind {
            white-space: nowrap;
        }

        .ecd-kind {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            color: #374151;
        }

        .ecd-badge {
            display: inline-flex;
            align-items: center;
            padding: .125rem .625rem;
            border-radius: 9999px;
            font-size: .75rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .ecd-badge-live {
            background: #dcfce7;
            color: #166534;
        }

        .ecd-badge-ended {
            background: #f3f4f6;
            color: #4b5563;
        }

        /* Menu cepat */
        .ecd-quick {
            display: grid;
            gap: .5rem;
        }

        .ecd-quick a {
            display: block;
            padding: .75rem 1rem;
            border-radius: .5rem;
            background: #f3f4f6;
            font-size: .875rem;
            font-weight: 600;
            color: #1f2937;
            transition: background-color .15s ease;
        }

        .ecd-quick a:hover {
            background: #e5e7eb;
        }

        .ecd-quick a.ecd-quick-primary {
            background: #2563eb;
            color: #fff;
        }

        .ecd-quick a.ecd-quick-primary:hover {
            background: #1d4ed8;
        }

        /* Tablet */
        @media (max-width: 1023px) {
            .ecd-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ecd-bottom-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .ecd-quick {
                grid-template-columns: repeat(auto-fit, minmax(12rem, 1fr));
            }
        }

        /* Mobile */
        @media (max-width: 639px) {
            .ecd-kpi-grid {
                gap: .75rem;
            }

            .ecd-kpi {
                padding: 1rem;
            }

            .ecd-kpi-value {
                font-size: 1.875rem;
            }

            .ecd-status-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: .75rem;
            }

            .ecd-panel {
                padding: 1rem;
            }

            .ecd-table-scroll {
                margin: 0 -1rem -1rem;
            }
        }
    </style>
</x-app-layout>
