<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelResource\Pages;
use App\Models\Channel;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Actions;
use Filament\Tables\Table;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-signal';
    protected static string | \UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Canais';
    protected static ?string $modelLabel = 'Canal';
    protected static ?string $pluralModelLabel = 'Canais';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Schemas\Components\Section::make('Canal de Atendimento')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome do Canal')
                        ->placeholder('Ex: WhatsApp Principal, Instagram Loja, etc.')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\Select::make('type')
                        ->label('Tipo')
                        ->options([
                            'whatsapp_evolution' => '📱 WhatsApp (Evolution API)',
                            'facebook' => '💬 Facebook Messenger',
                            'instagram' => '📷 Instagram Direct',
                            'webchat' => '🌐 Webchat',
                            'email' => '📧 E-mail',
                            'sms' => '📩 SMS',
                        ])
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('credentials', null)),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Canal Ativo')
                        ->default(true)
                        ->columnSpanFull(),
                ]),

            // ── WhatsApp Evolution ──
            Schemas\Components\Section::make('Configuração WhatsApp (Evolution)')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->description('Informe o nome da instância da Evolution API e o número do WhatsApp.')
                ->visible(fn (Forms\Get $get): bool => $get('type') === 'whatsapp_evolution')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('credentials.instance_name')
                        ->label('Nome da Instância')
                        ->placeholder('Ex: elite-principal')
                        ->helperText('Mesmo nome usado ao criar a instância na Evolution API')
                        ->required(),
                    Forms\Components\TextInput::make('identifier')
                        ->label('Número do WhatsApp')
                        ->placeholder('5511999998888')
                        ->helperText('Número completo com DDI+DDD, sem espaços ou traços')
                        ->tel()
                        ->required(),
                ]),

            // ── Facebook Messenger ──
            Schemas\Components\Section::make('Configuração Facebook Messenger')
                ->icon('heroicon-o-chat-bubble-oval-left')
                ->description('Configure a Página do Facebook para receber mensagens via Messenger.')
                ->visible(fn (Forms\Get $get): bool => $get('type') === 'facebook')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('identifier')
                        ->label('Page ID')
                        ->placeholder('ID numérico da Página do Facebook')
                        ->helperText('Encontre em Configurações da Página → Transparência da Página')
                        ->required(),
                    Forms\Components\TextInput::make('credentials.page_access_token')
                        ->label('Page Access Token')
                        ->placeholder('Token de acesso da página')
                        ->helperText('Gere um token permanente no Meta Developer Portal')
                        ->password()
                        ->revealable()
                        ->required()
                        ->columnSpanFull(),
                ]),

            // ── Instagram Direct ──
            Schemas\Components\Section::make('Configuração Instagram Direct')
                ->icon('heroicon-o-camera')
                ->description('Configure a conta profissional do Instagram para receber DMs.')
                ->visible(fn (Forms\Get $get): bool => $get('type') === 'instagram')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('identifier')
                        ->label('Instagram Account ID')
                        ->placeholder('ID da conta profissional do Instagram')
                        ->helperText('ID numérico da conta Instagram Business/Creator')
                        ->required(),
                    Forms\Components\TextInput::make('credentials.connected_page_id')
                        ->label('Page ID Conectada')
                        ->placeholder('ID da Página do Facebook vinculada')
                        ->helperText('A Página do Facebook que está conectada a esta conta Instagram')
                        ->required(),
                    Forms\Components\TextInput::make('credentials.page_access_token')
                        ->label('Page Access Token')
                        ->placeholder('Token de acesso da página conectada')
                        ->helperText('Mesmo token da Página do Facebook vinculada ao Instagram')
                        ->password()
                        ->revealable()
                        ->required()
                        ->columnSpanFull(),
                ]),

            // ── Webchat ──
            Schemas\Components\Section::make('Configuração Webchat')
                ->icon('heroicon-o-globe-alt')
                ->description('Widget de chat para embutir no seu site.')
                ->visible(fn (Forms\Get $get): bool => $get('type') === 'webchat')
                ->schema([
                    Forms\Components\TextInput::make('identifier')
                        ->label('Domínio do Site')
                        ->placeholder('https://www.seusite.com.br')
                        ->helperText('Site onde o widget será instalado')
                        ->url(),
                    Forms\Components\ColorPicker::make('settings.widget_color')
                        ->label('Cor do Widget')
                        ->default('#1e40af'),
                    Forms\Components\TextInput::make('settings.welcome_message')
                        ->label('Mensagem de Boas-Vindas')
                        ->placeholder('Olá! Como posso ajudar?')
                        ->default('Olá! Como posso ajudar?'),
                ]),

            // ── Email ──
            Schemas\Components\Section::make('Configuração E-mail')
                ->icon('heroicon-o-envelope')
                ->description('Receba e responda e-mails diretamente pela caixa de entrada.')
                ->visible(fn (Forms\Get $get): bool => $get('type') === 'email')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('identifier')
                        ->label('Endereço de E-mail')
                        ->placeholder('atendimento@suaempresa.com')
                        ->email()
                        ->required(),
                    Forms\Components\TextInput::make('credentials.imap_host')
                        ->label('Servidor IMAP')
                        ->placeholder('imap.gmail.com'),
                    Forms\Components\TextInput::make('credentials.imap_port')
                        ->label('Porta IMAP')
                        ->placeholder('993')
                        ->numeric(),
                    Forms\Components\TextInput::make('credentials.smtp_host')
                        ->label('Servidor SMTP')
                        ->placeholder('smtp.gmail.com'),
                    Forms\Components\TextInput::make('credentials.smtp_port')
                        ->label('Porta SMTP')
                        ->placeholder('587')
                        ->numeric(),
                    Forms\Components\TextInput::make('credentials.email_password')
                        ->label('Senha / App Password')
                        ->password()
                        ->revealable(),
                ]),

            // ── SMS ──
            Schemas\Components\Section::make('Configuração SMS')
                ->icon('heroicon-o-device-phone-mobile')
                ->visible(fn (Forms\Get $get): bool => $get('type') === 'sms')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('identifier')
                        ->label('Número de Envio')
                        ->placeholder('+5511999998888')
                        ->tel(),
                    Forms\Components\TextInput::make('credentials.sms_api_key')
                        ->label('API Key do Provedor SMS')
                        ->password()
                        ->revealable(),
                    Forms\Components\Select::make('settings.sms_provider')
                        ->label('Provedor')
                        ->options([
                            'twilio' => 'Twilio',
                            'zenvia' => 'Zenvia',
                            'infobip' => 'Infobip',
                        ])
                        ->native(false),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'whatsapp_meta', 'whatsapp_evolution' => 'success',
                        'facebook' => 'info',
                        'instagram' => 'warning',
                        'telegram' => 'primary',
                        'email' => 'gray',
                        'webchat' => 'purple',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'whatsapp_meta' => '📱 WhatsApp (Meta)',
                        'whatsapp_evolution' => '📱 WhatsApp (Evolution)',
                        'facebook' => '💬 Facebook',
                        'instagram' => '📷 Instagram',
                        'telegram' => 'Telegram',
                        'email' => '📧 E-mail',
                        'webchat' => '🌐 Webchat',
                        'sms' => '📩 SMS',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('identifier')
                    ->label('Identificador')
                    ->limit(30),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('conversations_count')
                    ->counts('conversations')
                    ->label('Conversas'),
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}
