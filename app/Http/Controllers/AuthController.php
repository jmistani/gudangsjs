<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private $response;

    public function __construct(ResponseHelper $response)
    {
        $this->response = $response;
    }

    /**
     * Register a new user.
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'username' => [
                'required',
                'string',
                'max:191',
                'unique:users,username',
            ],
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8',
            'c_password' => 'required|same:password',
        ]);

        $user = User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = auth('api')->login($user);

        return response()->json([
            'message' => 'Successfully created user!',
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'userData' => $this->buildData($user),
        ], 201);
    }

    /**
     * Login using username and password.
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'remember_me' => 'sometimes|boolean',
        ]);

        // JWT authentication only needs username/password
        $loginCredentials = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ];

        $token = auth('api')->attempt($loginCredentials);

        if (!$token) {
            return response()->json([
                'message' => 'Invalid username or password',
            ], 401);
        }

        $user = auth('api')->user();

        return $this->response->successResponse([
            'accessToken' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('api')
                ->factory()
                ->getTTL() * 60,
            'userData' => $this->buildData($user),
        ]);
    }

    /**
     * Refresh JWT access token.
     * POST /api/auth/refresh
     *
     * Requires Authorization: Bearer <token>
     */
    public function refresh(Request $request)
    {
        try {
            $guard = auth('api');

            $newToken = $guard->refresh();

            return response()->json([
                'accessToken' => $newToken,
                'token_type' => 'Bearer',
                'expires_in' => $guard
                    ->factory()
                    ->getTTL() * 60,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to refresh token',
            ], 401);
        }
    }

    /**
     * Get authenticated user.
     * GET /api/auth/user
     */
    public function user(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json(
            $this->buildData($user)
        );
    }

    /**
     * Get authenticated user.
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        return response()->json(
            $this->buildData($user)
        );
    }

    /**
     * Build user data with Spatie permissions.
     */
    public function buildData($user)
    {
        $permissions = $user->getAllPermissions()
            ->pluck('name');

        $abilities = [];

        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission, 2);

            if (count($parts) === 2) {
                $abilities[] = [
                    'action' => $parts[0],
                    'subject' => $parts[1],
                ];
            }
        }

        $user->setAttribute('ability', $abilities);

        $user->setAttribute(
            'role',
            $user->getRoleNames()
        );

        return $user;
    }

    /**
     * List users with searching, sorting and pagination.
     * GET /api/auth/users
     */
    public function users(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:191',
            'sortBy' => [
                'nullable',
                Rule::in([
                    'id',
                    'username',
                    'name',
                    'email',
                    'created_at',
                ]),
            ],
            'sortDesc' => 'nullable|boolean',
            'perPage' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = User::query();

        if (!empty($validated['q'])) {
            $query->where(
                'name',
                'LIKE',
                '%' . $validated['q'] . '%'
            );
        }

        $sortBy = $validated['sortBy']
            ?? 'created_at';

        $sortDesc = $request->boolean('sortDesc');

        $query->orderBy(
            $sortBy,
            $sortDesc ? 'DESC' : 'ASC'
        );

        $perPage = $validated['perPage'] ?? 15;

        $users = $query->paginate($perPage);

        $users->getCollection()->transform(
            function ($user) {
                return $this->buildData($user);
            }
        );

        return $this->response->successResponse($users);
    }

    /**
     * Logout and invalidate current JWT.
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            auth('api')->logout();

            return $this->response->successResponse([
                'message' => 'Successfully logged out',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Logout failed or token invalid',
            ], 401);
        }
    }
}
