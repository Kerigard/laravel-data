<?php

namespace Kerigard\LaravelData\Models;

use Illuminate\Database\Eloquent\Model;

class DataModel extends Model
{
    protected static $parameters = [];

    protected $primaryKey = null;

    public $incrementing = false;

    public $timestamps = false;

    public static function bind(array $parameters): static
    {
        static::$parameters = $parameters;

        return new static();
    }

    public function getConnectionName()
    {
        return static::$parameters['connection'] ?? parent::getConnectionName();
    }

    public function getTable()
    {
        return static::$parameters['table'] ?? parent::getTable();
    }

    public function getKeyName()
    {
        return static::$parameters['primaryKey'] ?? parent::getKeyName();
    }

    public function usesTimestamps()
    {
        return static::$parameters['timestamps'] ?? parent::usesTimestamps();
    }
}
