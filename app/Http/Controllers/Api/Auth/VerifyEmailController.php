<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\UserVerification;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

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

class VerifyEmailController extends Controller
{
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
