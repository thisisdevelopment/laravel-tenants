<?php

namespace ThisIsDevelopment\LaravelTenants;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Tenancy\Affects\Connections\Contracts\ProvidesConfiguration;
use Tenancy\Affects\Connections\Events\Resolving as ConnectionResolving;
use Tenancy\Environment;
use Tenancy\Hooks\Database\Events\Drivers\Configuring as ConfigureDatabase;
use Tenancy\Hooks\Migration\Events\ConfigureMigrations;
use Tenancy\Identification\Contracts\ResolvesTenants;
use Tenancy\Identification\Contracts\Tenant;
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

    public function register(): void
    {
        $this->app->alias(\ThisIsDevelopment\LaravelTenants\Contracts\Tenant::class, Tenant::class);

        $this->app->resolving(ResolvesTenants::class, static function (ResolvesTenants $resolver) {
            assert(!empty(static::$tenantModel), 'Tenant class is not set, please call ' . __CLASS__ . '::setup in your AppServiceProvider');

            $resolver->addModel(static::$tenantModel);
            $resolver->registerDriver(IdentifiesByAuthenticatedRequest::class);

            return $resolver;
        });
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
                    $event->path(app()->databasePath('tenants/migrations'));
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

    protected function listen($events): void
    {
        foreach ($events as $event => $listener) {
            Event::listen($event, $listener);
        }
    }

    public function configure(Tenant $tenant): array
    {
        $config = config('database.connections.' . config('database.default'));
        $config['database'] .= '__' . $tenant->getTenantKey();
        return $config;
    }
}
