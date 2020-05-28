<?php

namespace ThisIsDevelopment\LaravelTenants\Commands;

use Illuminate\Database\Console\Migrations\RollbackCommand as BaseMigrateCommand;
use ThisIsDevelopment\LaravelTenants\Traits\SupportsTenantMigrations;

class RollbackCommand extends BaseMigrateCommand
{
    use SupportsTenantMigrations;
}
