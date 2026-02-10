<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

class RolesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
      app()[PermissionRegistrar::class]->forgetCachedPermissions();
      
      $superadminrole = Role::firstOrCreate(['name' => 'super-admin']);
      $role = Role::firstOrCreate(['name' => 'admin']);

      User::create([
         'name'           => 'admin',
         'username'       => 'jmistani',
         'email'          => 'jmistani@gmail.com',
         'password'       => bcrypt('emcaiar'),
         'remember_token' => Str::random(60),
         'role_id'        => $role->id,
      ]);

      $permissions = array();
      $permissions = [ 
         Permission::create(['name' => 'manage all'])
      ];
      $role->syncPermissions($permissions);
      $superadminrole->syncPermissions($permissions);

      $user = User::where('username','jmistani')->firstOrFail();
      $user->assignRole('super-admin');
      $user->assignRole('admin');


      $rolestaff = Role::firstOrCreate(['name' => 'staff']);

      $role = Role::where('name', 'staff')->firstOrFail();
      User::create([
         'name'           => 'cencen',
         'username'       => 'cencen',
         'email'          => 'cencen@gmail.com',
         'password'       => bcrypt('abcdefg'),
         'remember_token' => Str::random(60),
         'role_id'        => $role->id,
      ]);
      $user = User::where('username','cencen')->firstOrFail();
      $user->assignRole('staff');

      $rolekasir1 = Role::firstOrCreate(['name' => 'kasir1']);
      $permissions = array();
      $permissions = [ 
       Permission::create(['name' => 'manage penerimaan']),
       ];
      $rolestaff->syncPermissions($permissions);

      $role = Role::where('name', 'kasir1')->firstOrFail();
      User::create([
         'name'           => 'hery',
         'username'       => 'hery',
         'email'          => 'hery@gmail.com',
         'password'       => bcrypt('abcdefg'),
         'remember_token' => Str::random(60),
         'role_id'        => $role->id,
      ]);

      $rolekasir2 = Role::firstOrCreate(['name' => 'kasir2']);
      $user = User::where('username','hery')->firstOrFail();
      $user->assignRole('kasir1');

      $role = Role::where('name', 'kasir2')->firstOrFail();
      User::create([
         'name'           => 'tanawi',
         'username'       => 'tanawi',
         'email'          => 'tanawi@gmail.com',
         'password'       => bcrypt('abcdefg'),
         'remember_token' => Str::random(60),
         'role_id'        => $role->id,
      ]);

      $user = User::where('username','tanawi')->firstOrFail();
      $user->assignRole('kasir2');

      $permissions = array();
      $permissions = [ 
       Permission::create(['name' => 'beri pinjaman']),
       Permission::create(['name' => 'keluar kas']),
       Permission::create(['name' => 'terima kas']),
       Permission::create(['name' => 'manage kasir'])
       ];
      $rolekasir1->syncPermissions($permissions);

      $permissions = array();
      $permissions = [ 
       'beri pinjaman',
       'keluar kas',
       'manage kasir'
       ];
      $rolekasir2->syncPermissions($permissions);


      $role = Role::firstOrCreate(['name' => 'staff-gudang']);
      $role = Role::where('name', 'staff-gudang')->firstOrFail();
      User::create([
         'name'           => 'jackie',
         'username'       => 'jackie',
         'email'          => 'jackie@gmail.com',
         'password'       => bcrypt('abcdefg'),
         'remember_token' => Str::random(60),
         'role_id'        => $role->id,
      ]);
      $user = User::where('username','jackie')->firstOrFail();
      $user->assignRole('staff-gudang');

      $permissions = array();
      $permissions = [ 
         Permission::create(['name' => 'list inventory']),
         Permission::create(['name' => 'create inventory']),
         Permission::create(['name' => 'edit inventory']),
         Permission::create(['name' => 'receive inventory']),
         Permission::create(['name' => 'checkout inventory']),
         Permission::create(['name' => 'manage inventory'])
      ];
      $role->syncPermissions($permissions);

    
     }
}
