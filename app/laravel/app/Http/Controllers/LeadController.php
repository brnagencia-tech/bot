<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Pipeline;
use App\Models\Stage;
use App\Support\CurrentTenant;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function kanban()
    {
        $tenant = CurrentTenant::get();
        abort_unless($tenant, 404);

        $pipeline = Pipeline::where('tenant_id', $tenant->id)->where('is_default', true)->first()
            ?? Pipeline::where('tenant_id', $tenant->id)->first();

        $stages = Stage::where('pipeline_id', optional($pipeline)->id)->orderBy('order')->get();
        $leadsByStage = [];
        foreach ($stages as $stage) {
            $leadsByStage[$stage->id] = Lead::where('tenant_id', $tenant->id)
                ->where('stage_id', $stage->id)
                ->orderBy('position')
                ->get();
        }

        return view('leads.kanban', compact('stages', 'leadsByStage'));
    }

    public function move(Request $request)
    {
        $tenant = CurrentTenant::get();
        abort_unless($tenant, 404);

        $data = $request->validate([
            'moves' => 'required|array',
            'moves.*.id' => 'required|integer|exists:leads,id',
            'moves.*.stage_id' => 'required|integer|exists:stages,id',
            'moves.*.position' => 'required|integer',
        ]);

        foreach ($data['moves'] as $item) {
            Lead::where('tenant_id', $tenant->id)
                ->where('id', $item['id'])
                ->update([
                    'stage_id' => $item['stage_id'],
                    'position' => $item['position'],
                ]);
        }

        return response()->json(['success' => true]);
    }
}

