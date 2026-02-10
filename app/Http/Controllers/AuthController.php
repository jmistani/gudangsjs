<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;
use App\Helpers\ResponseHelper;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Session;
class AuthController extends Controller
{

   private $response;

   public function __construct(ResponseHelper $response)
   {
       $this->response = $response;
   }

   /**
    * Create user
    *
    * @param  [string] name
    * @param  [string] email
    * @param  [string] password
    * @param  [string] password_confirmation
    * @return [string] message
    */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email'=>'required|string|unique:users',
            'password'=>'required|string',
            'c_password' => 'required|same:password'
        ]);

        $user = new User([
            'name'  => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if($user->save()){
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->plainTextToken;

            return response()->json([
            'message' => 'Successfully created user!',
            'accessToken'=> $token,
            ],201);
        }
        else{
            return response()->json(['error'=>'Provide proper details']);
        }
    }

/**
 * Login user and create token
*
* @param  [string] email
* @param  [string] password
* @param  [boolean] remember_me
*/

   public function login(Request $request)
   {
      $request->validate([
      'username' => 'required|',
      'password' => 'required|string',
      'remember_me' => 'boolean'
      ]);

      $credentials = request(['username','password']);
      if(!Auth::attempt($credentials))
      {
         return response()->json([
               'message' => 'Unauthorized'
         ],200);
      }

      $user = $request->user();
      $user = $this->buildData($user);
      $tokenResult = $user->createToken('Personal Access Token');
      $token = $tokenResult->plainTextToken;

      return $this->response->successResponse([
      'accessToken' =>$token,
      'token_type' => 'Bearer',
      'userData' => $user
      ]);
   }
   
   public function refresh(Request $request)
   {
       $user = $request->user();
       $user->tokens()->delete();
       return response()->json(['token' => $user->createToken($user->name)->plainTextToken]);
   }

   public function buildData($user)
   {
      $permissions = $user->getAllPermissions()->pluck('name');
      $abilities = [];
      foreach ($permissions as $perm)
      {
         $str= explode(" ",$perm); 
         $ability = ["action" => $str[0], "subject" => $str[1]];
         //array_push($ability, $abilities);
         $abilities[] = $ability;
      }
      $user['ability'] = $abilities;
      $user['role'] = $user->getRoleNames();
      return $user;
   }
   /**
    * Get the authenticated User
   *
   * @return [json] user object
   */
  public function user(Request $request)
  {
   $user = $request->user();
     if($user != null)
        $user = $this->buildData($user);
     return response()->json($user);
  }

  
   /**
    * Get the authenticated User
   *
   * @return [json] user object
   */
  public function me(Request $request)
  {
   $user = $request->user();
     if($user != null)
        $user = $this->buildData($user);
      else
         return response()->json([
            'message' => 'Unauthorized'
         ],401);

     return response()->json($user);
  }

  public function users(Request $request)
  {
      //public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null);
      $user = User::latest();
      if ($request->q) {
          $user = $user->where('name', 'LIKE', '%'. $request->q .'%');
      }
      if ($request->sortBy) {
          $user = $user->orderBy($request->sortBy, $request->sortDesc ? 'DESC' : 'ASC');
      }
      $user = $user->paginate($request->perPage, '*', 'page', $request->page);

      foreach($user as $key => $u)
      {
         $user[$key] = $this->buildData($u);
      }
      return $this->response->successResponse($user);
  }

  /**
    * Logout user (Revoke the token)
   *
   * @return [string] message
   */
   public function logout(Request $request)
   {
      // $user = $request->user();
      // if($user != null) {
      //    $user = $this->buildData($user);
      //    $user->tokens()->delete();
      //    $user->forgetCachedPermissions();
      // }

        try {
            Session::flush();
            $success = true;
            $message = 'Successfully logged out';
        } catch (\Illuminate\Database\QueryException $ex) {
            $success = false;
            $message = $ex->getMessage();
        }
      
        if($success) 
         return $this->response->successResponse([
            'message' => $message
         ]);
         else
         return $this->response->failedResponse([
            'message' => $message
         ]);
   }
}