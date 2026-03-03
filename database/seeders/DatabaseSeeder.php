<?php

namespace Database\Seeders;

use App\Models\Automation;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\Channel;
use App\Models\Contact;
use App\Models\Conversation;
use App\Models\KnowledgeArticle;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Message;
use App\Models\QuickReply;
use App\Models\SatisfactionSurvey;
use App\Models\SlaPolicy;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tenant ────────────────────────────────────────────
        $tenant = Tenant::create([
            'name'           => 'Elite Seminovos',
            'slug'           => 'elite-seminovos',
            'cnpj'           => '12.345.678/0001-90',
            'phone'          => '(11) 99999-0001',
            'email'          => 'contato@eliteseminovos.com.br',
            'address'        => 'Av. Brasil, 1500 - Centro',
            'city'           => 'São Paulo',
            'state'          => 'SP',
            'business_hours' => json_encode(['start' => '08:00', 'end' => '18:00']),
            'settings'       => json_encode(['nps_enabled' => true, 'csat_enabled' => true]),
            'is_active'      => true,
        ]);

        $tid = $tenant->id;

        // ── Users ─────────────────────────────────────────────
        $admin = User::create([
            'name'      => 'Carlos Admin',
            'email'     => 'admin@elite.com',
            'password'  => Hash::make('password'),
            'tenant_id' => $tid,
            'role'      => 'admin',
            'phone'     => '(11) 99999-1001',
            'is_active' => true,
            'is_online' => true,
        ]);

        $gestor = User::create([
            'name'      => 'Fernanda Gestora',
            'email'     => 'gestor@elite.com',
            'password'  => Hash::make('password'),
            'tenant_id' => $tid,
            'role'      => 'gestor',
            'phone'     => '(11) 99999-1002',
            'is_active' => true,
            'is_online' => true,
        ]);

        $vendedor = User::create([
            'name'      => 'Ricardo Vendedor',
            'email'     => 'vendedor@elite.com',
            'password'  => Hash::make('password'),
            'tenant_id' => $tid,
            'role'      => 'vendedor',
            'phone'     => '(11) 99999-1003',
            'is_active' => true,
            'is_online' => false,
        ]);

        $atendente = User::create([
            'name'      => 'Juliana Atendente',
            'email'     => 'atendente@elite.com',
            'password'  => Hash::make('password'),
            'tenant_id' => $tid,
            'role'      => 'atendente',
            'phone'     => '(11) 99999-1004',
            'is_active' => true,
            'is_online' => true,
        ]);

        $users = [$admin, $gestor, $vendedor, $atendente];

        // ── Channels ──────────────────────────────────────────
        $whatsapp = Channel::create([
            'tenant_id'  => $tid,
            'name'       => 'WhatsApp Comercial',
            'type'       => 'whatsapp',
            'identifier' => '+5511999990001',
            'is_active'  => true,
        ]);

        $instagram = Channel::create([
            'tenant_id'  => $tid,
            'name'       => 'Instagram',
            'type'       => 'instagram',
            'identifier' => '@eliteseminovos',
            'is_active'  => true,
        ]);

        $emailCh = Channel::create([
            'tenant_id'  => $tid,
            'name'       => 'E-mail Suporte',
            'type'       => 'email',
            'identifier' => 'suporte@eliteseminovos.com.br',
            'is_active'  => true,
        ]);

        $webchat = Channel::create([
            'tenant_id'  => $tid,
            'name'       => 'Chat do Site',
            'type'       => 'webchat',
            'identifier' => 'widget-xyz',
            'is_active'  => true,
        ]);

        $channels = [$whatsapp, $instagram, $emailCh, $webchat];

        // ── SLA Policies ──────────────────────────────────────
        $slaData = [
            ['name' => 'Urgente',  'priority' => 'urgent', 'first_response_minutes' => 15,  'resolution_minutes' => 120],
            ['name' => 'Alta',     'priority' => 'high',   'first_response_minutes' => 30,  'resolution_minutes' => 240],
            ['name' => 'Média',    'priority' => 'medium', 'first_response_minutes' => 60,  'resolution_minutes' => 480],
            ['name' => 'Baixa',    'priority' => 'low',    'first_response_minutes' => 120, 'resolution_minutes' => 1440],
        ];
        $slaPolicies = [];
        foreach ($slaData as $s) {
            $slaPolicies[$s['priority']] = SlaPolicy::create(array_merge($s, ['tenant_id' => $tid, 'is_active' => true]));
        }

        // ── Quick Replies ─────────────────────────────────────
        $quickRepliesData = [
            ['/saudacao',       'Saudação',      "Olá {nome}! Bem-vindo à Elite Seminovos 🚗\nComo posso ajudá-lo(a) hoje?"],
            ['/agradecimento',  'Saudação',      "Agradecemos seu contato, {nome}! Qualquer dúvida estamos à disposição. 😊"],
            ['/financiamento',  'Financiamento', "Trabalhamos com financiamento facilitado em até 60x! Para uma simulação, preciso das seguintes informações:\n- Nome completo\n- CPF\n- Renda mensal\n- Veículo de interesse"],
            ['/documentos',     'Documentação',  "Para a transferência do veículo, os documentos necessários são:\n✅ RG e CPF\n✅ Comprovante de residência\n✅ CNH válida"],
            ['/garantia',       'Pós-venda',     "Nossos veículos possuem garantia de motor e câmbio de 3 meses. Para acionamento, entre em contato conosco."],
            ['/horario',        'Geral',         "Nosso horário de funcionamento:\n📅 Segunda a Sexta: 08h às 18h\n📅 Sábado: 08h às 13h\n📍 Av. Brasil, 1500 - Centro, São Paulo/SP"],
        ];
        foreach ($quickRepliesData as [$shortcut, $category, $message]) {
            QuickReply::create([
                'tenant_id' => $tid,
                'title'     => ltrim($shortcut, '/'),
                'shortcut'  => $shortcut,
                'category'  => $category,
                'body'      => $message,
                'is_global' => true,
            ]);
        }

        // ── Contacts ──────────────────────────────────────────
        $contactsData = [
            ['João Silva',        '(11) 98765-0001', 'joao.silva@email.com',    '123.456.789-01', 'São Paulo',      'SP'],
            ['Maria Oliveira',    '(11) 98765-0002', 'maria.oliveira@email.com','234.567.890-12', 'Guarulhos',      'SP'],
            ['Pedro Santos',      '(11) 98765-0003', 'pedro.santos@email.com',  '345.678.901-23', 'Campinas',       'SP'],
            ['Ana Costa',         '(21) 98765-0004', 'ana.costa@email.com',     '456.789.012-34', 'Rio de Janeiro', 'RJ'],
            ['Marcos Pereira',    '(11) 98765-0005', 'marcos.pereira@email.com','567.890.123-45', 'Osasco',         'SP'],
            ['Carla Ferreira',    '(31) 98765-0006', 'carla.ferreira@email.com','678.901.234-56', 'Belo Horizonte', 'MG'],
            ['Lucas Almeida',     '(11) 98765-0007', 'lucas.almeida@email.com', '789.012.345-67', 'Santo André',    'SP'],
            ['Tatiana Gomes',     '(11) 98765-0008', 'tatiana.gomes@email.com', '890.123.456-78', 'São Bernardo',   'SP'],
            ['Roberto Lima',      '(19) 98765-0009', 'roberto.lima@email.com',  '901.234.567-89', 'Sorocaba',       'SP'],
            ['Patrícia Mendes',   '(11) 98765-0010', 'patricia.mendes@email.com','012.345.678-90','São Paulo',      'SP'],
            ['Fernando Barbosa',  '(11) 98765-0011', 'fernando.b@email.com',    null,              'São Paulo',      'SP'],
            ['Renata Nascimento', '(11) 98765-0012', 'renata.n@email.com',      null,              'Mogi das Cruzes','SP'],
        ];

        $contacts = [];
        foreach ($contactsData as [$name, $phone, $email, $doc, $city, $state]) {
            $contacts[] = Contact::create([
                'tenant_id' => $tid,
                'name'      => $name,
                'phone'     => $phone,
                'email'     => $email,
                'cpf'       => $doc,
                'city'      => $city,
                'state'     => $state,
                'tags'      => json_encode(array_slice(collect(['cliente', 'prospect', 'financiamento', 'troca', 'pcd', 'empresa'])->shuffle()->all(), 0, rand(1, 3))),
            ]);
        }

        // ── Vehicles ──────────────────────────────────────────
        $vehiclesData = [
            ['Toyota',     'Corolla',     'XEi 2.0 Flex',    2023, 2023, 'Branco Pérola',  'flex',    'automatico', 15000,  139000.00, 142000.00, 'available'],
            ['Honda',      'Civic',       'EXL 2.0',         2022, 2023, 'Preto',           'flex',    'automatico', 28000,  125000.00, 128000.00, 'available'],
            ['Volkswagen', 'T-Cross',     'Highline 1.4 TSI',2023, 2023, 'Cinza Platinum',  'flex',    'automatico', 12000,  145000.00, 149000.00, 'available'],
            ['Hyundai',    'HB20',        'Diamond Plus 1.0T',2023,2024, 'Vermelho',        'flex',    'automatico', 8000,   95000.00,  98000.00,  'available'],
            ['Jeep',       'Compass',     'Limited 1.3T',    2022, 2023, 'Azul',            'flex',    'automatico', 35000,  165000.00, 170000.00, 'reserved'],
            ['Chevrolet',  'Onix',        'LTZ 1.0T',        2023, 2023, 'Prata',           'flex',    'automatico', 10000,  82000.00,  85000.00,  'available'],
            ['Fiat',       'Pulse',       'Impetus 1.0T',    2023, 2024, 'Branco',          'flex',    'automatico', 5000,   105000.00, 108000.00, 'available'],
            ['Toyota',     'Hilux',       'SRX 2.8 Diesel',  2022, 2022, 'Prata',           'diesel',  'automatico', 45000,  270000.00, 280000.00, 'available'],
            ['Volkswagen', 'Polo',        'Comfortline 1.0 TSI',2022,2022,'Branco',         'flex',    'automatico', 32000,  85000.00,  88000.00,  'sold'],
            ['Honda',      'HR-V',        'EXL 1.5T',        2023, 2024, 'Cinza',           'flex',    'cvt',        18000,  160000.00, 165000.00, 'available'],
            ['Fiat',       'Argo',        'Trekking 1.3',    2023, 2023, 'Vermelho',        'flex',    'manual',     15000,  78000.00,  80000.00,  'available'],
            ['Nissan',     'Kicks',       'Exclusive 1.6',   2022, 2023, 'Preto',           'flex',    'cvt',        22000,  112000.00, 115000.00, 'reserved'],
        ];

        $vehicles = [];
        foreach ($vehiclesData as [$brand, $model, $version, $yearFab, $yearMod, $color, $fuel, $trans, $km, $price, $fipe, $status]) {
            $vehicles[] = Vehicle::create([
                'tenant_id'      => $tid,
                'brand'          => $brand,
                'model'          => $model,
                'version'        => $version,
                'year_manufacture' => $yearFab,
                'year_model'       => $yearMod,
                'color'            => $color,
                'plate'            => strtoupper(Str::random(3)) . rand(1, 9) . strtoupper(Str::random(1)) . rand(10, 99),
                'fuel_type'        => $fuel,
                'transmission'     => $trans,
                'mileage'          => $km,
                'price'            => $price,
                'fipe_price'       => $fipe,
                'features'         => json_encode(['Ar condicionado', 'Direção elétrica', 'Vidros elétricos', 'Travas elétricas', 'Airbag', 'ABS', 'Central multimídia']),
                'photos'           => json_encode([]),
                'status'           => $status,
                'description'      => $status === 'sold' ? 'Vendido para cliente João Silva' : null,
            ]);
        }

        // ── Conversations & Messages ──────────────────────────
        $statuses = ['new', 'open', 'pending', 'resolved'];
        $conversations = [];

        foreach (array_slice($contacts, 0, 8) as $i => $contact) {
            $channel = $channels[$i % count($channels)];
            $status = $statuses[$i % count($statuses)];
            $assignedTo = ($status !== 'new') ? $users[$i % count($users)]->id : null;

            $conv = Conversation::create([
                'tenant_id'           => $tid,
                'contact_id'          => $contact->id,
                'channel_id'          => $channel->id,
                'assigned_to'         => $assignedTo,
                'status'              => $status,
                'last_message_preview'=> $this->conversationSubjects()[$i % 8],
                'last_message_at'     => now()->subHours(rand(0, 48)),
                'unread_count'        => $status === 'new' ? rand(1, 5) : 0,
            ]);

            $conversations[] = $conv;

            // Messages for each conversation
            $msgCount = rand(3, 8);
            for ($m = 0; $m < $msgCount; $m++) {
                $isInbound = $m % 2 === 0;
                Message::create([
                    'conversation_id' => $conv->id,
                    'contact_id'      => $isInbound ? $contact->id : null,
                    'user_id'         => $isInbound ? null : ($assignedTo ?? $admin->id),
                    'direction'       => $isInbound ? 'inbound' : 'outbound',
                    'type'            => 'text',
                    'body'            => $this->sampleMessages($isInbound)[$m % 5],
                    'is_internal_note'=> false,
                    'created_at'      => now()->subHours($msgCount - $m),
                ]);
            }

            // Internal note on some
            if ($i % 3 === 0) {
                Message::create([
                    'conversation_id' => $conv->id,
                    'user_id'         => $gestor->id,
                    'direction'       => 'outbound',
                    'type'            => 'text',
                    'body'            => 'Nota interna: Cliente demonstrou interesse alto, priorizar atendimento.',
                    'is_internal_note'=> true,
                    'created_at'      => now()->subMinutes(30),
                ]);
            }
        }

        // ── Leads ─────────────────────────────────────────────
        $stages = ['new', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
        $sources = ['whatsapp', 'instagram', 'website', 'indicacao', 'olx', 'webmotors'];
        $temperatures = ['cold', 'warm', 'hot'];

        $leads = [];
        foreach (array_slice($contacts, 0, 10) as $i => $contact) {
            $stage = $stages[$i % count($stages)];
            $vehicle = $vehicles[$i % count($vehicles)];

            $lead = Lead::create([
                'tenant_id'        => $tid,
                'contact_id'       => $contact->id,
                'assigned_to'      => $users[$i % count($users)]->id,
                'vehicle_interest' => "{$vehicle->brand} {$vehicle->model} {$vehicle->version}",
                'stage'            => $stage,
                'temperature'      => $temperatures[$i % 3],
                'estimated_value'  => $vehicle->price,
                'source'           => $sources[$i % count($sources)],
                'notes'            => "Cliente interessado no {$vehicle->brand} {$vehicle->model}. Cor de preferência: {$vehicle->color}.",
                'won_at'           => $stage === 'won' ? now()->subDays(rand(1, 10)) : null,
                'lost_at'          => $stage === 'lost' ? now()->subDays(rand(1, 10)) : null,
                'lost_reason'      => $stage === 'lost' ? 'Preço acima do orçamento' : null,
            ]);

            $leads[] = $lead;

            // Lead activities
            LeadActivity::create([
                'lead_id'   => $lead->id,
                'user_id'   => $lead->assigned_to,
                'type'      => 'stage_change',
                'description'=> "Lead criado na etapa {$stage}",
            ]);

            if ($stage !== 'new') {
                LeadActivity::create([
                    'lead_id'    => $lead->id,
                    'user_id'    => $lead->assigned_to,
                    'type'       => 'call',
                    'description'=> 'Ligação realizada para qualificação do lead.',
                    'created_at' => now()->subDays(rand(1, 5)),
                ]);
            }

            if (in_array($stage, ['proposal', 'negotiation', 'won'])) {
                LeadActivity::create([
                    'lead_id'    => $lead->id,
                    'user_id'    => $lead->assigned_to,
                    'type'       => 'note',
                    'description'=> "Proposta enviada: R$ " . number_format($vehicle->price, 2, ',', '.'),
                    'created_at' => now()->subDays(rand(1, 3)),
                ]);
            }
        }

        // ── Tickets ───────────────────────────────────────────
        $ticketsData = [
            ['Problema com transferência de documentos',     'documentation', 'high',   'open'],
            ['Reclamação sobre pintura do veículo',          'complaint',     'urgent', 'open'],
            ['Solicitação de segunda via de nota fiscal',     'financial',     'medium', 'pending'],
            ['Agendamento de revisão 10.000km',              'warranty',      'low',    'open'],
            ['Dúvida sobre seguro do veículo',               'general',       'medium', 'resolved'],
            ['Problema com ar condicionado após compra',     'warranty',      'high',   'open'],
            ['Solicitação de cancelamento de financiamento',  'financial',     'urgent', 'open'],
            ['Feedback positivo sobre atendimento',          'general',       'low',    'resolved'],
        ];

        foreach ($ticketsData as $i => [$subject, $category, $priority, $status]) {
            $contact = $contacts[$i % count($contacts)];
            $sla = $slaPolicies[$priority];

            $ticket = Ticket::create([
                'tenant_id'            => $tid,
                'contact_id'           => $contact->id,
                'assigned_to'          => $users[$i % count($users)]->id,
                'number'               => 'TK-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'subject'              => $subject,
                'description'          => "Detalhes do chamado: {$subject}. O cliente {$contact->name} entrou em contato solicitando suporte.",
                'category'             => $category,
                'priority'             => $priority,
                'status'               => $status,
                'due_at'               => now()->addMinutes($sla->resolution_minutes),
                'first_response_at'    => $status !== 'open' ? now()->subHours(1) : null,
                'resolved_at'          => $status === 'resolved' ? now()->subHours(rand(1, 24)) : null,
            ]);

            TicketComment::create([
                'ticket_id'      => $ticket->id,
                'user_id'        => $ticket->assigned_to,
                'body'            => 'Ticket recebido e em análise. Entraremos em contato em breve.',
                'is_internal'    => false,
                'created_at'     => now()->subHours(2),
            ]);

            if ($status === 'resolved') {
                TicketComment::create([
                    'ticket_id'      => $ticket->id,
                    'user_id'        => $ticket->assigned_to,
                    'body'            => 'Problema resolvido conforme solicitação. Ticket encerrado.',
                    'is_internal'    => false,
                    'created_at'     => now()->subHours(1),
                ]);
            }
        }

        // ── Campaigns ─────────────────────────────────────────
        $campaign1 = Campaign::create([
            'tenant_id'        => $tid,
            'name'             => 'Feirão de Seminovos - Janeiro',
            'type'             => 'whatsapp',
            'message_template' => "🚗 *FEIRÃO ELITE SEMINOVOS* 🚗\n\nOlá {nome}!\n\nEste mês temos condições imperdíveis:\n✅ Taxa 0% nos primeiros 6 meses\n✅ Entrada facilitada\n✅ Bônus de R$ 2.000 na troca\n\nVenha conferir! 📍 Av. Brasil, 1500",
            'status'           => 'completed',
            'scheduled_at'     => now()->subDays(5),
            'started_at'       => now()->subDays(5),
            'completed_at'     => now()->subDays(5),
            'total_recipients' => 8,
            'sent_count'       => 8,
            'delivered_count'  => 7,
            'read_count'       => 6,
            'failed_count'     => 1,
            'created_by'       => $gestor->id,
        ]);

        foreach (array_slice($contacts, 0, 8) as $i => $contact) {
            CampaignMessage::create([
                'campaign_id' => $campaign1->id,
                'contact_id'  => $contact->id,
                'status'      => $i < 6 ? 'delivered' : ($i < 7 ? 'read' : 'failed'),
                'sent_at'     => now()->subDays(5),
                'delivered_at'=> $i < 7 ? now()->subDays(5)->addMinutes(rand(1, 30)) : null,
                'read_at'     => $i < 6 ? now()->subDays(4) : null,
            ]);
        }

        $campaign2 = Campaign::create([
            'tenant_id'        => $tid,
            'name'             => 'Promoção SUVs Premium',
            'type'             => 'whatsapp',
            'message_template' => "🌟 *SUVs com condições especiais!*\n\nOlá {nome}, temos T-Cross, Compass e HR-V prontos para entrega!\n\nFinanciamos em até 60x.\n\nAgende seu test drive! 🚙",
            'status'           => 'draft',
            'created_by'       => $gestor->id,
        ]);

        Campaign::create([
            'tenant_id'        => $tid,
            'name'             => 'Pesquisa de Satisfação',
            'type'             => 'whatsapp',
            'message_template' => "Olá {nome}! 😊\n\nGostaríamos de saber: de 0 a 10, o quanto recomendaria a Elite Seminovos?\n\nSua opinião é muito importante para nós!",
            'status'           => 'scheduled',
            'scheduled_at'     => now()->addDays(3),
            'created_by'       => $admin->id,
        ]);

        // ── Satisfaction Surveys ──────────────────────────────
        $npsScores  = [10, 9, 8, 10, 7, 6, 9, 10, 3, 8];
        $csatScores = [5, 4, 5, 5, 3, 4, 5, 4, 2, 5];

        foreach (array_slice($contacts, 0, 10) as $i => $contact) {
            // NPS survey
            SatisfactionSurvey::create([
                'tenant_id'  => $tid,
                'contact_id' => $contact->id,
                'type'       => 'nps',
                'score'      => $npsScores[$i],
                'comment'    => $npsScores[$i] >= 9
                    ? 'Excelente atendimento! Recomendo a todos.'
                    : ($npsScores[$i] >= 7 ? 'Bom atendimento, mas pode melhorar nos prazos.' : 'Achei o processo muito demorado.'),
            ]);
            // CSAT survey
            SatisfactionSurvey::create([
                'tenant_id'  => $tid,
                'contact_id' => $contact->id,
                'type'       => 'csat',
                'score'      => $csatScores[$i],
            ]);
        }

        // ── Knowledge Articles ────────────────────────────────
        $articlesData = [
            ['Como funciona o financiamento de seminovos?', 'financiamento', true,
             '<h2>Financiamento de Seminovos</h2><p>A Elite Seminovos trabalha com diversas instituições financeiras para oferecer as melhores condições de financiamento.</p><h3>Documentos necessários</h3><ul><li>RG e CPF</li><li>Comprovante de renda</li><li>Comprovante de residência</li><li>CNH válida</li></ul><h3>Condições</h3><p>Financiamos em até <strong>60 parcelas</strong> com taxas a partir de <strong>0,99% a.m.</strong></p><p>A aprovação é feita em até 24 horas úteis.</p>'],
            ['Documentação para transferência de veículo', 'documentacao', true,
             '<h2>Transferência de Veículo</h2><p>Ao adquirir um seminovo na Elite, cuidamos de toda a documentação de transferência.</p><h3>Prazo</h3><p>A transferência é realizada em até <strong>30 dias</strong> após a compra.</p><h3>Custos inclusos</h3><ul><li>IPVA proporcional</li><li>Taxa de transferência DETRAN</li><li>Emissão de novo CRV</li></ul><p>O comprador recebe o CRV em seu nome no endereço cadastrado.</p>'],
            ['Garantia dos veículos seminovos', 'garantia', true,
             '<h2>Política de Garantia</h2><p>Todos os veículos passam por inspeção de <strong>150 pontos</strong> antes da venda.</p><h3>Cobertura</h3><ul><li><strong>Motor:</strong> 3 meses ou 5.000 km</li><li><strong>Câmbio:</strong> 3 meses ou 5.000 km</li><li><strong>Suspensão:</strong> 1 mês ou 2.000 km</li></ul><h3>Como acionar</h3><p>Para acionar a garantia, entre em contato pelo WhatsApp ou agende uma visita na loja.</p>'],
            ['Processo de troca com troco', 'troca', true,
             '<h2>Troca com Troco</h2><p>Aceite seu veículo usado como parte do pagamento!</p><h3>Como funciona</h3><ol><li>Traga seu veículo para avaliação</li><li>Nossos avaliadores verificam o estado geral</li><li>Você recebe uma proposta de valor</li><li>Se aceitar, a diferença pode ser financiada</li></ol><p>A avaliação é gratuita e sem compromisso.</p>'],
            ['Horário de funcionamento e localização', 'geral', true,
             '<h2>Horário e Localização</h2><h3>Horário</h3><ul><li><strong>Segunda a Sexta:</strong> 08h às 18h</li><li><strong>Sábado:</strong> 08h às 13h</li><li><strong>Domingo:</strong> Fechado</li></ul><h3>Endereço</h3><p>Av. Brasil, 1500 - Centro<br>São Paulo/SP - CEP 01001-000</p><p>Estacionamento gratuito para clientes.</p>'],
            ['Política de devolução', 'pos-venda', false,
             '<h2>Política de Devolução</h2><p><em>Este artigo está em elaboração.</em></p><p>De acordo com o Código de Defesa do Consumidor, o cliente possui prazo de 7 dias para desistência em compras realizadas fora do estabelecimento.</p>'],
        ];

        foreach ($articlesData as [$title, $category, $published, $content]) {
            KnowledgeArticle::create([
                'tenant_id'    => $tid,
                'title'        => $title,
                'slug'         => Str::slug($title),
                'category'     => $category,
                'body'         => $content,
                'author_id'    => $admin->id,
                'is_published' => $published,
            ]);
        }

        // ── Automations ───────────────────────────────────────
        Automation::create([
            'tenant_id'    => $tid,
            'name'         => 'Auto-resposta WhatsApp',
            'description'  => 'Enviar mensagem automática de boas-vindas ao receber novo contato via WhatsApp',
            'trigger_type' => 'new_conversation',
            'n8n_webhook_url' => 'https://n8n.eliteseminovos.com.br/webhook/auto-reply',
            'is_active'    => true,
        ]);

        Automation::create([
            'tenant_id'    => $tid,
            'name'         => 'Notificar gestor - Lead quente',
            'description'  => 'Notificar gestor quando um lead é marcado como "quente"',
            'trigger_type' => 'keyword',
            'n8n_webhook_url' => 'https://n8n.eliteseminovos.com.br/webhook/lead-hot',
            'is_active'    => true,
        ]);

        Automation::create([
            'tenant_id'    => $tid,
            'name'         => 'Alerta SLA próximo do vencimento',
            'description'  => 'Enviar alerta quando ticket estiver a 30 minutos de estourar o SLA',
            'trigger_type' => 'schedule',
            'n8n_webhook_url' => 'https://n8n.eliteseminovos.com.br/webhook/sla-warning',
            'is_active'    => true,
        ]);

        Automation::create([
            'tenant_id'    => $tid,
            'name'         => 'Pesquisa NPS pós-venda',
            'description'  => 'Enviar pesquisa de satisfação 7 dias após a venda',
            'trigger_type' => 'webhook',
            'n8n_webhook_url' => 'https://n8n.eliteseminovos.com.br/webhook/nps-survey',
            'is_active'    => false,
        ]);

        $this->command->info('✅ Seed concluído! Dados demo criados com sucesso.');
        $this->command->info('');
        $this->command->info('📧 Credenciais de acesso:');
        $this->command->info('   Admin:     admin@elite.com / password');
        $this->command->info('   Gestor:    gestor@elite.com / password');
        $this->command->info('   Vendedor:  vendedor@elite.com / password');
        $this->command->info('   Atendente: atendente@elite.com / password');
    }

    private function conversationSubjects(): array
    {
        return [
            'Interesse no Corolla 2023',
            'Dúvida sobre financiamento',
            'Agendamento test drive T-Cross',
            'Consulta estoque HB20',
            'Negociação Compass Limited',
            'Documentação transferência',
            'Revisão garantia Civic',
            'Orçamento Hilux diesel',
        ];
    }

    private function sampleMessages(bool $inbound): array
    {
        if ($inbound) {
            return [
                'Olá! Vi o anúncio do veículo e gostaria de saber mais informações.',
                'Qual o valor de entrada mínima para financiamento?',
                'O veículo ainda está disponível?',
                'Vocês aceitam troca? Tenho um carro 2019 para dar como entrada.',
                'Pode me enviar mais fotos do veículo por favor?',
            ];
        }
        return [
            'Olá! Bem-vindo à Elite Seminovos! 🚗 Como posso ajudá-lo?',
            'Sim, o veículo está disponível! A entrada mínima é de 20% do valor.',
            'Claro! Vou enviar as fotos agora mesmo. Aguarde um momento.',
            'Sim, aceitamos troca! Podemos agendar uma avaliação gratuita do seu veículo.',
            'Perfeito! Vou preparar uma proposta personalizada para você. Pode me informar seu CPF para consulta de crédito?',
        ];
    }
}
