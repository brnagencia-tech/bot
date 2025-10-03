<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\CartRecoveryNoteRequest;
use App\Models\CartRecoveryJob;
use Illuminate\Http\JsonResponse;

class CartRecoveryNoteController extends Controller
{
    public function index(CartRecoveryJob $job): JsonResponse
    {
        $this->authorizeJobAccess($job);

        $notes = $job->notes()->with('author:id,name')->latest()->get()->map(fn ($note) => [
            'id' => $note->id,
            'author' => $note->author?->only(['id', 'name']),
            'note' => $note->note,
            'created_at' => $note->created_at->toIso8601String(),
        ]);

        return response()->json(['data' => $notes]);
    }

    public function store(CartRecoveryNoteRequest $request, CartRecoveryJob $job): JsonResponse
    {
        $this->authorizeJobAccess($job);

        $note = $job->notes()->create([
            'user_id' => $request->user()->id,
            'note' => $request->validated()['note'],
        ]);

        return response()->json([
            'note' => [
                'id' => $note->id,
                'note' => $note->note,
                'author' => $note->author?->only(['id', 'name']),
                'created_at' => $note->created_at->toIso8601String(),
            ],
        ], 201);
    }

    private function authorizeJobAccess(CartRecoveryJob $job): void
    {
        $user = request()->user();

        if ($user->role === UserRole::MASTER) {
            return;
        }

        if ($user->client_id === $job->client_id) {
            return;
        }

        abort(403, 'Este job pertence a outro cliente.');
    }
}
