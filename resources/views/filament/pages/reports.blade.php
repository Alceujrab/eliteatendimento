<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filtro de Período --}}
        <div class="max-w-xs">
            {{ $this->form }}
        </div>

        {{-- Métricas de Conversas --}}
        @php $conv = $this->getConversationMetrics(); @endphp
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-chat-bubble-left-right class="w-5 h-5 text-primary-500" />
                    Atendimento / Conversas
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Conversas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $conv['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Resolvidas</p>
                    <p class="text-2xl font-bold text-success-600">{{ $conv['resolved'] }}</p>
                    <p class="text-xs text-gray-400">{{ $conv['resolution_rate'] }}% taxa de resolução</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tempo Médio 1ª Resposta</p>
                    <p class="text-2xl font-bold text-warning-600">
                        {{ $conv['avg_response_minutes'] ? $conv['avg_response_minutes'] . ' min' : 'N/A' }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Por Status</p>
                    <div class="space-y-1 mt-1">
                        @foreach($conv['by_status'] as $status => $count)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300 capitalize">{{ str_replace('_', ' ', $status) }}</span>
                                <span class="font-semibold">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Métricas de Leads --}}
        @php $leads = $this->getLeadMetrics(); @endphp
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-fire class="w-5 h-5 text-warning-500" />
                    Vendas / Leads
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Leads</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $leads['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ganhos / Perdidos</p>
                    <p class="text-2xl font-bold">
                        <span class="text-success-600">{{ $leads['won'] }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="text-danger-600">{{ $leads['lost'] }}</span>
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Taxa de Conversão</p>
                    <p class="text-2xl font-bold text-primary-600">{{ $leads['conversion_rate'] }}%</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Valor Total (Ganhos)</p>
                    <p class="text-2xl font-bold text-success-600">
                        R$ {{ number_format($leads['total_value'], 0, ',', '.') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ciclo Médio (dias)</p>
                    <p class="text-2xl font-bold text-info-600">
                        {{ $leads['avg_cycle_days'] ?? 'N/A' }}
                    </p>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Leads por Estágio</p>
                <div class="grid grid-cols-2 md:grid-cols-7 gap-2">
                    @php
                        $stageLabels = ['new'=>'Novo','contacted'=>'Contatado','qualified'=>'Qualificado','proposal'=>'Proposta','negotiation'=>'Negociação','won'=>'Ganho','lost'=>'Perdido'];
                        $stageColors = ['new'=>'gray','contacted'=>'info','qualified'=>'primary','proposal'=>'warning','negotiation'=>'warning','won'=>'success','lost'=>'danger'];
                    @endphp
                    @foreach($stageLabels as $key => $label)
                        <div class="text-center bg-{{ $stageColors[$key] }}-50 dark:bg-{{ $stageColors[$key] }}-950 rounded-lg p-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
                            <p class="text-lg font-bold text-{{ $stageColors[$key] }}-600">{{ $leads['by_stage'][$key] ?? 0 }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>

        {{-- Métricas de Tickets --}}
        @php $tickets = $this->getTicketMetrics(); @endphp
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-ticket class="w-5 h-5 text-info-500" />
                    Tickets de Suporte
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total de Tickets</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $tickets['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Resolvidos</p>
                    <p class="text-2xl font-bold text-success-600">{{ $tickets['resolved'] }}</p>
                    <p class="text-xs text-gray-400">{{ $tickets['resolution_rate'] }}% taxa</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Tempo Médio Resolução</p>
                    <p class="text-2xl font-bold text-warning-600">
                        {{ $tickets['avg_resolution_hours'] ? $tickets['avg_resolution_hours'] . 'h' : 'N/A' }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Vencidos (SLA)</p>
                    <p class="text-2xl font-bold {{ $tickets['overdue'] > 0 ? 'text-danger-600' : 'text-success-600' }}">
                        {{ $tickets['overdue'] }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Por Prioridade</p>
                    <div class="space-y-1 mt-1">
                        @php
                            $prioLabels = ['low'=>'Baixa','medium'=>'Média','high'=>'Alta','critical'=>'Crítica'];
                        @endphp
                        @foreach($prioLabels as $key => $label)
                            @if(isset($tickets['by_priority'][$key]))
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-300">{{ $label }}</span>
                                    <span class="font-semibold">{{ $tickets['by_priority'][$key] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Métricas de Satisfação --}}
        @php $sat = $this->getSatisfactionMetrics(); @endphp
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-face-smile class="w-5 h-5 text-success-500" />
                    Satisfação do Cliente
                </div>
            </x-slot>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">NPS Score</p>
                    <p class="text-3xl font-bold {{ ($sat['nps'] ?? -1) >= 50 ? 'text-success-600' : (($sat['nps'] ?? -1) >= 0 ? 'text-warning-600' : 'text-danger-600') }}">
                        {{ $sat['nps'] ?? 'N/A' }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">CSAT (%)</p>
                    <p class="text-3xl font-bold {{ ($sat['csat_percent'] ?? 0) >= 80 ? 'text-success-600' : (($sat['csat_percent'] ?? 0) >= 60 ? 'text-warning-600' : 'text-danger-600') }}">
                        {{ $sat['csat_percent'] ? $sat['csat_percent'] . '%' : 'N/A' }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Média CSAT</p>
                    <p class="text-3xl font-bold text-primary-600">
                        {{ $sat['avg_csat'] ? number_format($sat['avg_csat'], 1, ',', '.') . '/5' : 'N/A' }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Avaliações</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $sat['total_surveys'] }}</p>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
