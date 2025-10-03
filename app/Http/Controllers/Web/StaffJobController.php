<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\CartRecoveryJob;
use App\Models\CartRecoveryNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StaffJobController extends Controller
{
    public function markContacted(Request $request, CartRecoveryJob $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $job->fill([
            'status' => CartRecoveryJob::STATUS_SENT,
            'acknowledged_at' => Carbon::now(),
            'finished_at' => Carbon::now(),
        ])->save();

        return back()->with('status', 'Contato registrado como concluído.');
    }

    public function reschedule(Request $request, CartRecoveryJob $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $job->fill([
            'status' => CartRecoveryJob::STATUS_PENDING,
            'scheduled_at' => Carbon::now()->addMinutes(5),
            'last_error' => null,
        ]);
        $job->attempts = $job->attempts + 1;
        $job->save();

        return back()->with('status', 'Envio reprogramado para os próximos minutos.');
    }

    public function storeNote(Request $request, CartRecoveryJob $job): RedirectResponse
    {
        $this->authorizeJob($job);

        $data = $request->validate([
            'note' => ['required', 'string', 'max:2000'],
        ]);

        CartRecoveryNote::create([
            'job_id' => $job->id,
            'user_id' => $request->user()->id,
            'note' => $data['note'],
        ]);

        return back()->with('status', 'Nota adicionada ao atendimento.');
    }

    private function authorizeJob(CartRecoveryJob $job): void
    {
        $user = auth()->user();

        if ($user->role === UserRole::MASTER) {
            return;
        }

        if (in_array($user->role, [UserRole::ADMIN, UserRole::STAFF], true) && $user->client_id === $job->client_id) {
            return;
        }

        abort(403, 'Acesso negado ao job informado.');
    }
}
