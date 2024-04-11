# Fill the database with required data

<p align="center">
  <a href="https://github.com/Kerigard/laravel-data/actions"><img src="https://github.com/Kerigard/laravel-data/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/Kerigard/laravel-data"><img src="https://img.shields.io/packagist/dt/Kerigard/laravel-data" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/Kerigard/laravel-data"><img src="https://img.shields.io/packagist/v/Kerigard/laravel-data" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/Kerigard/laravel-data"><img src="https://img.shields.io/packagist/l/Kerigard/laravel-data" alt="License"></a>
</p>

This package adds an alternative way to populate the database regarding migrations and seeders.

## Installation

Install package via composer:

``` bash
composer require kerigard/laravel-data
```

## Usage

Implement the `MustFillData` interface into the model and set the required data.

```php
use Illuminate\Database\Eloquent\Model;
use Kerigard\LaravelData\Contracts\MustFillData;
use Kerigard\LaravelData\Data;

class Role extends Model implements MustFillData
{
    public function data(): Data
    {
        return Data::make([
            ['id' => 1, 'name' => 'Admin'],
            ['id' => 2, 'name' => 'User'],
        ]);
    }
}
```

Run artisan command.

```bash
php artisan db:data
```

As a result of executing the command, all unnecessary data will be deleted from the table and new ones will be inserted if they do not already exist.

You can also disable deleting existing data from the table.

```php
public function data(): Data
{
    return Data::make([
        // ...
    ], false);
}
```

Use the `withoutEvents` method to disable all model events while a command is running. As a parameter, you can pass a list of events that should not be ignored.

```php
class MyModel extends Model implements MustFillData
{
    public static function booted()
    {
        static::creating(function (MyModel $model) {
            $model->slug = Str::slug($model->name);
        });
    }

    public function data(): Data
    {
        return Data::make([
            // ...
        ])->withoutEvents(['eloquent.creating: '.MyModel::class]);
    }
}
```

If the model is present in the vendor package or does not exist, fill in the data in the `AppServiceProvider`.

```php
use Kerigard\LaravelData\Data;
use Kerigard\LaravelData\DataManager;

public function boot()
{
    DataManager::model(VendorModel::class, fn () => Data::make([
        // ...
    ]));

    DataManager::table('role_user', fn () => Data::make([
        // ...
    ]));

    DataManager::table([
        'connection' => 'db2',
        'table' => 'permissions',
        'primaryKey' => 'id',
        'timestamps' => true,
    ], fn () => Data::make([
        // ...
    ]));
}
```

## Changelog

Please see the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

MIT. Please see the [LICENSE FILE](LICENSE.md) for more information.
