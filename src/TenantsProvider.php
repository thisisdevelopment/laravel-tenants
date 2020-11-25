<?php

namespace ThisIsDevelopment\LaravelTenants;

use ArgumentCountError;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Tenancy\Affects\Connections\Contracts\ProvidesConfiguration;
use Tenancy\Affects\Connections\Events\Resolving as ConnectionResolving;
use Tenancy\Environment;
use Tenancy\Hooks\Database\Events\Drivers\Configuring as ConfigureDatabase;
use Tenancy\Hooks\Migration\Events\ConfigureMigrations;
use Tenancy\Identification\Contracts\ResolvesTenants;
use Tenancy\Identification\Contracts\Tenant;
use ThisIsDevelopment\LaravelTenants\Commands\FreshCommand;
use ThisIsDevelopment\LaravelTenants\Commands\InstallCommand;
use ThisIsDevelopment\LaravelTenants\Commands\MigrateCommand;
use ThisIsDevelopment\LaravelTenants\Commands\MigrateMakeCommand;
use ThisIsDevelopment\LaravelTenants\Commands\RefreshCommand;
use ThisIsDevelopment\LaravelTenants\Commands\ResetCommand;
use ThisIsDevelopment\LaravelTenants\Commands\RollbackCommand;
use ThisIsDevelopment\LaravelTenants\Commands\StatusCommand;
use ThisIsDevelopment\LaravelTenants\Contracts\IdentifiesByAuthenticatedRequest;

class TenantsProvider extends ServiceProvider implements ProvidesConfiguration
{
    protected static $tenantModel = '';

    /**
     * @param string $tenantModel
     * @param Authenticatable[]
     */
    public static function setup(string $tenantModel): void
    {
        static::$tenantModel = $tenantModel;
    }

    public static function getDatabasePath()
    {
        return database_path('migrations/' . Str::snake(class_basename(static::$tenantModel)));
    }

    /**
     * @return Contracts\Tenant[]
     */
    public static function getTenants()
    {
        $class = static::$tenantModel;
        return $class::get()->all();
    }

    public function boot(): void
    {
        $this->listen([
            ConfigureDatabase::class => function(ConfigureDatabase $event)
            {
                $event->configuration = $this->configure($event->tenant);
            },
            ConfigureMigrations::class => function(ConfigureMigrations $event)
            {
                $event->path(static::getDatabasePath());
            },
            ConnectionResolving::class => function()
            {
                return $this;
            },
            Authenticated::class => function()
            {
                /** @var Environment $environment */
                $environment = app(Environment::class);
                $environment->identifyTenant(true, IdentifiesByAuthenticatedRequest::class);
            }
            //TODO: seeder(s)
        ]);

        Environment::macro('setAllowedTenants', function (?Collection $allowed) {
            $this->allowedTenants = $allowed;
        });

        Environment::macro('getAllowedTenants', function () {
            /** @var Environment $this */
            if (!$this->isIdentified()) {
                return collect([]);
            }

            if (!isset($this->allowedTenants)) {
                return collect([ $this->getTenant() ]);
            }

            return $this->allowedTenants;
        });
    }

    public function register(): void
    {
        $this->app->alias(Contracts\Tenant::class, Tenant::class);

        $this->app->resolving(ResolvesTenants::class, static function (ResolvesTenants $resolver) {
            assert(!empty(static::$tenantModel), 'Tenant class is not set, please call ' . __CLASS__ . '::setup in your AppServiceProvider');

            $resolver->addModel(static::$tenantModel);
            $resolver->registerDriver(IdentifiesByAuthenticatedRequest::class);

            return $resolver;
        });

        $this->registerCommands();
    }

    protected function registerCommands()
    {
        $this->app->extend('command.migrate', function () {
            try {
                return new MigrateCommand($this->app['migrator'], $this->app[Dispatcher::class]);
            } catch (ArgumentCountError $e) {
                return new MigrateCommand($this->app['migrator']);
            }
        });

        $this->app->extend('command.migrate.fresh', function () {
            return new FreshCommand;
        });

        $this->app->extend('command.migrate.install', function () {
            return new InstallCommand($this->app['migration.repository']);
        });

        $this->app->extend('command.migrate.make', function () {
            return new MigrateMakeCommand($this->app['migration.creator'], $this->app['composer']);
        });

        $this->app->extend('command.migrate.refresh', function () {
            return new RefreshCommand;
        });

        $this->app->extend('command.migrate.reset', function () {
            return new ResetCommand($this->app['migrator']);
        });
        $this->app->extend('command.migrate.rollback', function () {
            return new RollbackCommand($this->app['migrator']);
        });

        $this->app->extend('command.migrate.status', function () {
            return new StatusCommand($this->app['migrator']);
        });
    }

    protected function listen($events): void
    {
        foreach ($events as $event => $listener) {
            Event::listen($event, $listener);
        }
    }

    public function configure(Tenant $tenant): array
    {
        return $tenant->getTenantDBConfig();
    }
}
