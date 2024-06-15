<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/v1/account/wallet/admin/create/{user_id}',
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

class WalletController extends Controller
{

    public function store(Request $request, $user_id)
    {


        $validator = Validator::make(array_merge(
            ['user_id' => $user_id],
            $request->all()
        ), [
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

        $wallet = Wallet::where('user_id', $user_id)->first();

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
}
