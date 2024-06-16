<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/wallet/admin/get-all',
    operationId: 'getAllWallets',
    tags: ['Admin / Wallet'],
    summary: 'Get all wallets',
    description: 'Get all wallets',
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
            name: 'search',
            in: 'query',
            required: false,
            description: 'Tìm kiếm theo tên, email hoặc số điện thoại',
            schema: new OA\Schema(type: 'string')
        ),
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: false,
            description: 'Lọc theo trạng thái',
            schema: new OA\Schema(type: 'string', enum: ['active', 'locked', 'suspended', 'frozen'])
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
    path: '/api/v1/account/wallet/admin/create',
    tags: ['Admin / Wallet'],
    operationId: 'createTransaction',
    summary: 'Create transaction',
    description: 'Create transaction',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['user_id', 'amount', 'description', 'transaction_type'],
            properties: [
                new OA\Property(property: 'user_id', type: 'string', description: 'ID người dùng', example: '1'),
                new OA\Property(property: 'amount', type: 'number', description: 'Số tiền', example: 10000),
                new OA\Property(property: 'description', type: 'string', description: 'Mô tả', example: 'Nạp tiền vào ví'),
                new OA\Property(property: 'transaction_type', type: 'string', description: 'Loại giao dịch', example: 'deposit'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create transaction successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Create transaction failed',
        ),
    ],
)]

#[OA\Get(
    path: '/api/v1/account/wallet/admin/get-user-wallet-transactions-history/{id}',
    operationId: 'getUserWalletTransactionsHistory',
    tags: ['Admin / Wallet'],
    summary: 'Get user wallet transactions history',
    description: 'Get user wallet transactions history',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID người dùng',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get user wallet transactions history successfully',
        ),
        new OA\Response(
            response: 500,
            description: 'Get user wallet transactions history failed',
        ),
    ]
)]



class WalletController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'search' => 'string',
            'status' => 'string|in:active,locked,suspended,frozen',
        ], [
            'page.integer' => 'Trang phải là số nguyên.',
            'page.min' => 'Trang phải lớn hơn hoặc bằng 1.',
            'pageSize.integer' => 'Kích thước trang phải là số nguyên.',
            'pageSize.min' => 'Kích thước trang phải lớn hơn hoặc bằng 1.',
            'search.string' => 'Tìm kiếm phải là chuỗi.',
            'status.string' => 'Trạng thái phải là chuỗi.',
            'status.in' => 'Trạng thái phải là active, locked, suspended hoặc frozen.',
        ]);

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Wallet::query()->with('user');

        $totalItems = $query->count();

        if ($search) {
            $wallets = $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if($status) {
            $query->where('status', $status);
        }

        $wallets = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'message' => 'Get all wallets successfully',
            'data' => [
                "wallets" => $wallets->items(),
                "page" => $wallets->currentPage(),
                "pageSize" => $wallets->perPage(),
                "totalPages" => $wallets->lastPage(),
                "totalResults" => $wallets->total(),
                "total" => $totalItems
            ]
        ]);
    }

    public function store(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:10000',
            'description' => 'required|string',
            'transaction_type' => 'required|string|in:deposit,withdraw',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 10,000 VND',
            'description.required' => 'Vui lòng nhập mô tả',
            'description.string' => 'Mô tả phải là chuỗi',
            'transaction_type.required' => 'Vui lòng chọn loại giao dịch',
            'transaction_type.string' => 'Loại giao dịch phải là chuỗi',
            'transaction_type.in' => 'Loại giao dịch không hợp lệ',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $wallet = Wallet::where('user_id', $request->user_id)->first();

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại',
            ]);
        }

        $transaction_code = intval(substr(strval(microtime(true) * 100000), -6));

        $transaction = $wallet->transactions()->create([
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'description' => $request->description,
            'reference_id' => $transaction_code,
            'transaction_code' => $transaction_code,
            'transaction_type' => $request->transaction_type,
            'transaction_method' => 'offline',
            'status' => 'completed'
        ]);

        if (!$transaction) {
            return response()->json([
                'error' => false,
                'message' => 'Giao dịch thất bại',
            ]);
        }

        try {
            if ($transaction->transaction_type == 'deposit') {
                $wallet->update([
                    'balance' => $wallet->balance + $transaction->amount
                ]);
            }

            if ($transaction->transaction_type == 'withdraw') {
                $wallet->update([
                    'balance' => $wallet->balance - $transaction->amount
                ]);
            }

            return response()->json([
                "status" => true,
                "message" => "Success",
                "data" => $transaction
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "data" => null
            ]);
        }
    }

    public function show($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:wallets,id',
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

        $wallet = Wallet::with('user', 'transactions')->find($id);

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found',
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Get wallet successfully',
            'data' => $wallet
        ]);
    }
}
