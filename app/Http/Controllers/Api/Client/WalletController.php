<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PayOS\PayOS;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/account/wallet/statistic',
    operationId: 'statistic',
    tags: ['Wallet'],
    summary: 'Statistic',
    description: 'Statistic',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Statistic fetched successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ]
)]

#[OA\Get(
    path: '/api/v1/account/wallet/transaction-history',
    operationId: 'transactionHistory',
    tags: ['Wallet'],
    summary: 'Transaction history',
    description: 'Transaction history',
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
            name: 'sort',
            in: 'query',
            required: false,
            description: 'Sắp xếp theo tháng hoặc tất cả',
            schema: new OA\Schema(type: 'string', enum: ['inMonth', 'all'], default: 'inMonth')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Transaction history fetched successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        )
    ]
)]

#[OA\Post(
    path: '/api/v1/account/wallet/create-transaction',
    tags: ['Wallet'],
    operationId: 'storeDeposit',
    summary: 'Create transaction',
    description: 'Create transaction',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['amount', 'description', 'transaction_type'],
            properties: [
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
    path: '/api/v1/account/wallet/get-payment-link/{transaction_code}',
    operationId: 'getPaymentLink',
    tags: ['Wallet'],
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

#[OA\Put(
    path: '/api/v1/account/wallet/update-transaction-status/{transaction_code}',
    tags: ['Wallet'],
    operationId: 'updateTransactionStatus',
    summary: 'Update transaction status',
    description: 'Update transaction status',
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
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['status'],
            properties: [
                new OA\Property(property: 'status', type: 'string', description: 'Trạng thái', example: 'completed'),
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

#[OA\Post(
    path: '/api/v1/account/wallet/cancel-transaction/{transaction_code}',
    tags: ['Wallet'],
    operationId: 'cancelPaymentLinkOfTransction',
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

    public function statistic()
    {
        try {
            $wallet = Wallet::where('user_id', auth()->user()->id)->first();
            if (!$wallet) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ví chưa được kích hoạt',
                ]);
            }
            $balance = Wallet::where('user_id', auth()->user()->id)->first()->balance;
            $transactionsHolding = Wallet::with('transactions')->where('user_id', auth()->user()->id)->first()->transactions()->where('status', 'holding')->sum('amount');
            $transactionsPending = Wallet::with('transactions')->where('user_id', auth()->user()->id)->first()->transactions()->where('status', 'pending')->where('transaction_type', '!=', 'withdraw')->sum('amount');
            $transactionsWithdraw = Wallet::with('transactions')->where('user_id', auth()->user()->id)->first()->transactions()->where('transaction_type', 'withdraw')->sum('amount');

            return response()->json([
                'status' => true,
                'message' => 'Get statistic successfully',
                'data' => [
                    'balance' => $balance,
                    'transactionsHolding' => intval($transactionsHolding),
                    'transactionsPending' => intval($transactionsPending),
                    'transactionsWithdraw' => intval($transactionsWithdraw),
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function transactionHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'sort' => 'string|in:inMonth,all',
        ], [
            'page.integer' => 'Page phải là số nguyên.',
            'pageSize.integer' => 'PageSize phải là số nguyên.',
            'page.min' => 'Page phải lớn hơn hoặc bằng 1.',
            'pageSize.min' => 'PageSize phải lớn hơn hoặc bằng 1.',
            'sort.string' => 'Sort phải là chuỗi.',
            'sort.in' => 'Sort phải là inMonth hoặc all.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $wallet = Wallet::where('user_id', auth()->user()->id)->first();

        if (!$wallet) {
            return response()->json([
                'status' => false,
                'message' => 'Tài khoản không tồn tại',
            ]);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $sort = $request->input('sort', 'inMonth');

        if ($sort == 'inMonth') {
            $transactions = $wallet->transactions()
                ->whereMonth('created_at', now()->month)
                ->orderBy('created_at', 'desc')
                ->paginate($pageSize, ['*'], 'page', $page);
        } else {
            $transactions = $wallet->transactions()
                ->orderBy('created_at', 'desc')
                ->paginate($pageSize, ['*'], 'page', $page);
        }

        return response()->json([
            'status' => true,
            'message' => 'Success',
            'data' => [
                'transactions' => $transactions->items(),
                "page" => $transactions->currentPage(),
                "pageSize" => $transactions->perPage(),
                "totalPages" => $transactions->lastPage(),
                "totalResults" => $transactions->total(),
            ],
        ]);
    }

    public function storeDeposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:20000',
            'description' => 'required|string',
            'transaction_type' => 'required|string|in:deposit,withdraw',
        ], [
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 20,000 VND',
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

        $wallet = Wallet::where('user_id', auth()->user()->id)->first();

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

        $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

        $transaction = $wallet->transactions()->create([
            'wallet_id' => $wallet->id,
            'amount' => $request->amount,
            'description' => $request->description,
            'reference_id' => $transaction_code,
            'transaction_code' => $transaction_code,
            'transaction_type' => $request->transaction_type,
            'transaction_method' => 'online',
            'status' => 'pending'
        ]);

        if (!$transaction) {
            return response()->json([
                'error' => false,
                'message' => 'Giao dịch thất bại',
            ]);
        }


        try {
            if ($request->transaction_type == 'deposit') {
                $body = $request->input();

                $body["amount"] = intval($body["amount"]);
                $body["orderCode"] = $transaction_code;
                $body["description"] = $request->description;
                $body["expiredAt"] = now()->addMinutes(30)->getTimestamp();
                $body["returnUrl"] = "http://localhost:3000/account/wallet/transaction-success";
                $body["cancelUrl"] = "http://localhost:3000/account/wallet/transaction-error";

                $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);
                $response = $payOS->createPaymentLink($body);

                return response()->json([
                    "status" => true,
                    "message" => "Success",
                    "data" => $response
                ]);
            } else {
                return response()->json([
                    "status" => true,
                    "message" => "Success",
                    "data" => $transaction
                ]);
            }
        } catch (\Throwable $th) {
            $transaction->delete();
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "data" => null
            ]);
        }
    }

    public function getPaymentLink($transaction_code)
    {
        $validator = Validator::make(['transaction_code' => $transaction_code], [
            'transaction_code' => 'required|string|exists:wallet_transactions,transaction_code',
        ], [
            'transaction_code.required' => 'Vui lòng nhập mã giao dịch',
            'transaction_code.numeric' => 'Mã giao dịch phải là số',
            'transaction_code.exists' => 'Giao dịch không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 1,
                'message' => $validator->errors(),
            ]);
        }

        $transaction = Wallet::where('user_id', auth()->user()->id)->first()->transactions()->where('transaction_code', $transaction_code)->first();

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

    public function updateTransactionStatus(Request $request, $transaction_code)
    {
        $validator = Validator::make(array_merge($request->all(), [
            'transaction_code' => $transaction_code
        ]), [
            'transaction_code' => 'required|string|exists:wallet_transactions,transaction_code',
            'status' => 'required|string|in:completed,failed,canceled',
        ], [
            'transaction_code.required' => 'Vui lòng nhập mã giao dịch',
            'transaction_code.numeric' => 'Mã giao dịch phải là số',
            'transaction_code.exists' => 'Giao dịch không tồn tại',
            'status.required' => 'Vui lòng chọn trạng thái',
            'status.string' => 'Trạng thái phải là chuỗi',
            'status.in' => 'Trạng thái phải là completed, failed hoặc canceled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $wallet = Wallet::where('user_id', auth()->user()->id)->first();

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

        $transaction = Wallet::where('user_id', auth()->user()->id)->first()->transactions()->where('transaction_code', $transaction_code)->first();

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
        $validator = Validator::make(array_merge($request->all(), [
            'transaction_code' => $transaction_code
        ]), [
            'transaction_code' => 'required|string|exists:wallet_transactions,transaction_code',
        ], [
            'transaction_code.required' => 'Vui lòng nhập mã giao dịch',
            'transaction_code.numeric' => 'Mã giao dịch phải là số',
            'transaction_code.exists' => 'Giao dịch không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ]);
        }

        $wallet = Wallet::where('user_id', auth()->user()->id)->first();

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

        $transaction = Wallet::where('user_id', auth()->user()->id)->first()->transactions()->where('transaction_code', $transaction_code)->first();

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
}
