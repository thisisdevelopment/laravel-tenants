<?php

namespace ThisIsDevelopment\LaravelTenants\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand as BaseMigrateCommand;
use ThisIsDevelopment\LaravelTenants\Traits\SupportsTenantMigrations;

class MigrateCommand extends BaseMigrateCommand
{
    use SupportsTenantMigrations;
}
