<?php

namespace ThisIsDevelopment\LaravelTenants\Commands;

use Illuminate\Database\Console\Migrations\FreshCommand as BaseMigrateCommand;
use ThisIsDevelopment\LaravelTenants\Traits\SupportsTenantMigrations;

class FreshCommand extends BaseMigrateCommand
{
    use SupportsTenantMigrations;
}
