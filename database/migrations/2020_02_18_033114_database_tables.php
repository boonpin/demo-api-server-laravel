<?php

use App\Constants\DatabaseTable;
use App\Repositories\Database\DatabaseConnection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class DatabaseTables extends Migration
{
    public function up()
    {
        $connection = \App\Repositories\DatabaseRepo::defaultConnection();

        $this->createFrameworkTables($connection);
        $this->createSystemTable($connection);
    }

    public function down()
    {
        // DO Nothing
    }

    public function createFrameworkTables(DatabaseConnection $connection)
    {
        $connection->createTableIfNotExist(DatabaseTable::CORE_JOBS, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue');
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
            $table->index(['queue', 'reserved_at']);
        });

        $connection->createTableIfNotExist(DatabaseTable::CORE_FAILED_JOBS, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        $connection->createTableIfNotExist(DatabaseTable::CORE_SESSIONS, function (Blueprint $table) {
            $table->string('id', 128);
            $table->uuid('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
            $table->unique('id');
        });
    }

    public function createSystemTable(DatabaseConnection $connection)
    {
        $connection->createTableIfNotExist(DatabaseTable::SYS_SETTING, function (Blueprint $table) {
            $table->string('key', 128);
            $table->text('value')->nullable();
            $table->nullableTimestamps();
            $table->primary('key');
        });

        $connection->createTableIfNotExist(DatabaseTable::USER, function (Blueprint $table) {
            $table->uuid('id');
            $table->string('username', 128);
            $table->text("password");
            $table->string('name', 64)->nullable();
            $table->string('email', 64)->nullable();
            $table->string('contact', 32)->nullable();
            $table->string('identity_id', 64)->nullable();
            $table->boolean('is_disabled')->default(false);
            $table->nullableTimestamps();
            $table->primary('id');
            $table->unique('username');
        });
    }

}
