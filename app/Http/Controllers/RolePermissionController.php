<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use App\Helpers\ResponseHelper;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{

   private $response;
   
   public function __construct(ResponseHelper $response)
   {
       $this->response = $response;
   }

   public function index() {
      $roles = Role::all();
      return $this->response->successResponse($roles);
   }

   public function rolePermissions(Request $request) {
      $user = User::where('username', $request['username'])->with('roles')->get()->first();

      if($user) {
         $data['fullname'] = $user->name;
         $data['roles'] = $user->roles;
         $data['permissions'] = $user->getAllPermissions();

         return $this->response->successResponse($data);
      }
      return $this->response->failedResponse("User not found");
   }

   public function pageData(Request $request)
   {

      try {
         $data = [];
         $data['users'] = User::with('permissions')->get();
         $data['roles'] = Role::all();
         $data['permissions'] = Permission::all();

         $data['rolepermissions'] = array();
         foreach($data['roles'] as $role) {
            $data['rolepermissions'][$role->name] = $role->getPermissionNames();
         }

         return $this->response->successResponse($data);
      }
      catch(Exception $e) {
         return $this->response->failedResponse($e);
      }
   }

   public function saveUser(Request $request)
   {
      try {
         $user = User::where('username', $request->username)->get()->first();
         $user->name = $request->fullname;
         $user->syncRoles($request->selectedRoles);
         $user->syncPermissions($request->selectedPermissions);
         $user->save();
         // foreach($request->selectedRoles as $rolename) {
         //    $role = Role::where('name',$rolename)->get->first();
         //    $user->assignRole($role);
         // }
         return $this->response->successResponse("OK");
      }
      catch(Exception $e) {
         return $this->response->failedRespons($e);
      }
   }
}
