<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        DB::table('courses')->insert([
            'name' => 'BackEnd Development',
            'price' => 2000000,
        ]);

        DB::table('courses')->insert([
            'name' => 'FrontEnd Development',
            'price' => 1000000,
        ]);
    }
}
