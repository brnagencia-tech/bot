@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Kanban de Leads</h1>
    <div class="grid grid-cols-1 md:grid-cols-{{ max(count($stages),1) }} gap-4" id="kanban">
        @foreach($stages as $stage)
        <div class="border rounded p-2" data-stage-id="{{ $stage->id }}" ondragover="event.preventDefault();" ondrop="onDrop(event, {{ $stage->id }})">
            <div class="font-semibold mb-2">{{ $stage->name }}</div>
            <div class="space-y-2 min-h-10" id="stage-{{ $stage->id }}">
                @foreach(($leadsByStage[$stage->id] ?? []) as $lead)
                <div class="p-2 border rounded bg-white" draggable="true" ondragstart="onDragStart(event, {{ $lead->id }})" data-lead-id="{{ $lead->id }}">
                    <div class="font-medium">{{ $lead->name ?? $lead->phone ?? 'Lead #'.$lead->id }}</div>
                    <div class="text-xs text-gray-600">{{ $lead->email }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
let draggedId = null;
function onDragStart(e, id) {
    draggedId = id;
}

function onDrop(e, stageId) {
    e.preventDefault();
    const column = document.getElementById('stage-' + stageId);
    const card = document.querySelector(`[data-lead-id="${draggedId}"]`);
    if (!column || !card) return;
    column.appendChild(card);
    // recompute positions
    const moves = [];
    document.querySelectorAll(`[id^="stage-"]`).forEach((col) => {
        const sid = parseInt(col.id.split('-')[1], 10);
        [...col.children].forEach((child, idx) => {
            const leadId = parseInt(child.getAttribute('data-lead-id'), 10);
            if (!isNaN(leadId)) {
                moves.push({id: leadId, stage_id: sid, position: idx+1});
            }
        })
    });

    fetch('{{ route('leads.move') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({moves}),
    }).then(() => {}).catch(() => {});
}
</script>
@endsection

