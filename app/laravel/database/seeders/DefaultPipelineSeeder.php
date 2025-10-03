<?php

namespace Database\Seeders;

use App\Models\Pipeline;
use App\Models\Stage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DefaultPipelineSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->first();
        if (!$tenant) return;

        $pipeline = Pipeline::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Padrão'],
            ['is_default' => true]
        );

        $stages = [
            'Novo', 'Qualificado', 'Proposta', 'Fechamento', 'Pós-venda',
        ];

        foreach ($stages as $i => $name) {
            Stage::firstOrCreate([
                'pipeline_id' => $pipeline->id,
                'name' => $name,
            ], [
                'order' => $i + 1,
            ]);
        }
    }
}

