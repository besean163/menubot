<?php

namespace Database\Seeders;

use Faker\Core\Number;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 10; $i++) {
            DB::table('users')->insert([
                'name' => 'user_name_' . $i,
                'type' => Str::random(10),
                'telId' => rand(10000, 99999),
                'telLogin' => Str::random(10),
                'login' => Str::random(10),
                'password' => Str::random(10),
            ]);
        }
    }
}
