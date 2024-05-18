<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!$token = auth('api')->attempt($validator->validated())) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['Email or password is incorrect']
                ]
            ], 401);
        }

        if (auth()->user()->email_verified_at == null) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['Email is not verified']
                ]
            ], 401);
        }

        if (auth()->user()->status == 'banned') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['User is banned']
                ]
            ], 401);
        }

        $data = [
            'user_email' => auth()->user()->id,
            'random' => rand() . time(),
            'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
        ];

        $refreshToken = JWTAuth::getJWTProvider()->encode($data);

        User::where('id', auth()->user()->id)->update([
            'refresh_token' => $refreshToken,
        ]);

        return $this->createNewToken($token, $refreshToken);
    }

    // Login with Google
    public function loginWithGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idToken' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user_google = json_decode(file_get_contents("https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=" . $request->idToken));

        if (!$user_google) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid google token',
            ], 401);
        }

        $user = User::where('email', $user_google->email)->first();

        if ($user) {
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $user_google->sub,
                ]);
            }
            if (!$user->avatar) {
                $user->update([
                    'avatar' => $user_google->picture,
                ]);
            }
            if (!$user->email_verified_at) {
                $user->update([
                    'email_verified_at' => now(),
                ]);
            }
            $token = auth()->login($user);
            if (auth()->user()->status == 'banned') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                    'errors' => [
                        'email' => ['User is banned']
                    ]
                ], 401);
            }
            $data = [
                'user_email' => auth()->user()->id,
                'random' => rand() . time(),
                'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
            ];

            $refreshToken = JWTAuth::getJWTProvider()->encode($data);

            User::where('id', auth()->user()->id)->update([
                'refresh_token' => $refreshToken,
            ]);
            return $this->createNewToken($token, $refreshToken);
        } else {
            $user = User::create([
                'avatar' => $user_google->picture,
                'fullname' => $user_google->name,
                'email' => $user_google->email,
                'password' => bcrypt($user_google->kid),
                'google_id' => $user_google->sub,
                'email_verified_at' => now(),
            ]);

            $token = auth()->login($user);
            $data = [
                'user_email' => auth()->user()->id,
                'random' => rand() . time(),
                'exp' => now()->addMinutes(config('jwt.refresh_ttl'))->timestamp,
            ];

            $refreshToken = JWTAuth::getJWTProvider()->encode($data);

            User::where('id', auth()->user()->id)->update([
                'refresh_token' => $refreshToken,
            ]);

            return $this->createNewToken($token, $refreshToken);
        }
    }

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
        ));

        return response()->json([
            "status" => true,
            "message" => "User registered successfully",
            "data" => $user
        ], 201);
    }


    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout(true);

        return response()->json([
            "status" => true,
            "message" => "User Logged out successfully"
        ]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $refreshToken = validator(request()->all(), [
            'refreshToken' => 'required|string'
        ])->validated()['refreshToken'];



        try {
            $decoded = JWTAuth::getJWTProvider()->decode($refreshToken);

            if (User::where('refresh_token', $refreshToken)->exists()) {
                if ($decoded['exp'] < now()->timestamp) {
                    return response()->json([
                        "status" => false,
                        "message" => "Refresh token expired",
                    ], 401);
                } else {

                    $user = User::find($decoded['user_email']);
                    if (!$user) {
                        return response()->json([
                            "status" => false,
                            "message" => "User not found",
                        ], 404);
                    }
                    $token = auth()->login($user);
                    return $this->createNewRefreshToken($token, $refreshToken);
                }
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Invalid refresh token",
                ], 401);
            }
        } catch (JWTException $e) {
            return response()->json([
                "status" => false,
                "message" => "Invalid refresh token",
            ], 401);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        $user = User::with('role')->find(auth()->user()->id);

        // Trả về dữ liệu đã được định dạng lại thông qua Accessor
        return response()->json([
            "status" => true,
            "message" => "User profile fetched successfully",
            "data" => $user
        ]);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token, $refreshToken)
    {
        return response()->json([
            "status" => true,
            "message" => "Login successful",
            "data" => [
                "user" => User::with('role')->find(auth()->user()->id),
                "token" => [
                    "accessToken" =>  $token,
                    "refreshToken" => $refreshToken
                ],
            ]
        ]);
    }

    protected function createNewRefreshToken($token, $refreshToken)
    {
        return response()->json([
            "status" => true,
            "message" => "Refresh token successful",
            "data" => [
                "accessToken" => $token,
            ]
        ]);
    }

    public function changePassWord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }
        $userId = auth()->user()->id;

        $user = User::where('id', $userId)->update(
            ['password' => bcrypt($request->new_password)]
        );

        return response()->json([
            'status' => true,
            'message' => 'User successfully changed password',
            'user' => $user,
        ], 201);
    }
}
