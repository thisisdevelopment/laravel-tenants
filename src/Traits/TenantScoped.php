<?php

namespace ThisIsDevelopment\LaravelTenants\Traits;

use Illuminate\Database\Eloquent\Builder;
use ThisIsDevelopment\LaravelTenants\Contracts\Tenant;
use ThisIsDevelopment\LaravelTenants\Scopes\TenantScope;

trait TenantScoped
{
    public static function bootTenantScoped()
    {
        static::addGlobalScope(new TenantScope());
    }

    abstract public function scopeTenant(Builder $query, Tenant $tenant): void;
}
