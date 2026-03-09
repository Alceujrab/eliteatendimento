<x-filament-panels::page :full-height="true">
    <style>
        .crm-board .crm-column-body {
            min-height: calc(100vh - 280px);
        }

        .crm-board .crm-card {
            cursor: grab;
        }

        .crm-board .crm-card:active {
            cursor: grabbing;
        }
    </style>

    <div
        x-data="{ boardHeight: 'auto' }"
        x-init="
            const setBoardHeight = () => {
                const rect = $el.getBoundingClientRect();
                boardHeight = (window.innerHeight - rect.top - 16) + 'px';
            };
            setBoardHeight();
            window.addEventListener('resize', setBoardHeight);
        "
        :style="{ height: boardHeight }"
        class="crm-board flex overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900"
        wire:poll.20s
    >
        <aside class="w-[320px] min-w-[300px] border-r border-gray-200 bg-gray-50 p-3 dark:border-white/10 dark:bg-gray-900">
            <div class="space-y-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900 dark:text-white">CRM Board</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Arraste os leads entre as etapas do funil.
                    </p>
                </div>

                <div class="relative">
                    <x-heroicon-m-magnifying-glass class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="searchQuery"
                        placeholder="Buscar por nome, telefone ou interesse"
                        class="w-full rounded-lg border border-gray-300 bg-white py-2 pl-9 pr-3 text-sm text-gray-800 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    />
                </div>

                <div class="grid grid-cols-2 gap-1.5 text-xs">
                    <select wire:model.live="filterSector" class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="all">Setor</option>
                        @foreach ($this->sectors as $sector)
                            <option value="{{ $sector }}">{{ $sector }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterTag" class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="all">Etiqueta</option>
                        @foreach ($this->tags as $tag)
                            <option value="{{ $tag }}">{{ $tag }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterAgent" class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="all">Atendente</option>
                        <option value="unassigned">Sem responsavel</option>
                        @foreach ($this->agents as $agent)
                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterChannel" class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="all">Canal</option>
                        @foreach ($this->channels as $channel)
                            <option value="{{ $channel }}">{{ $this->sourceLabel($channel) }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="filterPeriod" class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        <option value="all">Data</option>
                        <option value="today">Hoje</option>
                        <option value="7d">Ultimos 7 dias</option>
                        <option value="30d">Ultimos 30 dias</option>
                    </select>

                    <button
                        wire:click="toggleSortOrder"
                        class="rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        {{ $sortOrder === 'recent' ? 'Mais recentes' : 'Mais antigas' }}
                    </button>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <button
                        wire:click="clearFilters"
                        class="text-xs font-medium text-primary-600 hover:text-primary-700"
                    >
                        Limpar filtros
                    </button>

                    <a
                        href="{{ $this->getCreateLeadUrl() }}"
                        class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-2.5 py-1.5 text-xs font-medium text-white hover:bg-primary-700"
                    >
                        <x-heroicon-m-plus class="h-4 w-4" />
                        Novo lead
                    </a>
                </div>

                <div class="grid grid-cols-2 gap-1.5">
                    <div class="rounded-lg border border-gray-200 bg-white px-2 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Total</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->boardStats['total'] }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white px-2 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Em aberto</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->boardStats['open'] }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white px-2 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Ganhos</p>
                        <p class="text-sm font-semibold text-green-600">{{ $this->boardStats['won'] }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-white px-2 py-2 dark:border-gray-700 dark:bg-gray-800">
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">Perdidos</p>
                        <p class="text-sm font-semibold text-red-600">{{ $this->boardStats['lost'] }}</p>
                    </div>
                </div>

                <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-2.5 py-2 text-xs text-indigo-700 dark:border-indigo-900/50 dark:bg-indigo-900/20 dark:text-indigo-200">
                    Valor em aberto: <b>R$ {{ number_format($this->boardStats['open_value'], 0, ',', '.') }}</b>
                </div>
            </div>
        </aside>

        <main class="flex-1 overflow-x-auto bg-gray-100/60 p-3 dark:bg-gray-950/40">
            <div class="flex h-full min-w-max gap-3">
                @foreach (\App\Filament\Pages\CrmBoard::stageMap() as $stage => $meta)
                    @php
                        $columnLeads = $this->leadsByStage[$stage] ?? collect();
                        $columnValue = $columnLeads->sum(fn ($lead) => (float) ($lead->estimated_value ?? 0));
                    @endphp

                    <section class="w-[320px] rounded-xl border border-gray-200 bg-white p-2.5 dark:border-white/10 dark:bg-gray-900">
                        <header class="rounded-lg border px-2.5 py-2" style="border-color: {{ $meta['color'] }}33; background: {{ $meta['color'] }}11;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full" style="background-color: {{ $meta['color'] }}"></span>
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $meta['label'] }}</h3>
                                </div>
                                <span class="rounded-full bg-white/70 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                    {{ $columnLeads->count() }}
                                </span>
                            </div>
                            <p class="mt-1 text-[11px] text-gray-600 dark:text-gray-300">
                                R$ {{ number_format($columnValue, 0, ',', '.') }}
                            </p>
                        </header>

                        <div
                            class="crm-column-body mt-2 space-y-2 overflow-y-auto rounded-lg bg-gray-50/80 p-2 dark:bg-gray-800/40"
                            x-on:dragover.prevent
                            x-on:drop.prevent="$wire.moveLead(Number($event.dataTransfer.getData('leadId')), '{{ $stage }}')"
                        >
                            @forelse ($columnLeads as $lead)
                                <article
                                    draggable="true"
                                    x-on:dragstart="$event.dataTransfer.setData('leadId', '{{ $lead->id }}')"
                                    class="crm-card rounded-lg border border-gray-200 bg-white p-2.5 shadow-sm transition hover:shadow dark:border-white/10 dark:bg-gray-900"
                                >
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ $lead->contact->name ?? 'Sem contato' }}
                                            </p>
                                            <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ $lead->contact->phone ?? ($lead->contact->email ?? 'Sem telefone') }}
                                            </p>
                                        </div>
                                        <span class="text-[10px] text-gray-400">
                                            {{ $lead->updated_at?->diffForHumans(short: true) }}
                                        </span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @if ($lead->source)
                                            <span class="rounded px-1.5 py-0.5 text-[10px] bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $this->sourceLabel($lead->source) }}
                                            </span>
                                        @endif
                                        <span class="rounded px-1.5 py-0.5 text-[10px] text-white" style="background-color: {{ $this->temperatureColor($lead->temperature) }}">
                                            {{ $this->temperatureLabel($lead->temperature) }}
                                        </span>
                                    </div>

                                    @if ($lead->vehicle_interest)
                                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                            {{ $lead->vehicle_interest }}
                                        </p>
                                    @endif

                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="text-xs font-semibold text-green-600">
                                            {{ $lead->estimated_value ? 'R$ ' . number_format($lead->estimated_value, 0, ',', '.') : 'Sem valor' }}
                                        </span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                            {{ $lead->assignedUser?->name ?? 'Sem responsavel' }}
                                        </span>
                                    </div>

                                    @if (!empty($lead->contact?->tags))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach (collect($lead->contact->tags)->take(2) as $tag)
                                                <span class="rounded bg-primary-100 px-1.5 py-0.5 text-[10px] text-primary-700 dark:bg-primary-900/30 dark:text-primary-300">
                                                    {{ $tag }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="mt-2 border-t border-gray-100 pt-2 text-right dark:border-gray-700">
                                        <a
                                            href="{{ $this->getEditLeadUrl($lead) }}"
                                            class="text-[11px] font-medium text-primary-600 hover:text-primary-700"
                                        >
                                            Abrir lead
                                        </a>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-lg border border-dashed border-gray-300 px-2 py-8 text-center text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">
                                    Solte um lead aqui
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </main>
    </div>
</x-filament-panels::page>
