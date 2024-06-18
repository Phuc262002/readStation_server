<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use PayOS\PayOS;

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
    path: '/api/v1/wallet/admin/create-deposit',
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
    path: '/api/v1/wallet/admin/get-user-wallet-transactions-history/{id}',
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

#[OA\Put(
    path: '/api/v1/wallet/admin/update-status/{id}',
    operationId: 'updateWalletStatus',
    tags: ['Admin / Wallet'],
    summary: 'Update wallet status',
    description: 'Update wallet status',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID ví',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status', 'reason'],
            properties: [
                new OA\Property(property: 'status', type: 'string', description: 'Trạng thái', example: 'active'),
                new OA\Property(property: 'reason', type: 'string', description: 'Lý do', example: 'Lý do khác'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Update wallet status successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error',
        ),
        new OA\Response(
            response: 500,
            description: 'Update wallet status failed',
        ),
    ],
)]

#[OA\Post(
    path: '/api/v1/wallet/cancel-transaction/{transaction_code}',
    tags: ['Admin / Wallet'],
    operationId: 'cancelPaymentLinkOfTransctionAdmin',
    summary: 'Cancel payment link of transaction',
    description: 'Cancel payment link of transaction',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'transaction_code',
            in: 'path',
            required: true,
            description: 'Mã giao dịch',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Cancel payment link of transaction successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ],
)]

#[OA\Get(
    path: '/api/v1/wallet/get-payment-link/{transaction_code}',
    operationId: 'getPaymentLinkAdmin',
    tags: ['Admin / Wallet'],
    summary: 'Get payment link',
    description: 'Get payment link',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'transaction_code',
            in: 'path',
            required: true,
            description: 'Mã giao dịch',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get payment link successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ]
)]


class WalletController extends Controller
{

    private string $payOSClientId;
    private string $payOSApiKey;
    private string $payOSChecksumKey;



    public function __construct()
    {
        $this->payOSClientId = env("PAYOS_CLIENT_ID");
        $this->payOSApiKey = env("PAYOS_API_KEY");
        $this->payOSChecksumKey = env("PAYOS_CHECKSUM_KEY");
    }

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

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Wallet::query()->with('user', 'user.role');

        $totalItems = $query->count();

        if ($search) {
            $wallets = $query->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        if ($status) {
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

    public function storeDeposit(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:10000',
            'description' => 'required|string',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 10,000 VND',
            'description.required' => 'Vui lòng nhập mô tả',
            'description.string' => 'Mô tả phải là chuỗi',
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
            'transaction_type' => 'deposit',
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
            $wallet->update([
                'balance' => $wallet->balance + $transaction->amount
            ]);

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

        $wallet = Wallet::with('user', 'user.role', 'transactions')->find($id);

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

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), [
            'id' => $id
        ]), [
            'id' => 'required|exists:wallets,id',
            'status' => 'required|string|in:active,locked,suspended,frozen',
            'reason' => 'required|string',
        ], [
            'id.required' => 'Vui lòng nhập ID',
            'id.exists' => 'ID không tồn tại',
            'status.required' => 'Vui lòng chọn trạng thái',
            'status.string' => 'Trạng thái phải là chuỗi',
            'status.in' => 'Trạng thái không hợp lệ',
            'reason.required' => 'Vui lòng nhập lý do',
            'reason.string' => 'Lý do phải là chuỗi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $wallet = Wallet::find($id);

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Wallet not found',
            ]);
        }

        $wallet->update([
            'status' => $request->status,
            'reason' => $request->reason,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Update wallet successfully',
            'data' => $wallet
        ]);
    }

    public function updateTransactionStatus(Request $request, $id)
    {
        $validator = Validator::make(array_merge($request->all(), [
            'id' => $id
        ]), [
            'id' => 'required|exists:wallet_transactions,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|string|in:active,locked,suspended,frozen',
        ], [
            'id.required' => 'Vui lòng nhập ID',
            'id.exists' => 'ID không tồn tại',
            'user_id.required' => 'Vui lòng nhập ID người dùng',
            'user_id.exists' => 'ID người dùng không tồn tại',
            'status.required' => 'Vui lòng chọn trạng thái',
            'status.string' => 'Trạng thái phải là chuỗi',
            'status.in' => 'Trạng thái không hợp lệ',
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

        if ($wallet->status != 'active') {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản đã bị khóa. Vui lòng liên hệ với quản trị viên để biết thêm chi tiết',
            ]);
        }

        $transaction = Wallet::where('user_id', $request->user_id)->first()->transactions()->find($id);

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Giao dịch không tồn tại',
            ]);
        }

        if ($transaction->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Giao dịch đã được xử lý',
            ]);
        }

        try {
            $transaction->update([
                'status' => $request->status,
                'completed_at' => now()
            ]);

            $wallet = Wallet::where('user_id', auth()->user()->id)->first();

            if ($request->status == 'completed') {
                $wallet->update([
                    'balance' => $wallet->balance + $transaction->amount
                ]);
            }

            return response()->json([
                "status" => true,
                "message" => "Success",
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "data" => null
            ]);
        }
    }

    public function cancelPaymentLinkOfTransction(Request $request, string $transaction_code)
    {
        $validator = Validator::make(['transaction_code' => $transaction_code], [
            'transaction_code' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ], [
            'transaction_code.required' => 'Vui lòng nhập mã giao dịch',
            'transaction_code.string' => 'Mã giao dịch phải là chuỗi',
            'user_id.required' => 'Vui lòng nhập ID người dùng',
            'user_id.exists' => 'ID người dùng không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $transaction = Wallet::where('user_id', $request->user_id)->first()->transactions()->where('transaction_code', $transaction_code)->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Giao dịch không tồn tại',
            ]);
        }

        $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);
        try {
            $response = $payOS->cancelPaymentLink($transaction_code);

            WalletTransaction::where('transaction_code', $transaction_code)->update([
                'status' => 'canceled',
                'completed_at' => now()
            ]);

            return response()->json([
                'status' => true,
                "message" => "Success",
                "data" => $response
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ]);
        }
    }

    public function getPaymentLink(Request $request, string $transaction_code)
    {
        $validator = Validator::make(['transaction_code' => $transaction_code], [
            'transaction_code' => 'required|string|exists:wallet_transactions,transaction_code',
            'user_id' => 'required|exists:users,id',
        ], [
            'transaction_code.required' => 'Vui lòng nhập mã giao dịch',
            'transaction_code.string' => 'Mã giao dịch phải là chuỗi',
            'transaction_code.exists' => 'Mã giao dịch không tồn tại',
            'user_id.required' => 'Vui lòng nhập ID người dùng',
            'user_id.exists' => 'ID người dùng không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $transaction = Wallet::where('user_id', $request->user_id)->first()->transactions()->where('transaction_code', $transaction_code)->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Giao dịch không tồn tại',
            ]);
        }

        if ($transaction->status != 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Giao dịch đã được xử lý',
            ]);
        }

        $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

        try {
            $response = $payOS->getPaymentLinkInformation($transaction_code);

            return response()->json([
                "status" => true,
                "message" => "Success",
                "data" => $response
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "data" => null
            ]);
        }
    }
}
