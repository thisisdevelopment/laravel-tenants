<?php

namespace ThisIsDevelopment\LaravelTenants\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Tenancy\Environment;
use Tenancy\Facades\Tenancy;
use ThisIsDevelopment\LaravelTenants\Contracts\Tenant;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var Environment $environment */
        $environment = app(Environment::class);
        $tenant = $environment->getTenant();
        if (!$tenant) {
            return;
        }

        $model->scopeTenant($builder, $tenant);
    }
}
