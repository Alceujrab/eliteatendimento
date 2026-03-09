<x-filament-panels::page :full-height="true">
    <style>
        .inbox-ui svg {
            width: 1rem;
            height: 1rem;
            max-width: 1rem;
            max-height: 1rem;
            flex-shrink: 0;
        }

        .inbox-ui .inbox-empty-state svg {
            width: 3rem;
            height: 3rem;
            max-width: 3rem;
            max-height: 3rem;
        }

        .inbox-ui .inbox-main-placeholder svg {
            width: 12rem;
            height: 12rem;
            max-width: 12rem;
            max-height: 12rem;
        }

        .inbox-ui .inbox-status-check {
            width: 0.875rem;
            height: 0.75rem;
            max-width: 0.875rem;
            max-height: 0.75rem;
        }

        .inbox-ui .inbox-sticky-header {
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(8px);
        }
    </style>
    <div
        x-data="{
            inboxHeight: 'auto',
            shortcutsOpen: false,
            init() {
                this.setHeight();
                window.addEventListener('resize', () => this.setHeight());
                this.scrollToBottom();
                Livewire.hook('morph.updated', ({component}) => {
                    this.scrollToBottom();
                });
            },
            setHeight() {
                this.$nextTick(() => {
                    const rect = this.$el.getBoundingClientRect();
                    this.inboxHeight = (window.innerHeight - rect.top - 16) + 'px';
                });
            },
            scrollToBottom() {
                this.$nextTick(() => {
                    const el = document.getElementById('messages-container');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            },
            focusSearch() {
                this.$refs.searchInput?.focus();
                this.$refs.searchInput?.select();
            },
            applyQueueFilter(status) {
                this.$wire.setQueue(status);
            }
        }"
        x-on:keydown.window.slash.prevent="focusSearch()"
        x-on:keydown.window.alt.1.prevent="applyQueueFilter('entrada')"
        x-on:keydown.window.alt.2.prevent="applyQueueFilter('esperando')"
        x-on:keydown.window.alt.3.prevent="applyQueueFilter('finalizados')"
        x-on:message-sent.window="scrollToBottom()"
        :style="{ height: inboxHeight }"
        class="inbox-ui flex rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm"
        wire:poll.2s="refreshInbox"
    >
        {{-- LEFT SIDEBAR --}}
        <div class="w-[390px] min-w-[350px] flex border-r border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900">
            <div class="w-14 flex flex-col items-center gap-2 py-3 bg-gradient-to-b from-indigo-500 to-blue-700 text-white">
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                    <x-heroicon-m-chat-bubble-left-right class="w-5 h-5" />
                </div>
                <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center opacity-70">
                    <x-heroicon-m-chat-bubble-left-right class="w-4 h-4" />
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center opacity-60">
                    <x-heroicon-m-user-group class="w-4 h-4" />
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center opacity-60">
                    <x-heroicon-m-calendar-days class="w-4 h-4" />
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center opacity-60">
                    <x-heroicon-m-megaphone class="w-4 h-4" />
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center opacity-60 mt-auto">
                    <x-heroicon-m-cog-6-tooth class="w-4 h-4" />
                </div>
            </div>

            <div class="flex-1 min-w-0 flex flex-col bg-gray-50 dark:bg-gray-900">
                <div class="inbox-sticky-header px-3 py-2.5 border-b border-gray-200 dark:border-white/10 bg-white/95 dark:bg-gray-800/95 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-gray-800 dark:text-white">Atendimento</h2>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $this->getConversations()->count() }} conversas</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button
                                wire:click="openNewConversationModal"
                                class="inline-flex items-center gap-1 rounded-lg bg-primary-600 px-2 py-1 text-[11px] font-medium text-white hover:bg-primary-700"
                                title="Novo atendimento"
                            >
                                <x-heroicon-m-plus class="w-3 h-3" />
                                Novo
                            </button>
                            <button
                                wire:click="toggleSortOrder"
                                class="p-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                                title="{{ $sortOrder === 'recent' ? 'Mais recentes' : 'Mais antigas' }}"
                            >
                                <x-heroicon-m-arrows-up-down class="w-4 h-4" />
                            </button>
                            <button
                                @click="shortcutsOpen = !shortcutsOpen"
                                class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[11px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300"
                            >
                                <x-heroicon-m-command-line class="w-3 h-3" />
                                Atalhos
                            </button>
                        </div>
                    </div>

                    <div x-show="shortcutsOpen" x-transition class="rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-2.5 py-2 text-[11px] text-gray-600 dark:text-gray-300">
                        <div class="flex flex-wrap gap-x-3 gap-y-1">
                            <span><b>/</b> buscar</span>
                            <span><b>Alt+1</b> Entrada</span>
                            <span><b>Alt+2</b> Esperando</span>
                            <span><b>Alt+3</b> Finalizados</span>
                            <span><b>Ctrl+Enter</b> enviar</span>
                        </div>
                    </div>

                    <div class="relative">
                        <x-heroicon-m-magnifying-glass class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input
                            x-ref="searchInput"
                            type="text"
                            wire:model.live.debounce.300ms="searchQuery"
                            placeholder="Buscar por nome ou telefone"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        />
                    </div>

                    <div class="grid grid-cols-3 gap-1.5">
                        <button
                            wire:click="setQueue('entrada')"
                            class="rounded-lg px-2 py-1.5 text-left transition border {{ $filterStatus === 'entrada' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40' }}"
                        >
                            <p class="text-[10px] text-gray-500">Entrada</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->queueStats['entrada'] ?? 0 }}</p>
                        </button>
                        <button
                            wire:click="setQueue('esperando')"
                            class="rounded-lg px-2 py-1.5 text-left transition border {{ $filterStatus === 'esperando' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40' }}"
                        >
                            <p class="text-[10px] text-gray-500">Esperando</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->queueStats['esperando'] ?? 0 }}</p>
                        </button>
                        <button
                            wire:click="setQueue('finalizados')"
                            class="rounded-lg px-2 py-1.5 text-left transition border {{ $filterStatus === 'finalizados' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/40' }}"
                        >
                            <p class="text-[10px] text-gray-500">Finalizados</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $this->queueStats['finalizados'] ?? 0 }}</p>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-1.5 text-xs">
                        <select wire:model.live="filterSector" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-1.5">
                            <option value="all">Setor</option>
                            @foreach($this->sectors as $sector)
                                <option value="{{ $sector }}">{{ $sector }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterTag" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-1.5">
                            <option value="all">Etiqueta</option>
                            @foreach($this->tags as $tag)
                                <option value="{{ $tag }}">{{ $tag }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterAgent" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-1.5">
                            <option value="all">Atendente</option>
                            <option value="me">Apenas minhas</option>
                            <option value="unassigned">Sem agente</option>
                            @foreach($this->agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterChannel" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-1.5">
                            <option value="all">Canal</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="instagram">Instagram</option>
                            <option value="facebook">Facebook</option>
                        </select>
                        <select wire:model.live="filterPeriod" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-1.5">
                            <option value="all">Data</option>
                            <option value="today">Hoje</option>
                            <option value="7d">Ultimos 7 dias</option>
                            <option value="30d">Ultimos 30 dias</option>
                        </select>
                        <select wire:model.live="filterWhatsAppChannel" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 px-2 py-1.5">
                            <option value="all">Instancia WA</option>
                            @foreach($this->whatsappChannels as $channel)
                                <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-between gap-2">
                        <label class="inline-flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <input type="checkbox" wire:model.live="filterUnreadOnly" class="rounded border-gray-300 dark:border-gray-600" />
                            Somente nao lidas
                        </label>
                        <button wire:click="clearMainFilters" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Limpar filtros</button>
                    </div>

                    <div class="grid grid-cols-2 gap-1.5">
                        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 px-2 py-1.5 border border-red-100 dark:border-red-900/30">
                            <p class="text-[10px] text-red-600 dark:text-red-300">SLA 1a resposta (5 min)</p>
                            <p class="text-sm font-semibold text-red-700 dark:text-red-200">{{ $this->slaStats['first_response_overdue'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 px-2 py-1.5 border border-amber-100 dark:border-amber-900/30">
                            <p class="text-[10px] text-amber-700 dark:text-amber-300">SLA aguardando (30 min)</p>
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">{{ $this->slaStats['pending_overdue'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
            {{-- Conversation list --}}
            <div class="flex-1 overflow-y-auto">
                @forelse ($this->getConversations() as $conversation)
                    <button
                        wire:click="selectConversation({{ $conversation->id }})"
                        class="w-full flex items-start gap-2.5 px-3.5 py-2.5 text-left transition-colors border-b border-gray-100 dark:border-gray-800
                            {{ $activeConversationId === $conversation->id
                                ? 'bg-primary-50 dark:bg-primary-900/20 border-l-[3px] border-l-primary-500'
                                : 'hover:bg-gray-100 dark:hover:bg-gray-800 border-l-4 border-l-transparent' }}"
                    >
                        {{-- Avatar --}}
                        <div class="relative flex-shrink-0">
                            <img
                                src="{{ $conversation->contact->avatar_url }}"
                                alt="{{ $conversation->contact->name }}"
                                class="w-11 h-11 rounded-full object-cover"
                            />
                            {{-- Channel badge --}}
                            <span
                                class="absolute -bottom-0.5 -right-0.5 w-5 h-5 rounded-full flex items-center justify-center text-white text-[10px] font-bold ring-2 ring-white dark:ring-gray-900"
                                style="background-color: {{ $conversation->channel->color }}"
                                title="{{ $conversation->channel->name }}"
                            >
                                @switch($conversation->channel->type)
                                    @case('whatsapp_meta')
                                    @case('whatsapp_evolution')
                                        <span>W</span>
                                        @break
                                    @case('facebook')
                                        <span>F</span>
                                        @break
                                    @case('instagram')
                                        <span>I</span>
                                        @break
                                    @case('telegram')
                                        <span>T</span>
                                        @break
                                    @case('email')
                                        <span>@</span>
                                        @break
                                    @default
                                        <span>C</span>
                                @endswitch
                            </span>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-[13px] text-gray-900 dark:text-white truncate leading-5">
                                    {{ $conversation->contact->name }}
                                </span>
                                <span class="text-[10px] text-gray-500 dark:text-gray-400 whitespace-nowrap ml-2">
                                    {{ $conversation->last_message_at?->diffForHumans(short: true) ?? '' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between mt-0.5">
                                <p class="text-[12px] leading-4 text-gray-500 dark:text-gray-400 truncate max-w-[200px]">
                                    {{ $conversation->last_message_preview ?? 'Sem mensagens' }}
                                </p>
                                <div class="flex items-center gap-1.5 ml-1">
                                    @if ($conversation->unread_count > 0)
                                        <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-green-500 text-white text-[11px] font-bold">
                                            {{ $conversation->unread_count }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if (!empty($conversation->contact->tags))
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach (collect($conversation->contact->tags)->take(2) as $tag)
                                        <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-[10px] text-gray-600 dark:text-gray-300">
                                            {{ $tag }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Status + Agent --}}
                            <div class="flex items-center gap-2 mt-1">
                                @php $badge = $conversation->status_badge; @endphp
                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] leading-none font-medium rounded
                                    {{ match($badge['color']) {
                                        'blue' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'green' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'yellow' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        default => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
                                    } }}">
                                    {{ $badge['label'] }}
                                </span>
                                @if ($conversation->assignedUser)
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 truncate leading-none">
                                        {{ $conversation->assignedUser->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="inbox-empty-state flex flex-col items-center justify-center py-12 text-gray-400">
                        <x-heroicon-o-chat-bubble-left-right class="w-12 h-12 mb-3" />
                        <p class="text-sm">Nenhuma conversa encontrada</p>
                        <button
                            wire:click="openNewConversationModal"
                            class="mt-3 inline-flex items-center gap-1 rounded-lg bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-700"
                        >
                            <x-heroicon-m-plus class="w-4 h-4" />
                            Criar atendimento
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- MAIN CHAT AREA --}}
        @if ($activeConversationId && $this->activeConversation)
            @php
                $conv = $this->activeConversation;
                $msgs = $this->messages;
            @endphp
            <div class="flex-1 flex flex-col min-w-0">
                {{-- Chat header --}}
                <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800">
                    <div class="flex items-center gap-3 min-w-0">
                        <img
                            src="{{ $conv->contact->avatar_url }}"
                            alt="{{ $conv->contact->name }}"
                            class="w-9 h-9 rounded-full object-cover"
                        />
                        <div class="min-w-0">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-[13px] truncate leading-5">
                                {{ $conv->contact->name }}
                            </h3>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 leading-4">
                                {{ $conv->contact->phone ?? $conv->contact->email ?? '' }}
                                <span class="mx-1">&middot;</span>
                                <span style="color: {{ $conv->channel->color }}">{{ $conv->channel->name }}</span>
                            </p>
                            <div class="flex items-center gap-1 mt-1">
                                <span class="px-2 py-0.5 rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 text-[10px]">
                                    Atendimento
                                </span>
                                <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 text-[10px]">
                                    Geral
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1">
                        @if (!$conv->assigned_to)
                            <button
                                wire:click="assignToMe"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-primary-500 text-white hover:bg-primary-600 transition"
                                title="Assumir esta conversa"
                            >
                                <x-heroicon-m-hand-raised class="w-3.5 h-3.5" />
                                Assumir
                            </button>
                        @endif

                        @if ($conv->assigned_to)
                            <div class="relative" x-data="{ openTransfer: false }">
                                <button
                                    @click="openTransfer = !openTransfer"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600 transition"
                                    title="Transferir conversa"
                                >
                                    <x-heroicon-m-arrow-right-circle class="w-3.5 h-3.5" />
                                    Transferir
                                </button>

                                <div
                                    x-show="openTransfer"
                                    @click.outside="openTransfer = false"
                                    class="absolute right-0 mt-1 w-48 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg z-20"
                                >
                                    @foreach($this->agents as $agent)
                                        <button
                                            wire:click="transferConversation({{ $agent->id }})"
                                            @click="openTransfer = false"
                                            class="w-full text-left px-3 py-2 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200"
                                        >
                                            {{ $agent->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <button
                            wire:click="$toggle('isInternalNote')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $isInternalNote ? 'bg-yellow-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}"
                            title="Atendimento privado / nota interna"
                        >
                            <x-heroicon-m-lock-closed class="w-3.5 h-3.5" />
                            Privado
                        </button>

                        @if (in_array($conv->status, ['new', 'open', 'pending']))
                            <button
                                wire:click="resolveConversation"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-green-500 text-white hover:bg-green-600 transition"
                                title="Resolver conversa"
                            >
                                <x-heroicon-m-check-circle class="w-3.5 h-3.5" />
                                Resolver
                            </button>
                        @else
                            <button
                                wire:click="reopenConversation"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 transition"
                                title="Reabrir conversa"
                            >
                                <x-heroicon-m-arrow-path class="w-3.5 h-3.5" />
                                Reabrir
                            </button>
                        @endif

                        <button
                            wire:click="toggleContactInfo"
                            class="p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                            title="Informacoes do contato"
                        >
                            <x-heroicon-m-information-circle class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <div class="flex flex-1 overflow-hidden">
                    {{-- Messages area --}}
                    <div class="flex-1 flex flex-col min-w-0">
                        {{-- Messages scroll --}}
                        <div
                            id="messages-container"
                            class="flex-1 overflow-y-auto px-3.5 py-3 space-y-0.5 bg-[#efeae2] dark:bg-gray-950"
                            x-init="$el.scrollTop = $el.scrollHeight"
                        >
                            @php $lastDate = null; @endphp
                            @foreach ($msgs as $msg)
                                {{-- Date separator --}}
                                @if ($lastDate !== $msg->created_at->format('Y-m-d'))
                                    @php $lastDate = $msg->created_at->format('Y-m-d'); @endphp
                                    <div class="flex justify-center my-2.5">
                                        <span class="px-2.5 py-1 rounded-lg bg-white/80 dark:bg-gray-700/80 text-[11px] text-gray-600 dark:text-gray-300 shadow-sm">
                                            {{ $msg->created_at->translatedFormat('d \\d\\e F') }}
                                        </span>
                                    </div>
                                @endif

                                {{-- System message --}}
                                @if ($msg->type === 'system')
                                    <div class="flex justify-center my-2">
                                        <span class="px-3 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-xs text-blue-600 dark:text-blue-400 italic">
                                            {{ $msg->body }}
                                        </span>
                                    </div>
                                    @continue
                                @endif

                                {{-- Internal note --}}
                                @if ($msg->is_internal_note)
                                    <div class="flex justify-center my-2">
                                        <div class="max-w-[75%] px-3 py-2 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
                                            <div class="flex items-center gap-1.5 mb-1">
                                                <x-heroicon-m-lock-closed class="w-3 h-3 text-yellow-600" />
                                                <span class="text-[10px] font-semibold text-yellow-700 dark:text-yellow-400">
                                                    Nota interna - {{ $msg->user?->name ?? 'Sistema' }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-yellow-800 dark:text-yellow-200">{{ $msg->body }}</p>
                                            <span class="text-[10px] text-yellow-500 mt-1 block text-right">{{ $msg->formatted_time }}</span>
                                        </div>
                                    </div>
                                    @continue
                                @endif

                                {{-- Chat bubble --}}
                                <div class="flex {{ $msg->isOutbound() ? 'justify-end' : 'justify-start' }} mb-0.5">
                                    <div class="max-w-[72%] px-3 py-1.5 rounded-2xl shadow-sm relative
                                        {{ $msg->isOutbound()
                                            ? 'bg-[#d9fdd3] dark:bg-green-900/40 rounded-tr-sm'
                                            : 'bg-white dark:bg-gray-700 rounded-tl-sm' }}
                                    ">
                                        {{-- Sender name for inbound --}}
                                        @if ($msg->isInbound())
                                            <p class="text-[11px] font-semibold text-primary-600 dark:text-primary-400 mb-0.5 leading-4">
                                                {{ $msg->sender_name }}
                                            </p>
                                        @endif

                                        {{-- Message body --}}
                                        @if ($msg->type === 'image' && $msg->attachments)
                                            @foreach ($msg->attachments as $att)
                                                <img src="{{ $att['url'] ?? '' }}" alt="Imagem" class="max-w-full rounded-lg mb-1" />
                                            @endforeach
                                        @endif

                                        @if ($msg->type === 'audio')
                                            <div class="flex items-center gap-2 py-1">
                                                <x-heroicon-m-microphone class="w-5 h-5 text-gray-400" />
                                                <div class="h-1 flex-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                                            </div>
                                        @endif

                                        @if ($msg->type === 'document' && $msg->attachments)
                                            @foreach ($msg->attachments as $att)
                                                <div class="flex items-center gap-2 p-2 rounded-lg bg-gray-100 dark:bg-gray-600 mb-1">
                                                    <x-heroicon-m-document class="w-5 h-5 text-gray-500" />
                                                    <span class="text-xs truncate">{{ $att['filename'] ?? 'Documento' }}</span>
                                                </div>
                                            @endforeach
                                        @endif

                                        @if ($msg->body)
                                            <p class="text-[13px] text-gray-900 dark:text-gray-100 whitespace-pre-wrap break-words leading-5">{{ $msg->body }}</p>
                                        @endif

                                        {{-- Time + status --}}
                                        <div class="flex items-center justify-end gap-1 mt-1">
                                            <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $msg->formatted_time }}</span>
                                            @if ($msg->isOutbound())
                                                @switch($msg->status)
                                                    @case('read')
                                                        <svg class="inbox-status-check text-blue-500" fill="currentColor" viewBox="0 0 16 11"><path d="M11.07 0L5.97 5.1 4.5 3.63 3.07 5.1l2.9 2.9 6.53-6.53L11.07 0zM8.07 0L2.97 5.1 1.5 3.63.07 5.1l2.9 2.9L9.5 1.47 8.07 0z"/></svg>
                                                        @break
                                                    @case('delivered')
                                                        <svg class="inbox-status-check text-gray-400" fill="currentColor" viewBox="0 0 16 11"><path d="M11.07 0L5.97 5.1 4.5 3.63 3.07 5.1l2.9 2.9 6.53-6.53L11.07 0zM8.07 0L2.97 5.1 1.5 3.63.07 5.1l2.9 2.9L9.5 1.47 8.07 0z"/></svg>
                                                        @break
                                                    @case('sent')
                                                        <svg class="inbox-status-check text-gray-400" fill="currentColor" viewBox="0 0 12 11"><path d="M10.07 0L4.97 5.1 3.5 3.63 2.07 5.1l2.9 2.9 6.53-6.53L10.07 0z"/></svg>
                                                        @break
                                                    @case('failed')
                                                        <x-heroicon-m-exclamation-circle class="w-3 h-3 text-red-500" />
                                                        @break
                                                @endswitch
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if ($msgs->isEmpty())
                                <div class="inbox-empty-state flex flex-col items-center justify-center h-full text-gray-400">
                                    <x-heroicon-o-chat-bubble-left-ellipsis class="w-16 h-16 mb-3 opacity-30" />
                                    <p class="text-sm">Nenhuma mensagem ainda</p>
                                    <p class="text-xs mt-1">Envie a primeira mensagem</p>
                                </div>
                            @endif
                        </div>

                        {{-- Quick replies dropdown --}}
                        @if ($showQuickReplies)
                            <div class="border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 max-h-48 overflow-y-auto">
                                @foreach ($this->quickReplies as $reply)
                                    <button
                                        wire:click="insertQuickReply({{ $reply->id }})"
                                        class="w-full text-left px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-700 last:border-0"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $reply->title }}</span>
                                            @if ($reply->shortcut)
                                                <span class="text-xs text-gray-400 font-mono">{{ $reply->shortcut }}</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 truncate mt-0.5">{{ $reply->body }}</p>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        {{-- Message input --}}
                        <div class="border-t border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 px-4 py-2.5">
                            @if ($isInternalNote)
                                <div class="flex items-center gap-2 mb-2 px-2 py-1 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-300 text-xs">
                                    <x-heroicon-m-lock-closed class="w-3.5 h-3.5" />
                                    <span>Modo nota interna - esta mensagem nao sera enviada ao cliente</span>
                                    <button wire:click="$set('isInternalNote', false)" class="ml-auto font-bold hover:text-yellow-900">&times;</button>
                                </div>
                            @endif

                            <div class="flex items-end gap-2">
                                {{-- Action buttons --}}
                                <div class="flex items-center gap-0.5 pb-1">
                                    <button
                                        wire:click="$toggle('showQuickReplies')"
                                        class="p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition"
                                        title="Respostas rapidas"
                                    >
                                        <x-heroicon-m-bolt class="w-5 h-5" />
                                    </button>
                                    <button
                                        wire:click="$toggle('isInternalNote')"
                                        class="p-2 rounded-full transition {{ $isInternalNote ? 'text-yellow-600 bg-yellow-50 dark:bg-yellow-900/20' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                        title="Nota interna"
                                    >
                                        <x-heroicon-m-lock-closed class="w-5 h-5" />
                                    </button>
                                </div>

                                {{-- Text field --}}
                                <div class="flex-1">
                                    <textarea
                                        wire:model="messageText"
                                        x-on:keydown.enter.prevent="if (!$event.shiftKey) { $wire.sendMessage() }"
                                        x-on:keydown.ctrl.enter.prevent="$wire.sendMessage()"
                                        placeholder="{{ $isInternalNote ? 'Escreva uma nota interna...' : 'Digite uma mensagem...' }}"
                                        rows="1"
                                        class="w-full resize-none rounded-2xl border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white px-3.5 py-2.5 text-[13px] leading-5 focus:ring-2 focus:ring-primary-500 focus:border-transparent placeholder-gray-400"
                                        x-data="{ resize() { $el.style.height = '40px'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'; } }"
                                        x-on:input="resize()"
                                        x-init="resize()"
                                        style="min-height: 40px; max-height: 120px;"
                                    ></textarea>
                                </div>

                                {{-- Send button --}}
                                <button
                                    wire:click="sendMessage"
                                    @disabled(trim($messageText) === '')
                                    class="p-2.5 rounded-full transition-colors disabled:opacity-40
                                        {{ $isInternalNote
                                            ? 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                            : 'bg-primary-500 hover:bg-primary-600 text-white' }}"
                                    title="Enviar"
                                >
                                    <x-heroicon-m-paper-airplane class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT SIDEBAR --}}
                    @if ($showContactInfo)
                        <div class="w-[280px] border-l border-gray-200 dark:border-white/10 bg-white dark:bg-gray-800 overflow-y-auto">
                            <div class="p-4">
                                {{-- Close --}}
                                <div class="flex items-center justify-between mb-4">
                                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm">Informacoes do contato</h4>
                                    <button wire:click="toggleContactInfo" class="text-gray-400 hover:text-gray-600">
                                        <x-heroicon-m-x-mark class="w-5 h-5" />
                                    </button>
                                </div>

                                {{-- Avatar + Name --}}
                                <div class="flex flex-col items-center mb-6">
                                    <img
                                        src="{{ $conv->contact->avatar_url }}"
                                        alt="{{ $conv->contact->name }}"
                                        class="w-20 h-20 rounded-full object-cover mb-3"
                                    />
                                    <h5 class="font-bold text-gray-900 dark:text-white">{{ $conv->contact->name }}</h5>
                                    <p class="text-xs text-gray-500">{{ $conv->contact->source ?? 'Nao informado' }}</p>
                                </div>

                                {{-- Contact details --}}
                                <div class="space-y-3">
                                    @if ($conv->contact->phone)
                                        <div class="flex items-center gap-3 text-sm">
                                            <x-heroicon-m-phone class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                            <span class="text-gray-700 dark:text-gray-300">{{ $conv->contact->phone }}</span>
                                        </div>
                                    @endif
                                    @if ($conv->contact->email)
                                        <div class="flex items-center gap-3 text-sm">
                                            <x-heroicon-m-envelope class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                            <span class="text-gray-700 dark:text-gray-300">{{ $conv->contact->email }}</span>
                                        </div>
                                    @endif
                                    @if ($conv->contact->cpf)
                                        <div class="flex items-center gap-3 text-sm">
                                            <x-heroicon-m-identification class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                            <span class="text-gray-700 dark:text-gray-300">{{ $conv->contact->cpf }}</span>
                                        </div>
                                    @endif
                                    @if ($conv->contact->city)
                                        <div class="flex items-center gap-3 text-sm">
                                            <x-heroicon-m-map-pin class="w-4 h-4 text-gray-400 flex-shrink-0" />
                                            <span class="text-gray-700 dark:text-gray-300">{{ $conv->contact->city }}{{ $conv->contact->state ? '/' . $conv->contact->state : '' }}</span>
                                        </div>
                                    @endif
                                </div>

                                <hr class="my-4 border-gray-200 dark:border-gray-700" />

                                {{-- Conversation info --}}
                                <h5 class="font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wide mb-3">Conversa</h5>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Canal</span>
                                        <span class="font-medium" style="color: {{ $conv->channel->color }}">{{ $conv->channel->name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Status</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $conv->status_badge['label'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Prioridade</span>
                                        <span class="font-medium text-gray-900 dark:text-white capitalize">{{ $conv->priority }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Agente</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $conv->assignedUser?->name ?? 'Nenhum' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Criada em</span>
                                        <span class="text-gray-700 dark:text-gray-300">{{ $conv->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    @if ($conv->first_response_at)
                                        <div class="flex justify-between">
                                            <span class="text-gray-500">1a resposta</span>
                                            <span class="text-gray-700 dark:text-gray-300">{{ $conv->first_response_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <hr class="my-4 border-gray-200 dark:border-gray-700" />

                                {{-- Tags --}}
                                @if ($conv->contact->tags)
                                    <h5 class="font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wide mb-2">Tags</h5>
                                    <div class="flex flex-wrap gap-1 mb-4">
                                        @foreach ($conv->contact->tags as $tag)
                                            <span class="px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- CRM snapshot --}}
                                @php
                                    $leads = $conv->contact->leads()->latest()->limit(3)->get();
                                    $latestLead = $leads->first();
                                @endphp
                                <hr class="my-4 border-gray-200 dark:border-gray-700" />
                                <h5 class="font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wide mb-2">CRM / Funil</h5>
                                @if ($latestLead)
                                    <div class="p-2.5 rounded-lg border border-indigo-100 dark:border-indigo-900/40 bg-indigo-50/60 dark:bg-indigo-900/10 mb-2">
                                        <p class="text-[10px] text-indigo-600 dark:text-indigo-300 uppercase tracking-wide">Oportunidade principal</p>
                                        <p class="text-xs font-semibold text-gray-900 dark:text-white mt-1">{{ $latestLead->title }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-white dark:bg-gray-800 text-indigo-600 dark:text-indigo-300">
                                                {{ $latestLead->stage }}
                                            </span>
                                            @if ($latestLead->estimated_value)
                                                <span class="text-[10px] font-medium text-gray-700 dark:text-gray-200">R$ {{ number_format($latestLead->estimated_value, 0, ',', '.') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if ($leads->count() > 0)
                                    <div class="space-y-2">
                                        @foreach ($leads as $lead)
                                            <div class="p-2 rounded-lg bg-gray-50 dark:bg-gray-700">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-900 dark:text-white">{{ $lead->title }}</span>
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300">{{ $lead->stage }}</span>
                                                </div>
                                                @if ($lead->estimated_value)
                                                    <span class="text-[10px] text-gray-500">R$ {{ number_format($lead->estimated_value, 0, ',', '.') }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-xs text-gray-500">Sem oportunidades no CRM para este contato.</p>
                                @endif

                                {{-- Notes --}}
                                @if ($conv->contact->notes)
                                    <hr class="my-4 border-gray-200 dark:border-gray-700" />
                                    <h5 class="font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wide mb-2">Observacoes</h5>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $conv->contact->notes }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- No conversation selected --}}
            <div class="inbox-main-placeholder flex-1 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 text-gray-400">
                <div class="text-center">
                    <svg class="w-48 h-48 mx-auto mb-6 opacity-20" viewBox="0 0 200 200" fill="none">
                        <circle cx="100" cy="100" r="80" stroke="currentColor" stroke-width="2" />
                        <path d="M65 90 c0-15 13-28 28-28h14c15 0 28 13 28 28v10c0 15-13 28-28 28h-5l-15 15v-15h-8c-8 0-14-6-14-14z" fill="currentColor" opacity="0.3"/>
                        <circle cx="86" cy="95" r="3" fill="currentColor" opacity="0.5"/>
                        <circle cx="100" cy="95" r="3" fill="currentColor" opacity="0.5"/>
                        <circle cx="114" cy="95" r="3" fill="currentColor" opacity="0.5"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-500 dark:text-gray-400 mb-2">Elite Atendimento</h3>
                    <p class="text-sm text-gray-400 dark:text-gray-500 max-w-sm">
                        Selecione uma conversa para iniciar o atendimento. Use os filtros a esquerda para organizar suas conversas.
                    </p>
                    <button
                        wire:click="openNewConversationModal"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700"
                    >
                        <x-heroicon-m-plus class="w-4 h-4" />
                        Novo atendimento
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if ($showNewConversationModal)
        <div class="fixed inset-0 z-[100] flex items-center justify-center bg-black/40 px-4" wire:key="new-conversation-modal">
            <div class="w-full max-w-lg rounded-xl border border-gray-200 bg-white p-4 shadow-xl dark:border-white/10 dark:bg-gray-900">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Novo atendimento</h3>
                    <button wire:click="closeNewConversationModal" class="rounded p-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <x-heroicon-m-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Contato</label>
                        <select wire:model.live="newConversationContactId" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">Selecione um contato</option>
                            @foreach ($this->contacts as $contact)
                                <option value="{{ $contact->id }}">
                                    {{ $contact->name }}{{ $contact->phone ? ' - ' . $contact->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Canal</label>
                        <select wire:model.live="newConversationChannelId" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">Selecione um canal</option>
                            @foreach ($this->activeChannels as $channel)
                                <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Mensagem inicial (opcional)</label>
                        <textarea
                            wire:model="newConversationInitialMessage"
                            rows="3"
                            placeholder="Digite a primeira mensagem..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-2">
                    <button wire:click="closeNewConversationModal" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">
                        Cancelar
                    </button>
                    <button wire:click="createConversation" class="rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-primary-700">
                        Criar atendimento
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>

