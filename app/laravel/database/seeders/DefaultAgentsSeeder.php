<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultAgentsSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->first();
        if (!$tenant) return;

        $defaults = [
            [
                'role' => 'SDR',
                'prompt' => 'Você é um SDR. Faça qualificação e colete dados.',
            ],
            [
                'role' => 'CLOSER',
                'prompt' => 'Você é um Closer. Foque em fechamento e objeções.',
            ],
            [
                'role' => 'SUPORTE',
                'prompt' => 'Você é Suporte. Faça triagem fora do horário.',
            ],
        ];

        foreach ($defaults as $cfg) {
            Agent::firstOrCreate(
                ['tenant_id' => $tenant->id, 'role' => $cfg['role']],
                [
                    'prompt' => $cfg['prompt'],
                    'tools_enabled' => ['create_lead', 'update_stage', 'book_meeting', 'send_payment_link'],
                    'temperature' => 0.4,
                    'language' => 'pt-BR',
                ]
            );
        }
    }
}

