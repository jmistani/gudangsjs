<?php
namespace App\Models;
use Spatie\Permission\Models\Permission;

class MyPermissions extends Permission implements PermissionContract 
{
   use HasRoles;
   use RefreshesPermissionCache;
   
   protected static function getAbilities(array $params = []): Collection
   {
      $permissions = Static::getPermissions();
      $abilities = array();
      foreach ($permissions as $perm)
      {
         $str= explode(" ",$perm); 
         $ability = ["action" => $str[0], "subject" => $str[1]];
         array_push($ability, $abilities);
      }
      return $abilities;
   }
}