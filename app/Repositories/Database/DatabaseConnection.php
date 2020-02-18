<?php

namespace App\Repositories\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DatabaseConnection
{
    /**
     * @var $schema Schema
     */
    private $schema;

    public function __construct($connection)
    {
        $this->schema = \Schema::connection($connection);
    }

    public function addColumnIfNotExist($table, $column, $callback)
    {
        if ($this->schema->hasTable($table)) {
            if ($this->schema->hasColumn($table, $column) == false) {
                $this->schema->table($table, $callback);
            }
        }
    }

    public function createTableIfNotExist($tableName, $callback)
    {
        if ($this->schema->hasTable($tableName) == false) {
            $this->schema->create($tableName, $callback);
        }
    }

    public function dropColumnIfExist($tableName, $column)
    {
        if ($this->schema->hasColumn($tableName, $column)) {
            $this->schema->table($tableName, function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }

    public function schema()
    {
        return $this->schema;
    }

    public function hasTable($tableName)
    {
        return $this->schema->hasTable($tableName);
    }

    public function dropIfExists($tableName)
    {
        $this->schema->dropIfExists($tableName);
    }

    public function unprepared($raw)
    {
        $this->schema->getConnection()->unprepared($raw);
    }
}
