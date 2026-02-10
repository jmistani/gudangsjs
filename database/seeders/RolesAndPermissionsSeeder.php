<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        Permission::create(['name' => 'edit articles']);
        Permission::create(['name' => 'delete articles']);
        Permission::create(['name' => 'publish articles']);
        Permission::create(['name' => 'unpublish articles']);

        // users
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'add users']);
        Permission::create(['name' => 'remove users']);

        // warehouse
        Permission::create(['name' => 'add parts']);
        Permission::create(['name' => 'edit parts']);
        Permission::create(['name' => 'post parts-consume']);
        Permission::create(['name' => 'edit parts-consume']);
        Permission::create(['name' => 'cancel parts-consume']);
        Permission::create(['name' => 'post parts-receive']);
        Permission::create(['name' => 'edit parts-receive']);
        Permission::create(['name' => 'cancel parts-receive']);
        Permission::create(['name' => 'post parts-send']);
        Permission::create(['name' => 'edit parts-send']);
        Permission::create(['name' => 'cancel parts-send']);
        Permission::create(['name' => 'post ticket']);
        Permission::create(['name' => 'edit ticket']);
        Permission::create(['name' => 'cancel ticket']);
        Permission::create(['name' => 'close ticket']);
        Permission::create(['name' => 'print warehouse-report']);        

        //kasir
        Permission::create(['name' => 'post payment']);        
        Permission::create(['name' => 'edit payment']);        
        Permission::create(['name' => 'cancel payment']);        
        Permission::create(['name' => 'post receivable']);        
        Permission::create(['name' => 'edit receivable']);        
        Permission::create(['name' => 'cancel receivable']);        
        Permission::create(['name' => 'print cashier-report']);        

        // or may be done by chaining
        $role = Role::create(['name' => 'kasir'])
            ->givePermissionTo([
               'post payment', 'edit payment', 'cancel payment',
               'post receivable', 'edit receivable', 'cancel receivable',
               'print cashier-report'
            ]);
        // create roles and assign created permissions

        // this can be done as separate statements
        $role = Role::create(['name' => 'staff-gudang']);
        $role->givePermissionTo('add parts');
        $role->givePermissionTo('edit parts');
        $role->givePermissionTo('post parts-consume');
        $role->givePermissionTo('edit parts-consume');
        $role->givePermissionTo('cancel parts-consume');
        $role->givePermissionTo('post parts-receive');
        $role->givePermissionTo('edit parts-receive');
        $role->givePermissionTo('cancel parts-receive');
        $role->givePermissionTo('post parts-send');
        $role->givePermissionTo('edit parts-send');
        $role->givePermissionTo('cancel parts-send');
        $role->givePermissionTo('post ticket');
        $role->givePermissionTo('edit ticket');
        $role->givePermissionTo('cancel ticket');
        $role->givePermissionTo('close ticket');
        $role->givePermissionTo('print warehouse-report');

        $role = Role::create(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());
    }
}