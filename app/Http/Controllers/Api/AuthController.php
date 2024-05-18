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

        $customMessages = [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.string' => 'Mật khẩu phải là một chuỗi.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ];

        $validator->setCustomMessages($customMessages);

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
                    'email' => ['Email hoặc mật khẩu không đúng']
                ]
            ], 401);
        }

        if (auth()->user()->email_verified_at == null) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['Email chưa được xác thực']
                ]
            ], 401);
        }

        if (auth()->user()->status == 'banned') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['Tài khoản đã bị khóa']
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
                        'email' => ['Tài khoản đã bị khóa']
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

        $customMessages = [
            'fullname.required' => 'Tên không được để trống.',
            'fullname.string' => 'Tên phải là một chuỗi.',
            'fullname.between' => 'Tên phải có độ dài từ 2 đến 100 ký tự.',
            'email.required' => 'Email không được để trống.',
            'email.string' => 'Email phải là một chuỗi.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được quá 100 ký tự.',
            'email.unique' => 'Email đã tồn tại.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.string' => 'Mật khẩu phải là một chuỗi.',
            'password.confirmed' => 'Mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ];

        $validator->setCustomMessages($customMessages);

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

        $customMessages = [
            'old_password.required' => 'Mật khẩu cũ không được để trống.',
            'old_password.string' => 'Mật khẩu cũ phải là một chuỗi.',
            'old_password.min' => 'Mật khẩu cũ phải có ít nhất 8 ký tự.',
            'new_password.required' => 'Mật khẩu mới không được để trống.',
            'new_password.string' => 'Mật khẩu mới phải là một chuỗi.',
            'new_password.confirmed' => 'Mật khẩu mới không khớp.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        if (!auth()->attempt(['email' => auth()->user()->email, 'password' => $request->old_password])) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'old_password' => ['Mật khẩu cũ không đúng']
                ]
            ], 401);
        }

        if ($request->old_password == $request->new_password) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'new_password' => ['Mật khẩu mới không được trùng với mật khẩu cũ']
                ]
            ], 401);
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

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'nullable|string',
            'fullname' => 'required|string|between:2,100',
            'job' => 'nullable|string',
            'story' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female',
            'dob' => 'nullable|date',
            'street' => 'nullable|string',
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'ward' => 'nullable|string',
            'address_detail' => 'nullable|string',
            'phone' => 'required|string|min:10|max:11',
        ]);

        $customMessages = [
            'gender.in' => 'Giới tính phải là male hoặc female.',
            'dob.date' => 'Ngày sinh phải là một ngày.',
            'phone.required' => 'Số điện thoại không được để trống.',
            'phone.string' => 'Số điện thoại phải là một chuỗi.',
            'phone.min' => 'Số điện thoại phải có ít nhất 10 ký tự.',
            'phone.max' => 'Số điện thoại không được quá 11 ký tự.',
            'fullname.required' => 'Tên không được để trống.',
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::with('role')->find(auth()->user()->id);

        try {
            $user->update($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Update profile successfully',
                'data' => $user,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Update profile failed',
            ], 400);
        }
    }
}
