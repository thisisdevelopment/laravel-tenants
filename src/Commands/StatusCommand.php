<?php

namespace ThisIsDevelopment\LaravelTenants\Commands;

use Illuminate\Database\Console\Migrations\StatusCommand as BaseMigrateCommand;
use ThisIsDevelopment\LaravelTenants\Traits\SupportsTenantMigrations;

class StatusCommand extends BaseMigrateCommand
{
    use SupportsTenantMigrations;
}
