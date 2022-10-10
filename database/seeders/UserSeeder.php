<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
          User::create([
            'name' => 'user',
            'email' => 'user@mail.com',
            'password' => 1234,
            'api_token' => 'UseRwl3lDsXkh9YMbDPkv0Abdz7Vgppc9Oq239GSln9noNJEn4beAdNYPQz1'
        ]);
    }
}
