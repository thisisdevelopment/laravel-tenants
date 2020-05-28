# Laravel tenants

A package to provide easy multi tenancy in your laravel application

After a simple setup (see below) you will have a fully functional multi tenancy app which out of the box:
- allows for many-to-many relationship between your users and tenant objects. 
- supports easy switching between multiple tenants for a user which has access to multiple tenants (via `x-selected-tenant` header or via `selected-tenant` cookie)
- once switched by default only allows access to a single tenant object and you can easily implement tenant scoped models (see setup #4)
- allows to use tinker (or any other cli command) with a specific tenant (--tenant=<tenantId>)
- all jobs submitted from a context where a tenant is selected will use that tenant when executing from a queue
- when a tenant model is created the tenant database is automatically created

For an example see https://github.com/thisisdevelopment/laravel-tenants-example

# Todo

- integrate with laravel-test-snapshot
- proper seed support
- support sqlite
- cleanup
- .. 

# Setup 

To setup multi tenancy with this package you'll need the following steps

### 1) include this package

```shell script
composer require thisisdevelopment/laravel-tenants
```

### 2) find the model which serves as tenant (eg: customer/user) and implement our `Tenant` contract  

```php
use Illuminate\Database\Eloquent\Model;
use ThisIsDevelopment\LaravelTenants\Contracts\Tenant;
use ThisIsDevelopment\LaravelTenants\Traits\ProvidesTenant;

class Customer extends Model implements Tenant
{
    use ProvidesTenant;

}
```
and register it in your `AppServiceProvider` class
```php
use Illuminate\Support\ServiceProvider;
use ThisIsDevelopment\LaravelTenants\TenantsProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        TenantsProvider::setup(Customer::class);
    }
}
```

### 3) find the models which should live in tenant specific databases and use our `onTenantDB` trait

```php
use Illuminate\Database\Eloquent\Model;
use ThisIsDevelopment\LaravelTenants\Traits\OnTenantDB;

class Test extends Model
{
    use OnTenantDB;
}
```

and move all of the migrations / seeders for these models to 
the `database/migrations/<tenant model name>` / `database/seeds/<tenant model name>` so they will only be used for tenant databases.  


### 4) find the models which should be restricted once you switched to the tenant db and use our `TenantScoped` trait

```php
use Illuminate\Database\Eloquent\Model;
use ThisIsDevelopment\LaravelTenants\Traits\TenantScoped;
use Illuminate\Database\Eloquent\Builder;

class Settings extends Model
{
    use TenantScoped;
  
    public function scopeTenant(Builder $query, Customer $tenant): void
    {
       // custom filtering to limit this to what the current tenant is allowed to see 
    }
}
```

### 5) (optional) find the model which you authenticate with and let it be responsible for providing the allowedTenants + defaultTenant by implementing the `TenantAuth` interface by using the `ProvidesTenantAuth` trait

```php
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use ThisIsDevelopment\LaravelTenants\Contracts\TenantAuth;
use ThisIsDevelopment\LaravelTenants\Traits\ProvidesTenantAuth;

class User extends Authenticatable implements TenantAuth
{
    use ProvidesTenantAuth;
    
    public function getAllowedTenants(): ?Collection
    {
        return $this->customers()->get();
    }

    public function getDefaultTenant()
    {
        return $this->getAllowedTenants()->first()->id;
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_users', 'user_id', 'customer_id');
    }

    public function scopeTenant(Builder $query, Customer $tenant): void
    {
        $query->whereIn($this->getKeyName(), $tenant->users()->get()->pluck('id')->all());
    }
}

```
