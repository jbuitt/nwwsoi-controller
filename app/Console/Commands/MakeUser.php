<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class MakeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nwwsoi-controller:make:user {name} {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userName = $this->argument('name');
        $userEmail = $this->argument('email');
        $userPassword = $this->argument('password');
        // Create the user
        try {
            User::create([
                'name' => $userName,
                'email' => $userEmail,
                'password' => Hash::make($userPassword),
            ]);
        } catch (Exception $e) {
            print "Error: User creation failed: " . $e->getMessage() . "\n";
            return 1;
        }
        print "User created.\n";
        return 0;
    }
}
