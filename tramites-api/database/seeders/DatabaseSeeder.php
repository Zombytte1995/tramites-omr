<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@omr.gob.sv'],
            [
                'name'     => 'Administrador OMR',
                'email'    => 'admin@omr.gob.sv',
                'password' => Hash::make('password'),
            ]
        );

        $this->call([
            InstitucionSeeder::class,
            TramiteSeeder::class,
        ]);
    }
}
