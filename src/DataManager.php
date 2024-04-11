<?php

namespace Kerigard\LaravelData;

use Closure;

class DataManager
{
    public static $models = [];

    public static $tables = [];

    /**
     * @template T
     *
     * @param  class-string<T>  $class
     * @param  \Closure(T): \Kerigard\LaravelData\Data  $callback
     */
    public static function model(string $class, Closure $callback): void
    {
        if (app()->runningInConsole()) {
            static::$models[$class] = $callback;
        }
    }

    /**
     * @param  \Closure(\Kerigard\LaravelData\Models\DataModel): \Kerigard\LaravelData\Data  $callback
     */
    public static function table(string|array $table, Closure $callback): void
    {
        if (app()->runningInConsole()) {
            static::$tables[] = [is_string($table) ? compact('table') : $table, $callback];
        }
    }
}
