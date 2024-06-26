<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/account/get-profile',
    operationId: 'userProfile',
    tags: ['Account'],
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

#[OA\Put(
    path: '/api/v1/account/update-profile',
    operationId: 'updateProfile',
    tags: ['Account'],
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
                new OA\Property(property: 'province_id', type: 'string'),
                new OA\Property(property: 'district_id', type: 'string'),
                new OA\Property(property: 'ward_id', type: 'string'),
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


class AccountController extends Controller
{
    public function userProfile()
    {
        $user = array_merge(User::with(['role', 'province', 'district', 'ward'])->find(auth()->user()->id)->toArray(), [
            'google_id' => auth()->user()->google_id ? true : false,
        ]);

        // Trả về dữ liệu đã được định dạng lại thông qua Accessor
        return response()->json([
            "status" => true,
            "message" => "User profile fetched successfully",
            "data" => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'nullable|string',
            'fullname' => 'string|between:2,100',
            'job' => 'nullable|string',
            'story' => 'nullable|string',
            'gender' => 'nullable|string|in:male,female',
            'dob' => 'nullable|date',
            'street' => 'nullable|string',
            'province_id' => 'nullable|string|exists:provinces,id',
            'district_id' => 'nullable|string|exists:districts,id',
            'ward_id' => 'nullable|string|exists:wards,id',
            'address_detail' => 'nullable|string',
            'phone' => 'string|min:10|max:11',
        ], [
            'gender.in' => 'Giới tính phải là male hoặc female.',
            'dob.date' => 'Ngày sinh phải là một ngày.',
            'phone.string' => 'Số điện thoại phải là một chuỗi.',
            'phone.min' => 'Số điện thoại phải có ít nhất 10 ký tự.',
            'phone.max' => 'Số điện thoại không được quá 11 ký tự.',
            'province_id.exists' => 'Tỉnh/Thành phố không tồn tại.',
            'district_id.exists' => 'Quận/Huyện không tồn tại.',
            'ward_id.exists' => 'Xã/Phường không tồn tại.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => true,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $user = User::with(['role', 'province', 'district', 'ward'])->find(auth()->user()->id);

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
