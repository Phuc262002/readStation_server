<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Api\PayOS\CheckCCCDController;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/account/verification-requests',
    operationId: 'clientVerificationRequestIndex',
    tags: ['Account / Verification Request'],
    summary: 'Get all verification requests',
    description: 'Get all verification requests',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'page',
            in: 'query',
            required: false,
            description: 'Số trang hiện tại',
            schema: new OA\Schema(type: 'integer', default: 1)
        ),
        new OA\Parameter(
            name: 'pageSize',
            in: 'query',
            required: false,
            description: 'Số lượng mục trên mỗi trang',
            schema: new OA\Schema(type: 'integer', default: 10)
        ),
        new OA\Parameter(
            name: 'verification_card_type',
            in: 'query',
            required: false,
            description: 'Lọc theo loại thẻ',
            schema: new OA\Schema(type: 'string', enum: ['student_card', 'citizen_card'])
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Lọc theo trạng thái',
            schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected'])
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all wallets successfully',
        ),
        new OA\Response(
            response: 500,
            description: 'Get all wallets failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/account/verification-requests/create',
    operationId: 'clientVerificationRequestStore',
    tags: ['Account / Verification Request'],
    summary: 'Create a verification request',
    description: 'Create a verification request',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['verification_card_type', 'verification_card_image', 'verification_card_info'],
            properties: [
                new OA\Property(property: 'verification_card_type', type: 'string', description: 'Loại thẻ', enum: ['student_card', 'citizen_card']),
                new OA\Property(
                    property: 'verification_card_image',
                    type: 'object',
                    description: 'Ảnh thẻ',
                    properties: [
                        new OA\Property(property: 'front', type: 'string', description: 'Ảnh mặt trước thẻ'),
                        new OA\Property(property: 'back', type: 'string', description: 'Ảnh mặt sau thẻ'),
                    ]
                ),
                new OA\Property(
                    property: 'verification_card_info',
                    type: 'object',
                    description: 'Thông tin thẻ',
                    properties: [
                        new OA\Property(property: 'student_name', type: 'string', description: 'Tên sinh viên'),
                        new OA\Property(property: 'student_code', type: 'string', description: 'Mã số sinh viên'),
                        new OA\Property(property: 'student_card_expired', type: 'string', format: 'date', description: 'Ngày hết hạn thẻ'),
                        new OA\Property(property: 'place_of_study', type: 'string', description: 'Tên trường'),
                    ]
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create verification request successfully',
        ),
        new OA\Response(
            response: 500,
            description: 'Create verification request failed',
        ),
    ]
)]

class VerificationRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'verification_card_type' => 'string|in:student_card,citizen_card',
            'status' => 'string|in:pending,approved,rejected',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'verification_card_type.string' => 'Loại thẻ phải là chuỗi.',
            'verification_card_type.in' => 'Loại thẻ phải là student_card hoặc citizen_card.',
            'status.string' => 'Trạng thái phải là chuỗi.',
            'status.in' => 'Trạng thái phải là pending, approved hoặc rejected.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $verification_card_type = $request->input('verification_card_type');
        $status = $request->input('status');

        $query = VerificationRequest::query()->with('userHandle')->where('user_request_id', auth()->id());

        $totalItems = $query->count();

        if ($verification_card_type) {
            $query->where('verification_card_type', $verification_card_type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $verification_requests = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        $verification_requests->getCollection()->transform(function ($verificationRequest) {
            return [
                'id' => $verificationRequest->id,
                'verification_card_type' => $verificationRequest->verification_card_type,
                'verification_card_image' => $verificationRequest->verification_card_image,
                'verification_card_info' => $verificationRequest->verification_card_info,
                'reason' => $verificationRequest->reason,
                'status' => $verificationRequest->status,
                'verification_date' => $verificationRequest->verification_date,
                'userHandle' => [
                    'fullname' => $verificationRequest->userHandle->name,
                    'email' => $verificationRequest->userHandle->email,
                    'phone' => $verificationRequest->userHandle->phone,
                    'avatar' => $verificationRequest->userHandle->avatar,
                ],
                'created_at' => $verificationRequest->created_at,
                'updated_at' => $verificationRequest->updated_at,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Get all verification_requests successfully',
            'data' => [
                "verificationRequests" => $verification_requests->items(),
                "page" => $verification_requests->currentPage(),
                "pageSize" => $verification_requests->perPage(),
                "totalPages" => $verification_requests->lastPage(),
                "totalResults" => $verification_requests->total(),
                "total" => $totalItems
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_card_type' => 'required|string|in:student_card,citizen_card',
            'verification_card_image' => 'required|array',
            'verification_card_image.front' => 'required|string',
            'verification_card_image.back' => 'required|string',
        ], [
            'verification_card_type.required' => 'Loại thẻ là bắt buộc.',
            'verification_card_type.string' => 'Loại thẻ phải là chuỗi.',
            'verification_card_type.in' => 'Loại thẻ phải là student_card hoặc citizen_card.',
            'verification_card_image.required' => 'Ảnh thẻ là bắt buộc.',
            'verification_card_image.array' => 'Ảnh thẻ phải là mảng.',
            'verification_card_image.front.required' => 'Ảnh mặt trước thẻ là bắt buộc.',
            'verification_card_image.front.string' => 'Ảnh mặt trước thẻ phải là chuỗi.',
            'verification_card_image.back.required' => 'Ảnh mặt sau thẻ là bắt buộc.',
            'verification_card_image.back.string' => 'Ảnh mặt sau thẻ phải là chuỗi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $verification_requests = VerificationRequest::where('user_request_id', auth()->id())->where('status', 'pending')->get();
        foreach ($verification_requests as $verification_request) {
            if ($verification_request->verification_card_type == $request->verification_card_type) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bạn đã gửi yêu cầu xác thực thẻ ' . $request->verification_card_type . ' rồi.',
                ]);
            }
        }

        if (auth()->user()->role->name == 'student' && $request->verification_card_type == 'student_card') {
            return response()->json([
                'status' => false,
                'message' => 'Bạn đang là sinh viên, không thể xác thực thẻ sinh viên.',
            ]);
        }

        if (auth()->user()->role->name === 'admin' || auth()->user()->role->name == 'manager') {
            return response()->json([
                'status' => false,
                'message' => 'Bạn Không có quyền thực hiện hành động này.',
            ]);
        }

        if (auth()->user()->user_verified_at) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản của bạn đã được xác thực.',
            ]);
        }

        if ($request->verification_card_type == 'student_card') {
            $validator2 = Validator::make($request->all(), [
                'verification_card_info' => 'required|array',
                'verification_card_info.student_name' => 'required|string',
                'verification_card_info.student_code' => 'required|string',
                'verification_card_info.student_card_expired' => 'required|date',
                'verification_card_info.place_of_study' => 'required|string',
            ], [
                'verification_card_info.required' => 'verification_card_info không được để trống.',
                'verification_card_info.array' => 'verification_card_info phải là dạng mảng.',
                'verification_card_info.student_name.required' => 'Tên sinh viên không được để trống.',
                'verification_card_info.student_name.string' => 'Tên sinh viên phải là chuỗi.',
                'verification_card_info.student_code.required' => 'Mã số sinh viên không được để trống.',
                'verification_card_info.student_code.string' => 'Mã số sinh viên phải là chuỗi.',
                'verification_card_info.student_card_expired.required' => 'Ngày hết hạn không được để trống.',
                'verification_card_info.student_card_expired.date' => 'Ngày hết hạn phải là ngày.',
                'verification_card_info.place_of_study.required' => 'Tên trường không được để trống.',
            ]);

            if ($validator2->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Dữ liệu không hợp lệ",
                    "errors" => $validator2->errors()
                ], 400);
            }
        } else {
            $validator3 = Validator::make($request->all(), [
                'verification_card_info' => 'required|array',
                'verification_card_info.citizen_name' => 'required|string',
                'verification_card_info.citizen_code' => 'required|string',
                'verification_card_info.date_of_issue' => 'required|date',
                'verification_card_info.place_of_issue' => 'required|string',
            ], [
                'verification_card_info.required' => 'verification_card_info không được để trống.',
                'verification_card_info.array' => 'verification_card_info phải là dạng mảng.',
                'verification_card_info.citizen_name.required' => 'Tên công dân không được để trống.',
                'verification_card_info.citizen_name.string' => 'Tên công dân phải là chuỗi.',
                'verification_card_info.citizen_code.required' => 'Mã CCCD không được để trống.',
                'verification_card_info.citizen_code.string' => 'Mã CCCD phải là chuỗi.',
                'verification_card_info.date_of_issue.required' => 'Ngày cấp không được để trống.',
                'verification_card_info.date_of_issue.date' => 'Ngày cấp phải là ngày.',
                'verification_card_info.place_of_issue.required' => 'Nơi cấp không được để trống.',
                'verification_card_info.place_of_issue.string' => 'Nơi cấp phải là chuỗi.',
            ]);

            if ($validator3->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => "Dữ liệu không hợp lệ",
                    "errors" => $validator3->errors()
                ], 400);
            }

            $users = User::all();

            foreach ($users as $user) {
                if ($user->citizen_identity_card) {
                    if ($user->citizen_identity_card['citizen_code'] == $request->verification_card_info['citizen_code']) {
                        return response()->json([
                            "status" => false,
                            "message" => "CCCD/CMND đã tồn tại trong hệ thống.",
                        ], 400);
                    }
                }
            }

            $checkCCCD = new CheckCCCDController();

            $response = $checkCCCD->checkCCCDUser($request->verification_card_info['citizen_code'], $request->verification_card_info['citizen_name']);

            if (!$response) {
                return response()->json([
                    "status" => false,
                    "message" => "Tên ứng với CCCD/CMND đang sai, vui lòng kiểm tra lại.",
                ], 400);
            }
        }

        try {
            $verificationRequest = VerificationRequest::create([
                'user_request_id' => auth()->id(),
                'verification_card_type' => $request->verification_card_type,
                'verification_card_image' => $request->verification_card_image,
                'verification_card_info' => $request->verification_card_info,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Create verification request successfully',
                'data' => $verificationRequest
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Create verification request failed',
                'data' => $th->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(VerificationRequest $verificationRequest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VerificationRequest $verificationRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VerificationRequest $verificationRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VerificationRequest $verificationRequest)
    {
        //
    }
}
