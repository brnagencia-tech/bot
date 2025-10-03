<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Models\Lead;
use App\Models\Stage;

class ApiKanbanController
{
    public function move()
    {
        Auth::requireLogin();
        $user = Auth::user();
        // Espera JSON: [{id, stage_id, position}, ...]
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Dados inválidos']);
            return;
        }
        foreach ($data as $item) {
            if (!isset($item['id'], $item['stage_id'], $item['position'])) continue;
            Lead::updatePosition($item['id'], $user->id, $item['stage_id'], $item['position']);
        }
        echo json_encode(['success' => true]);
    }
}
