<?php

use Illuminate\Database\Seeder;

class DefaultData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createUser();
    }

    private function createUser()
    {
        $user = \App\Models\User::query()->where("username", "admin")->first();
        if (empty($user)) {
            $user = new \App\Models\User();
            $user->name = "Admin";
            $user->username = "admin";
            $user->password = "admin";
            $user->save();
        }
    }
}
