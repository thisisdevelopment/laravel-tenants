<?php

namespace ThisIsDevelopment\LaravelTenants\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\Console\Input\InputInterface;
use Tenancy\Environment;
use Tenancy\Identification\Concerns\AllowsTenantIdentification;
use Tenancy\Identification\Contracts\Tenant;
use Tenancy\Identification\Drivers\Queue\Events\Processing;
use Tenancy\Tenant\Events\Created;
use Tenancy\Tenant\Events\Deleted;
use ThisIsDevelopment\LaravelTenants\Contracts\TenantAuth;

trait ProvidesTenant
{
    use AllowsTenantIdentification;
    use TenantScoped;

    public static function bootProvidesTenant()
    {
        static::created(static function(Tenant $tenant) {
            event(new Created($tenant));
        });

        static::deleted(static function(Tenant $tenant) {
            event(new Deleted($tenant));
        });

        static::bootTenantScoped();
    }

    public function getTenantDBSuffix(): string
    {
        return $this->getTenantKey();
    }

    public function getTenantDBConfig(): array
    {
        $config = config('database.connections.' . config('database.default'));
        $config['database'] .= '__' . $this->getTenantDBSuffix();
        return $config;
    }

    public function getAllowedTenants(): ?Collection
    {
        return null;
    }

    public function getDefaultTenant()
    {
        return $this->getTenantKey();
    }

    public function getByTenantKey($key): ?Tenant
    {
        return static::query()
            ->where($this->getTenantKeyName(), $key)
            ->first();
    }

    public function scopeTenant(Builder $query, \ThisIsDevelopment\LaravelTenants\Contracts\Tenant $tenant): void
    {
        $allowed = app(Environment::class)->getAllowedTenants()->pluck($this->getTenantKeyName())->all();
        $query->whereIn("{$this->getTable()}.{$this->getTenantKeyName()}", $allowed);
    }

    public function tenantIdentificationByConsole(InputInterface $input): ?Tenant
    {
        if (app()->runningInConsole() && $input->hasParameterOption('--tenant')) {
            return $this->getByTenantKey($input->getParameterOption('--tenant'));
        }

        return null;
    }

    public function tenantIdentificationByAuthenticatedRequest(Request $request): ?Tenant
    {
        $user = $request->user();
        if (! ($user instanceof TenantAuth)) {
            return null;
        }

        $allowed = $user->getAllowedTenants();
        if ($allowed !== null && $allowed->isEmpty()) {
            return null;
        }

        $default = $user->getDefaultTenant();
        if ($allowed === null) {
            assert($default !== null, 'Default should not be null when allowed === null');
            return $this->getByTenantKey($default);
        }

        $environment = app(Environment::class);
        $environment->setAllowedTenants($allowed);

        $selected = $request->header('x-selected-tenant');
        if ($selected === null) {
            $selected = $request->cookie('selected-tenant');
        }
        if ($selected === null) {
            $selected = $default;
        }

        if (!in_array($selected, $allowed->pluck($this->getTenantKeyName())->all(), false)) {
            throw new UnauthorizedException('Invalid tenant specified');
        }

        return $this->getByTenantKey($selected);
    }

    public function tenantIdentificationByQueue(Processing $event): ?Tenant
    {
        if (empty($event->tenant_key)) {
            return null;
        }

        return $this->getByTenantKey($event->tenant_key);
    }
}
