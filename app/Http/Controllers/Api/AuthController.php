<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\UserVerification;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
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
            description: 'Validation error'
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
            description: 'Validation error'
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
            description: 'Validation error'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/verify-email',
    operationId: 'verifyEmail',
    tags: ['Auth'],
    summary: 'Verify email',
    description: 'Verify email',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'code'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'otpCode', type: 'string')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Email verified successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthorized'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/resend-otp',
    operationId: 'reRegister',
    tags: ['Auth'],
    summary: 'Resend OTP',
    description: 'Resend OTP',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Re-register successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/logout',
    operationId: 'logout',
    tags: ['Auth'],
    summary: 'Logout',
    description: 'Logout',
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

#[OA\Post(
    path: '/api/v1/auth/send-reset-password',
    operationId: 'sendRequestForgotPassword',
    tags: ['Auth'],
    summary: 'Send request forgot password',
    description: 'Send request forgot password',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Send email successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/reset-password',
    operationId: 'changePassWordReset',
    tags: ['Auth'],
    summary: 'Change password reset',
    description: 'Change password reset',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'token', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'token', type: 'string'),
                new OA\Property(property: 'password', type: 'string', format: 'password'),
                new OA\Property(property: 'password_confirmation', type: 'string', format: 'password')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Change password reset successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthorized'
        )
    ]
)]

#[OA\Get(
    path: '/api/v1/auth/profile',
    operationId: 'userProfile',
    tags: ['Auth'],
    summary: 'User profile',
    description: 'User profile',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User profile fetched successfully'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/update-profile',
    operationId: 'updateProfile',
    tags: ['Auth'],
    summary: 'Update profile',
    description: 'Update profile',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['avatar', 'fullname', 'job', 'story', 'gender', 'dob', 'street', 'province', 'district', 'ward', 'address_detail', 'phone'],
            properties: [
                new OA\Property(property: 'avatar', type: 'string'),
                new OA\Property(property: 'fullname', type: 'string'),
                new OA\Property(property: 'job', type: 'string'),
                new OA\Property(property: 'story', type: 'string'),
                new OA\Property(property: 'gender', type: 'string', enum: ['male', 'female']),
                new OA\Property(property: 'dob', type: 'string', format: 'date'),
                new OA\Property(property: 'street', type: 'string'),
                new OA\Property(property: 'province', type: 'string'),
                new OA\Property(property: 'district', type: 'string'),
                new OA\Property(property: 'ward', type: 'string'),
                new OA\Property(property: 'address_detail', type: 'string'),
                new OA\Property(property: 'phone', type: 'string')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update profile successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/auth/change-password',
    operationId: 'changePassWord',
    tags: ['Auth'],
    summary: 'Change password',
    description: 'Change password',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['old_password', 'new_password', 'new_password_confirmation'],
            properties: [
                new OA\Property(property: 'old_password', type: 'string', format: 'password'),
                new OA\Property(property: 'new_password', type: 'string', format: 'password'),
                new OA\Property(property: 'new_password_confirmation', type: 'string', format: 'password')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'User successfully changed password'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthorized'
        )
    ]
)]




class AuthController extends Controller
{
    public function __construct()
    {
    }

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

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:8|regex:/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$/',
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
            'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường và 1 số.',
            'password.confirmed' => 'Mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
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

    public function reRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100',
        ], [
            'email.required' => 'Email không được để trống.',
            'email.string' => 'Email phải là một chuỗi.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được quá 100 ký tự.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::where([
            'email' => $request->email,
        ])->first();

        if ($user) {
            if ($user['email_verified_at'] != null)
                return [
                    'status' => false,
                    'message' => 'Email đã được xác nhận.',
                ];
            else {
                $user->confirmation_code = rand(100000, 999999);
                $user->confirmation_code_expired_in = Carbon::now()->addSecond(60);
                $user->save();
                try {
                    Mail::to($user->email)->send(new UserVerification($user));
                    return response()->json([
                        'status' => true,
                        'message' => 'Gửi lại mã xác nhận thành công.',
                    ], 201);
                } catch (\Exception $err) {
                    return [
                        'status' => false,
                        'message' => 'Không thể gửi email xác nhận, vui lòng thử lại.',
                    ];
                }
            }
        } else {
            return [
                'status' => false,
                'message' => 'Email không tồn tại',
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

    public function userProfile()
    {
        $user = array_merge(User::with('role')->find(auth()->user()->id)->toArray(), [
            'google_id' => auth()->user()->google_id ? true : false,
        ]);

        // Trả về dữ liệu đã được định dạng lại thông qua Accessor
        return response()->json([
            "status" => true,
            "message" => "User profile fetched successfully",
            "data" => $user
        ]);
    }

    protected function createNewToken($token, $refreshToken)
    {
        $user = User::with('role')->find(auth()->user()->id);
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

    public function changePassWord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:8',
            'new_password' => 'required|string|confirmed|min:8|regex:/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$/',
        ]);

        $customMessages = [
            'old_password.required' => 'Mật khẩu cũ không được để trống.',
            'old_password.string' => 'Mật khẩu cũ phải là một chuỗi.',
            'old_password.min' => 'Mật khẩu cũ phải có ít nhất 8 ký tự.',
            'new_password.required' => 'Mật khẩu mới không được để trống.',
            'new_password.string' => 'Mật khẩu mới phải là một chuỗi.',
            'new_password.confirmed' => 'Mật khẩu mới không khớp.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.regex' => 'Mật khẩu mới phải có ít nhất 1 chữ hoa, 1 chữ thường và 1 số.',
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

    public function sendRequestForgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], [
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không đúng định dạng',
            'email.exists' => 'Email không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $checkEmail = User::where([
            'email' => $request->email,
        ])->first();

        if (!$checkEmail) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['Email không tồn tại']
                ]
            ], 401);
        } else {
            if ($checkEmail->email_verified_at == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                    'errors' => [
                        'email' => ['Email chưa được xác thực']
                    ]
                ], 401);
            } elseif ($checkEmail->status == 'banned') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                    'errors' => [
                        'email' => ['Tài khoản đã bị khóa']
                    ]
                ], 401);
            } else {
                try {
                    $status = Password::sendResetLink(
                        $request->only('email')
                    );

                    if ($status == Password::RESET_LINK_SENT) {
                        return response()->json([
                            'status' => true,
                            'message' => 'Gửi email thành công',
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Gửi email thất bại',
                        ], 400);
                    }
                } catch (\Throwable $th) {
                    DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                    return response()->json([
                        'status' => false,
                        'message' => 'Gửi email thất bại',
                    ], 400);
                }
            }
        }
    }

    public function changePassWordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:8|regex:/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])[A-Za-z\d]{8,}$/',
        ], [
            'email.required' => 'Email không được để trống',
            'email.string' => 'Email phải là một chuỗi',
            'email.email' => 'Email không đúng định dạng',
            'email.exists' => 'Email không tồn tại',
            'token.required' => 'Token không được để trống',
            'token.string' => 'Token phải là một chuỗi',
            'password.required' => 'Mật khẩu không được để trống',
            'password.string' => 'Mật khẩu phải là một chuỗi',
            'password.confirmed' => 'Mật khẩu không khớp',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.regex' => 'Mật khẩu phải có ít nhất 1 chữ hoa, 1 chữ thường và 1 số',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::where([
            'email' => $request->email,
        ])->first();

        if ($user->status == 'banned') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['Tài khoản đã bị khóa. Vui lòng liên hệ với quản trị viên để biết thêm chi tiết.']
                ]
            ], 401);
        } elseif ($user->status == 'deleted') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'email' => ['Tài khoản đã bị xóa. Vui lòng liên hệ với quản trị viên để biết thêm chi tiết.']
                ]
            ], 401);
        }

        $resetToken = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if ($resetToken && Hash::check($request->token, $resetToken->token)) {
            $now = Carbon::now();
            if ($now->diffInMinutes($resetToken->created_at) > 5) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                    'errors' => [
                        'token' => ['Token đã hết hạn']
                    ]
                ], 401);
            } else {

                $user = User::where([
                    'email' => $request->email,
                ])->first();

                if ($user) {
                    if (!Hash::check($request->password, $user->password)) {
                        $user->password = Hash::make($request->password);
                        $user->save();
                        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                        if ($user->email_verified_at == null) {
                            return response()->json([
                                'status' => false,
                                'message' => 'Unauthorized',
                                'errors' => [
                                    'email' => ['Email chưa được xác thực']
                                ]
                            ], 401);
                        } elseif ($user->status == 'banned') {
                            return response()->json([
                                'status' => false,
                                'message' => 'Unauthorized',
                                'errors' => [
                                    'email' => ['Tài khoản đã bị khóa. Vui lòng liên hệ với quản trị viên để biết thêm chi tiết.']
                                ]
                            ], 401);
                        } elseif ($user->status == 'deleted') {
                            return response()->json([
                                'status' => false,
                                'message' => 'Unauthorized',
                                'errors' => [
                                    'email' => ['Tài khoản đã bị xóa. Vui lòng liên hệ với quản trị viên để biết thêm chi tiết.']
                                ]
                            ], 401);
                        } else {
                            return response()->json([
                                'status' => true,
                                'message' => 'Đổi mật khẩu thành công',
                            ], 200);
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Unauthorized',
                            'errors' => [
                                'password' => ['Mật khẩu mới không được trùng với mật khẩu cũ']
                            ]
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Unauthorized',
                        'errors' => [
                            'email' => ['Email không tồn tại']
                        ]
                    ], 401);
                }
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
                'errors' => [
                    'token' => ['Token không hợp lệ']
                ]
            ], 401);
        }
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

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otpCode' => 'required|string|min:6|max:6',
        ], [
            'email.required' => 'Email không được để trống',
            'email.email' => 'Email không đúng định dạng',
            'email.exists' => 'Email không tồn tại',
            'otpCode.required' => 'Mã OTP không được để trống',
            'otpCode.string' => 'Mã OTP phải là một chuỗi',
            'otpCode.min' => 'Mã OTP phải có ít nhất 6 ký tự',
            'otpCode.max' => 'Mã OTP không được quá 6 ký tự',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::where([
            'email' => $request->input('email'),
            'google_id' => null,
        ])->first();
        if (!$user) {
            return [
                'status' => false,
                'message' => 'Tài khoản không tồn tại'
            ];
        }

        if ($user->email_verified_at != null) {
            return [
                'status' => false,
                'message' => 'Email đã được xác thực'
            ];
        }

        if (Carbon::now()->gt($user->confirmation_code_expired_in)) {
            return [
                'status' => false,
                'message' => 'Mã OTP đã hết hạn'
            ];
        } else {
            if ($request->input('otpCode') != $user->confirmation_code) {
                return [
                    'status' => false,
                    'message' => 'Mã OTP không hợp lệ'
                ];
            }
            $user->email_verified_at = Carbon::now();
            $user->save();
            return [
                'status' => true,
                'message' => 'Xác thực thành công'
            ];
        }
    }
}
