<?php

namespace ThisIsDevelopment\LaravelTenants\Contracts;

use Illuminate\Support\Collection;

interface TenantAuth
{
    public function getAllowedTenants(): ?Collection;

    public function getDefaultTenant();
}
