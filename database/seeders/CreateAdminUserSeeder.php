<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check for existing Admin user
        if (User::where('name', '=', 'NWWS-OI Controller Admin')->get()->isEmpty()) {
            $user = User::create([
                'name' => 'NWWS-OI Controller Admin',
                'email' => 'admin@localhost',
                'email_verified_at' => date('Y-m-d H:i:s'),
                'password' => bcrypt('password'),
                'remember_token' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            print "Admin user already exists, skipping.\n";
        }
    }
}
