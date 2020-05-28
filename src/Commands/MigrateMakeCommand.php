<?php

namespace ThisIsDevelopment\LaravelTenants\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand as BaseMigrateCommand;
use ThisIsDevelopment\LaravelTenants\Traits\SupportsTenantMigrations;

class MigrateMakeCommand extends BaseMigrateCommand
{
    use SupportsTenantMigrations;
}
