<x-filament-panels::page>
    <div class="space-y-6">

        {{-- ╔══════════════════════════════════════════════════════════╗ --}}
        {{-- ║  EVOLUTION API (WhatsApp)                                ║ --}}
        {{-- ╚══════════════════════════════════════════════════════════╝ --}}
        <x-filament::section icon="heroicon-o-chat-bubble-left-right" collapsible>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full" style="background-color: #25D366">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </span>
                    <span>Evolution API — WhatsApp</span>
                </div>
            </x-slot>

            <x-slot name="description">
                Conecte seu servidor Evolution API para gerenciar múltiplas instâncias de WhatsApp.
            </x-slot>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            URL do Servidor <span class="text-danger-600">*</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="url"
                                wire:model="evo_base_url"
                                placeholder="https://evolution.seuservidor.com"
                            />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500">URL base da sua Evolution API no VPS</p>
                        @error('evo_base_url') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            API Key <span class="text-danger-600">*</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model="evo_api_key"
                                placeholder="Sua chave de API da Evolution"
                            />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500">Chave de autenticação do Evolution</p>
                        @error('evo_api_key') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            Webhook URL
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="url"
                                wire:model="evo_webhook_url"
                                placeholder="{{ url('/api/webhooks/evolution') }}"
                            />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500">URL que a Evolution usará para enviar eventos (deixe vazio para usar o padrão)</p>
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            Webhook Secret
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model="evo_webhook_secret"
                                placeholder="Opcional"
                            />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500">Secret para validar webhooks recebidos</p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-filament::input.checkbox wire:model="evo_is_active" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Integração ativa</span>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-filament::button wire:click="saveEvolution" icon="heroicon-o-check">
                        Salvar Evolution
                    </x-filament::button>
                    <x-filament::button wire:click="testEvolution" color="gray" icon="heroicon-o-signal" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="testEvolution">Testar Conexão</span>
                        <span wire:loading wire:target="testEvolution">Testando...</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        {{-- ╔══════════════════════════════════════════════════════════╗ --}}
        {{-- ║  META PLATFORM (Facebook Messenger + Instagram)          ║ --}}
        {{-- ╚══════════════════════════════════════════════════════════╝ --}}
        <x-filament::section icon="heroicon-o-globe-alt" collapsible>
            <x-slot name="heading">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full" style="background-color: #1877F2">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </span>
                    <span>Meta Platform — Facebook Messenger & Instagram</span>
                </div>
            </x-slot>

            <x-slot name="description">
                Configure as credenciais do Meta Developer Portal para receber mensagens do Facebook Messenger e Instagram Direct.
            </x-slot>

            <div class="space-y-4">
                {{-- Webhook info box --}}
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:bg-blue-950/30 dark:border-blue-800">
                    <div class="flex gap-3">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"/>
                        <div class="text-sm text-blue-800 dark:text-blue-200">
                            <p class="font-medium mb-1">URL do Webhook para configurar no Meta Developer Portal:</p>
                            <code class="bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded text-xs font-mono">
                                {{ url('/api/webhooks/meta') }}
                            </code>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            App ID <span class="text-danger-600">*</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                wire:model="meta_app_id"
                                placeholder="ID do App Meta"
                            />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500">
                            <a href="https://developers.facebook.com/apps/" target="_blank" class="text-primary-600 hover:underline">
                                → Obter no Meta Developer Portal
                            </a>
                        </p>
                        @error('meta_app_id') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            App Secret <span class="text-danger-600">*</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="password"
                                wire:model="meta_app_secret"
                                placeholder="Secret do App Meta"
                            />
                        </x-filament::input.wrapper>
                        @error('meta_app_secret') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                            Verify Token <span class="text-danger-600">*</span>
                        </label>
                        <x-filament::input.wrapper>
                            <x-filament::input
                                type="text"
                                wire:model="meta_verify_token"
                                placeholder="Token de verificação do webhook"
                            />
                        </x-filament::input.wrapper>
                        <p class="mt-1 text-xs text-gray-500">Mesmo token que você informou no Meta Developer Portal ao configurar o webhook</p>
                        @error('meta_verify_token') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <x-filament::input.checkbox wire:model="meta_is_active" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Integração ativa</span>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-filament::button wire:click="saveMeta" icon="heroicon-o-check">
                        Salvar Meta
                    </x-filament::button>
                </div>

                {{-- Setup guide --}}
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:bg-gray-900 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
                    <p class="font-medium text-gray-800 dark:text-gray-200 mb-2">📋 Passo a passo para configurar:</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Crie um App no <a href="https://developers.facebook.com/apps/" target="_blank" class="text-primary-600 hover:underline">Meta Developer Portal</a></li>
                        <li>Adicione os produtos <strong>Messenger</strong> e <strong>Instagram</strong> ao app</li>
                        <li>Copie o <strong>App ID</strong> e <strong>App Secret</strong> e cole acima</li>
                        <li>Em Webhooks, configure a URL: <code class="bg-gray-200 dark:bg-gray-800 px-1 rounded text-xs">{{ url('/api/webhooks/meta') }}</code></li>
                        <li>Use o <strong>Verify Token</strong> acima ao configurar o webhook</li>
                        <li>Assine os eventos: <code class="text-xs">messages, messaging_postbacks, message_deliveries, message_reads</code></li>
                        <li>Depois, crie um <strong>Canal</strong> do tipo "Facebook Messenger" ou "Instagram" e informe o Page Access Token nas credenciais do canal</li>
                    </ol>
                </div>
            </div>
        </x-filament::section>

        {{-- ╔══════════════════════════════════════════════════════════╗ --}}
        {{-- ║  CANAIS CONFIGURADOS                                     ║ --}}
        {{-- ╚══════════════════════════════════════════════════════════╝ --}}
        <x-filament::section icon="heroicon-o-signal" collapsible collapsed>
            <x-slot name="heading">Canais Configurados</x-slot>
            <x-slot name="description">Canais de atendimento ativos nesta empresa. Para criar novos canais, use o menu Canais.</x-slot>

            @php
                $channels = \App\Models\Channel::where('tenant_id', filament()->getTenant()->id)->get();
            @endphp

            @if($channels->isEmpty())
                <div class="text-center py-6 text-gray-500">
                    <x-heroicon-o-signal class="w-10 h-10 mx-auto mb-2 text-gray-300"/>
                    <p>Nenhum canal configurado ainda.</p>
                    <a href="{{ \App\Filament\Resources\ChannelResource::getUrl('create') }}" class="text-primary-600 hover:underline text-sm">
                        + Criar primeiro canal
                    </a>
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($channels as $channel)
                        <div class="flex items-center justify-between py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white text-xs font-bold"
                                      style="background-color: {{ $channel->color }}">
                                    {{ strtoupper(substr($channel->name, 0, 2)) }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $channel->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $channel->identifier ?? 'Sem identificador' }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($channel->is_active)
                                    <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-400/10 dark:text-success-400">Ativo</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-400/10 dark:text-gray-400">Inativo</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-filament::section>

        {{-- ╔══════════════════════════════════════════════════════════╗ --}}
        {{-- ║  FEED DE ESTOQUE (XML)                                   ║ --}}
        {{-- ╚══════════════════════════════════════════════════════════╝ --}}
        <x-filament::section icon="heroicon-o-arrow-path" collapsible>
            <x-slot name="heading">Feed XML de Estoque</x-slot>
            <x-slot name="description">
                Sincroniza automaticamente o estoque para o catálogo a cada 15 minutos e permite sincronização manual no menu de Veículos.
            </x-slot>

            <div class="space-y-4">
                <div>
                    <label class="fi-fo-field-wrp-label text-sm font-medium text-gray-950 dark:text-white">
                        URL do Feed XML <span class="text-danger-600">*</span>
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="url"
                            wire:model="vehicle_feed_url"
                            placeholder="https://app.revendamais.com.br/.../companyFeed/...xml"
                        />
                    </x-filament::input.wrapper>
                    <p class="mt-1 text-xs text-gray-500">Use o link XML do sistema de estoque da empresa (Revenda Mais ou similar).</p>
                    @error('vehicle_feed_url') <p class="mt-1 text-xs text-danger-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <x-filament::input.checkbox wire:model="vehicle_feed_is_active" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Sincronização automática ativa (a cada 15 minutos)</span>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-filament::button wire:click="saveVehicleFeed" icon="heroicon-o-check">
                        Salvar Feed
                    </x-filament::button>

                    <x-filament::button wire:click="testVehicleFeed" color="gray" icon="heroicon-o-signal" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="testVehicleFeed">Testar Feed</span>
                        <span wire:loading wire:target="testVehicleFeed">Testando...</span>
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
