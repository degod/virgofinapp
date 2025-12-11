<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an test user if not exists
        if (!User::where('email', 'test@mail.com')->exists()) {
            User::factory()->create([
                'name' => 'Test Mail',
                'email' => 'test@mail.com',
                'balance' => 2500.0,
                'password' => bcrypt('password'),
            ]);
        }
        $this->command->info('Test user created...');
        $this->command->info('U: test@mail.com');
        $this->command->info("P: password\n");

        // Create 5 regular users
        User::factory(5)->create();
        $this->command->info('Created 5 other regular users...');
        $this->command->info("P: password\n");
    }
}
