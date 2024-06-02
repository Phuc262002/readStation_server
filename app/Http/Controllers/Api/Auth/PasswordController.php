<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

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

class PasswordController extends Controller
{
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
            'password' => 'required|string|confirmed|min:8',
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
}
