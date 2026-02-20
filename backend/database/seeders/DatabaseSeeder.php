<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::where('email', 'admin@test.com')->first();
        
        if ($user) {
            $user->update(['password' => Hash::make('password')]);
        } else {
            User::create([
                'email' => 'admin@test.com',
                'password' => Hash::make('password'),
                'uuid' => '' . (string) \Illuminate\Support\Str::uuid()
            ]);
        }
    }
}
