<?php


namespace ThisIsDevelopment\LaravelTenants\Contracts;

use Tenancy\Identification\Contracts\Tenant as BaseTenant;
use Tenancy\Identification\Drivers\Console\Contracts\IdentifiesByConsole;

interface Tenant extends TenantAuth, BaseTenant, IdentifiesByConsole, IdentifiesByAuthenticatedRequest
{

}
