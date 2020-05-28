<?php

namespace ThisIsDevelopment\LaravelTenants\Commands;

use Illuminate\Database\Console\Migrations\InstallCommand as BaseMigrateCommand;
use ThisIsDevelopment\LaravelTenants\Traits\SupportsTenantMigrations;

class InstallCommand extends BaseMigrateCommand
{
    use SupportsTenantMigrations;
}
