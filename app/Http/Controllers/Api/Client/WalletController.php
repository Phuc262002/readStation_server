<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PayOS\PayOS;
use OpenApi\Attributes as OA;

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
    path: '/api/v1/account/wallet/get-payment-link/{id}',
    operationId: 'getPaymentLink',
    tags: ['Wallet'],
    summary: 'Transaction history',
    description: 'Transaction history',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Mã giao dịch',
            schema: new OA\Schema(type: 'integer')
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

#[OA\Put(
    path: '/api/v1/account/wallet/update-transaction-status/{id}',
    tags: ['Wallet'],
    operationId: 'updateTransactionStatus',
    summary: 'Update transaction status',
    description: 'Update transaction status',
    security: [
        ['bearerAuth' => []]
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

        $wallet = Wallet::where('user_id', auth()->user()->id)->first();

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
            'transaction_method' => 'online',
            'status' => 'pending'
        ]);

        if (!$transaction) {
            return response()->json([
                'error' => false,
                'message' => 'Giao dịch thất bại',
            ]);
        }

        $body = $request->input();

        $body["amount"] = intval($body["amount"]);
        $body["description"] = $body["description"];
        $body["orderCode"] = $transaction_code;
        $body["returnUrl"] = env('APP_URL') . "/success.html";
        $body["cancelUrl"] = env('APP_URL') . "/cancel.html";


        $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

        try {
            $response = $payOS->createPaymentLink($body);

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

    public function getPaymentLink($id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|numeric',
        ], [
            'id.required' => 'Vui lòng nhập mã giao dịch',
            'id.numeric' => 'Mã giao dịch phải là số',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 1,
                'message' => $validator->errors(),
            ]);
        }

        $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

        try {
            $response = $payOS->getPaymentLinkInformation($id);

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

    public function updateTransactionStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:completed,failed,canceled',
        ], [
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

        $transaction = Wallet::where('user_id', auth()->user()->id)->first()->transactions()->where('transaction_code', $id)->first();

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
                'status' => $request->status
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
}
