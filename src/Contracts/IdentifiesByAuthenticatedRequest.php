<?php

namespace ThisIsDevelopment\LaravelTenants\Contracts;

use Illuminate\Http\Request;
use Tenancy\Identification\Contracts\Tenant as BaseTenant;

interface IdentifiesByAuthenticatedRequest
{
    public function tenantIdentificationByAuthenticatedRequest(Request $request): ?BaseTenant;
}
