<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Facades\App;

class CurrentTenant
{
    public static function set(?Tenant $tenant): void
    {
        App::instance('currentTenant', $tenant);
    }

    public static function get(): ?Tenant
    {
        /** @var Tenant|null $tenant */
        $tenant = App::has('currentTenant') ? App::get('currentTenant') : null;
        return $tenant;
    }
}

