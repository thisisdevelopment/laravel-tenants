<?php

namespace ThisIsDevelopment\LaravelTenants\Commands;

use ArgumentCountError;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\MigrateCommand as BaseMigrateCommand;
use Illuminate\Database\Migrations\Migrator;
use ThisIsDevelopment\LaravelTenants\Traits\SupportsTenantMigrations;

class MigrateCommand extends BaseMigrateCommand
{
    use SupportsTenantMigrations;

    public function __construct(Migrator $migrator, Dispatcher $dispatcher)
    {
        try {
            parent::__construct($migrator, $dispatcher);
        } catch (ArgumentCountError $e) {
            parent::__construct($migrator);
        }
    }
}
