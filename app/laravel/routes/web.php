<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Admin\WhatsappController as AdminWhatsappController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin area (MASTER or tenant ADMIN)
    Route::middleware(['tenant.set'])->group(function () {
        Route::middleware('role.global:MASTER')->group(function () {
            Route::get('/master/tenants', [AdminTenantController::class, 'index'])->name('master.tenants');
            Route::post('/master/tenants', [AdminTenantController::class, 'store'])->name('master.tenants.store');
        });

        Route::middleware('role.tenant:ADMIN')->group(function () {
            Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
            Route::get('/admin/whatsapp', [AdminWhatsappController::class, 'index'])->name('admin.whatsapp');
            Route::post('/admin/whatsapp', [AdminWhatsappController::class, 'store'])->name('admin.whatsapp.store');
        });

        // Leads Kanban (any authenticated within tenant)
        Route::get('/leads/kanban', [LeadController::class, 'kanban'])->name('leads.kanban');
        Route::post('/leads/move', [LeadController::class, 'move'])->name('leads.move');
    });
});

// WhatsApp webhooks
Route::get('/webhooks/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'verify']);
Route::post('/webhooks/whatsapp', [\App\Http\Controllers\Webhook\WhatsappWebhookController::class, 'receive']);

require __DIR__.'/auth.php';
