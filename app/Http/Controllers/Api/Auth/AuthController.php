<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserVerification;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/auth/login',
    operationId: 'login',
    tags: ['Auth'],
    summary: 'Login',
    description: 'Login',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Successful login'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthorized'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/google',
    operationId: 'loginWithGoogle',
    tags: ['Auth'],
    summary: 'Login with Google',
    description: 'Login with Google',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['idToken'],
            properties: [
                new OA\Property(property: 'idToken', type: 'string')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Successful login with Google'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthorized'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/register',
    operationId: 'register',
    tags: ['Auth'],
    summary: 'Register',
    description: 'Register',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['fullname', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'fullname', type: 'string'),
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string', format: 'password'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'User registered successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/logout',
    operationId: 'logout',
    tags: ['Auth'],
    summary: 'Logout',
    description: 'Logout',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User Logged out successfully'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/refresh',
    operationId: 'refresh',
    tags: ['Auth'],
    summary: 'Refresh',
    description: 'Refresh',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['refreshToken'],
            properties: [
                new OA\Property(property: 'refreshToken', type: 'string')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Refresh token successful'
        ),
        new OA\Response(
            response: 401,
            description: 'Invalid refresh token'
        )
    ]
)]

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.email' => 'Email không đúng định dạng.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.string' => 'Mật khẩu phải là một chuỗi.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Dữ liệu không hợp lệ',
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
                    'email' => ['Tài khoản đã bị vô hiệu hóa']
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

    public function loginWithGoogle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idToken' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Dữ liệu không hợp lệ",
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
                        'email' => ['Tài khoản đã bị vô hiệu hóa']
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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:8',
        ], [
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            [
                'confirmation_code' => rand(100000, 999999),
                'confirmation_code_expired_in' => Carbon::now()->addSecond(60)
            ]
        ));

        try {
            Mail::to($user->email)->send(new UserVerification($user));
            return response()->json([
                "status" => true,
                "message" => "User registered successfully",
            ], 201);
        } catch (\Exception $err) {
            $user->delete();
            return [
                'status' => false,
                'message' => 'Không thể gửi email xác nhận, vui lòng thử lại.',
                'errors' => $err->getMessage()
            ];
        }
    }

    public function logout()
    {
        auth()->logout(true);

        return response()->json([
            "status" => true,
            "message" => "User Logged out successfully"
        ]);
    }

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

    protected function createNewToken($token, $refreshToken)
    {
        $user = User::with(['role', 'province', 'district', 'ward'])->find(auth()->user()->id);
        setcookie('token', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
        return response()->json([
            "status" => true,
            "message" => "Login successful",
            "data" => [
                "user" => array_merge($user->toArray(), [
                    'google_id' => auth()->user()->google_id ? true : false,
                ]),
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
}
