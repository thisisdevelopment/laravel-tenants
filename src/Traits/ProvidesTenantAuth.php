<?php

namespace ThisIsDevelopment\LaravelTenants\Traits;

use Illuminate\Http\Request;
use Tenancy\Identification\Contracts\Tenant;

trait ProvidesTenantAuth
{
    use TenantScoped;

    public static function bootProvidesTenantAuth()
    {
        static::bootTenantScoped();
    }
}
