<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/verification-requests',
    operationId: 'adminVerificationRequestIndex',
    tags: ['Admin / Verification Request'],
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

#[OA\Get(
    path: '/api/v1/admin/verification-requests/{id}',
    operationId: 'adminVerificationRequestShow',
    tags: ['Admin / Verification Request'],
    summary: 'Get a verification request',
    description: 'Get a verification request',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của verification request',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get verification request successfully',
        ),
        new OA\Response(
            response: 500,
            description: 'Get verification request failed',
        ),
    ]
)]

#[OA\Put(
    path: '/api/v1/admin/verification-requests/update/{id}',
    operationId: 'adminVerificationRequestUpdate',
    tags: ['Admin / Verification Request'],
    summary: 'Update a verification request',
    description: 'Update a verification request',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID của verification request',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status', 'reason'],
            properties: [
                new OA\Property(property: 'status', type: 'string', enum: ['approved', 'rejected']),
                new OA\Property(property: 'reason', type: 'string', nullable: true),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update verification request successfully',
        ),
        new OA\Response(
            response: 500,
            description: 'Update verification request failed',
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

        $query = VerificationRequest::query()->with('userRequest', 'userHandle');

        $totalItems = $query->count();

        if ($verification_card_type) {
            $query->where('verification_card_type', $verification_card_type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $verification_requests = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

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

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:verification_requests,id',
        ], [
            'id.required' => 'Vui lòng nhập ID',
            'id.exists' => 'ID không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        try {
            $verification_request = VerificationRequest::with('userRequest')->find($id);

            return response()->json([
                'status' => true,
                'message' => 'Get verification_request successfully',
                'data' => $verification_request
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Get verification_request failed',
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|exists:verification_requests,id',
            'status' => 'required|in:approved,rejected',
            'reason' => 'required_if:status,rejected',
        ], [
            'id.required' => 'Vui lòng nhập ID',
            'id.exists' => 'ID không tồn tại',
            'status.required' => 'Vui lòng nhập trạng thái',
            'status.in' => 'Trạng thái phải là approved hoặc rejected',
            'reason.required_if' => 'Vui lòng nhập lý do từ chối',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        try {

            $verification_request = VerificationRequest::find($id);

            if ($verification_request->status !== 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Verification request is not pending',
                ]);
            }

            $verification_request->update([
                'status' => $request->status,
                'reason' => $request->reason,
                'user_handle_id' => auth()->user()->id,
                'verification_date' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Update verification_request successfully',
                'data' => $verification_request
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Update verification_request failed',
            ]);
        }
    }
}
