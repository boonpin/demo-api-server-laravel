<?php

namespace App\Repositories;

use App\Repositories\Database\DatabaseConnection;

class DatabaseRepo
{
    private static $_instances = [];

    public static function defaultConnection()
    {
        return self::connection(config('database.default'));
    }

    /**
     * @param $name
     * @return DatabaseConnection
     */
    public static function connection($name): DatabaseConnection
    {
        if (empty(self::$_instances[$name])) {
            $c = new DatabaseConnection($name);
            self::$_instances[$name] = $c;
        }
        return self::$_instances[$name];
    }
}
