<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

    $this->call([
        RoleSeeder::class,
        PermissionSeeder::class,
        HourTypesSeeder::class,
        DaysSeeder::class,
        CategorySeeder::class,
    ]);

    // Create Sebastian Gonnsen if doesn't exist
    $sebastian = User::where('email', 'sg@webhusetballum.dk')->first();
    if (!$sebastian) {
        $sebastian = User::factory()->create([
            'name' => 'Sebastian Gonnsen',
            'email' => 'sg@webhusetballum.dk',
            'password' => bcrypt('password'),
        ]);
        $sebastian->assignRole('Administrator');
    }

    // Create Christopher Sutherland if doesn't exist
    $christopher = User::where('email', 'chrissu360@gmail.com')->first();
    if (!$christopher) {
        $christopher = User::factory()->create([
            'name' => 'Christopher Sutherland',
            'email' => 'chrissu360@gmail.com',
            'password' => bcrypt('password'),
        ]);
        $christopher->assignRole('Administrator');
    }
    }
    
    /**
     * Seed the application's database.
     */
    // public function run(): void
    // {
    //     //create a sg@webhusetballum.dk user with password password, name Sebastian Gonnsen
    //     //find by email

    //     $user = User::where('email', 'sg@webhusetballum.dk')->first();

    //     $this->call(RoleSeeder::class);
    //     $this->call(PermissionSeeder::class);
    //     $this->call(HourTypesSeeder::class);
    //     $this->call(DaysSeeder::class);
    //     $this->call(CategorySeeder::class);

    //     if (! $user) {

    //         $admin = \App\Models\User::factory()->create([
    //             'name' => 'Sebastian Gonnsen',
    //             'email' => 'sg@webhusetballum.dk',
    //             'password' => bcrypt('password'),
    //         ]);

    //         $admin->assignRole('Administrator');
    //     }
    // }
}
