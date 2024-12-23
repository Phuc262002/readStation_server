<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ApprovedVerification;
use App\Mail\RejectVerification;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Tìm kiếm theo ID hoặc trạng thái',
            schema: new OA\Schema(type: 'string')
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
            'search' => 'string',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'verification_card_type.string' => 'Loại thẻ phải là chuỗi.',
            'verification_card_type.in' => 'Loại thẻ phải là student_card hoặc citizen_card.',
            'status.string' => 'Trạng thái phải là chuỗi.',
            'status.in' => 'Trạng thái phải là pending, approved hoặc rejected.',
            'search.string' => 'Tìm kiếm phải là chuỗi.',
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
        $search = $request->input('search');

        $query = VerificationRequest::query()->with('userRequest', 'userHandle');

        $totalItems = $query->count();

        if ($verification_card_type) {
            $query->where('verification_card_type', $verification_card_type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('userRequest', function ($query) use ($search) {
                        $query->where('email', 'like', '%' . $search . '%')
                            ->orWhere('phone', 'like', '%' . $search . '%')
                            ->orWhere('fullname', 'like', '%' . $search . '%');
                    });
            });
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
            $verification_request = VerificationRequest::with([
                'userRequest', 
                'userRequest.role', 
                'userRequest.ward.district.province',
            ])->find($id);

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
                    'message' => 'Yêu cầu xác thực đã được xử lý',
                ]);
            }

            $user = User::find($verification_request->user_request_id);

            if ($verification_request->verification_card_type === 'student_card') {

                if ($request->status === 'approved') {
                    $verification_request->update([
                        'status' => $request->status,
                        'reason' => $request->reason,
                        'user_handle_id' => auth()->user()->id,
                        'verification_date' => now()
                    ]);

                    $user->update([
                        'role_id' => 2,
                        'student_id_card' => [
                            'student_name' => $verification_request->verification_card_info['student_name'],
                            'student_code' => $verification_request->verification_card_info['student_code'],
                            'student_card_expired' => $verification_request->verification_card_info['student_card_expired'],
                            'place_of_study' => $verification_request->verification_card_info['place_of_study'],
                        ],
                    ]);
                    Mail::to($user->email)->send(new ApprovedVerification($verification_request));
                } else {
                    $verification_request->update([
                        'status' => $request->status,
                        'reason' => $request->reason,
                        'user_handle_id' => auth()->user()->id,
                        'verification_date' => now()
                    ]);

                    Mail::to($user->email)->send(new RejectVerification($verification_request));
                }
            } else {
                if ($request->status === 'approved') {
                    $verification_request->update([
                        'status' => $request->status,
                        'reason' => $request->reason,
                        'user_handle_id' => auth()->user()->id,
                        'verification_date' => now()
                    ]);

                    $user->update([
                        'user_verified_at' => now(),
                        'citizen_identity_card' => [
                            'citizen_name' => $verification_request->verification_card_info['citizen_name'],
                            'citizen_code' => $verification_request->verification_card_info['citizen_code'],
                            'date_of_issue' => $verification_request->verification_card_info['date_of_issue'],
                            'place_of_issue' => $verification_request->verification_card_info['place_of_issue']
                        ],
                    ]);
                    Mail::to($user->email)->send(new ApprovedVerification($verification_request));
                } else {
                    $verification_request->update([
                        'status' => $request->status,
                        'reason' => $request->reason,
                        'user_handle_id' => auth()->user()->id,
                        'verification_date' => now()
                    ]);
                    Mail::to($user->email)->send(new RejectVerification($verification_request));
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Update verification_request successfully',
                'data' => $verification_request
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Update verification_request failed',
                'errors' => $th->getMessage(),
            ], 500);
        }
    }
}
