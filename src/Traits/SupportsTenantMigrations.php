<?php

namespace ThisIsDevelopment\LaravelTenants\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Tenancy\Facades\Tenancy;
use ThisIsDevelopment\LaravelTenants\TenantsProvider;

trait SupportsTenantMigrations
{
    public function handle()
    {
        /** @var InputInterface $input */
        $input = $this->input;
        
        if ($input->hasOption('path') && $input->getOption('tenant')) {
            $input->setOption('path', TenantsProvider::getDatabasePath());
            $input->setOption('realpath', true);
        }
        
        if ($input->hasOption('database') && $input->getOption('tenant')) {

            $connection = Tenancy::getTenantConnectionName();
            $input->setOption('database', $connection);
            

            if (strtolower($input->getOption('tenant')) === 'all') {

                foreach (TenantsProvider::getTenants() as $tenant) {
                    Tenancy::setTenant($tenant);

                    $this->alert('Tenant database: ' . config("database.connections.{$connection}.database"));

                    parent::handle();
                }
                return;
            }
            else {
                $this->alert('Tenant database: ' . config("database.connections.{$connection}.database"));
            }
        }

        parent::handle();
    }
}
