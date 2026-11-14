<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //La clave del administrador se toma del .env (ADMIN_PASSWORD), nunca del código.
        //Si no está definida, se genera una aleatoria y se muestra una sola vez en consola.
        $password = env('ADMIN_PASSWORD');
        if (empty($password)) {
            $password = Str::random(16);
            $this->command?->warn("ADMIN_PASSWORD no está en el .env. Clave temporal del administrador: {$password}");
        }

        $user = User::create([
            'name' => env('ADMIN_NAME', 'Santiago'),
            'email' => env('ADMIN_EMAIL', 'admin@gmail.com'),
            'password' => Hash::make($password)
        ]);

        //Usuario administrador
        $rol = Role::create(['name' => 'administrador']);
        $permisos = Permission::pluck('id','id')->all();
        $rol->syncPermissions($permisos);
        //$user = User::find(1);
        $user->assignRole('administrador');
    }
}
