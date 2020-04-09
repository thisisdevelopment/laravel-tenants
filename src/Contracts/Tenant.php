<?php


namespace ThisIsDevelopment\LaravelTenants\Contracts;

use Tenancy\Identification\Contracts\Tenant as BaseTenant;
use Tenancy\Identification\Drivers\Console\Contracts\IdentifiesByConsole;
use Tenancy\Identification\Drivers\Queue\Contracts\IdentifiesByQueue;

interface Tenant extends TenantAuth, BaseTenant, IdentifiesByConsole, IdentifiesByQueue, IdentifiesByAuthenticatedRequest
{

}
