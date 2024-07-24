<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/account/transactions',
    operationId: 'transactionIndex',
    tags: ['Account / Transaction'],
    summary: 'Get all transaction',
    description: 'Get all transaction',
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
            description: 'Sắp xếp theo tháng hoặc tất cả thời gian',
            schema: new OA\Schema(type: 'string', enum: ['inMonth', 'allTime'], default: 'allTime')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all order successfully',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/account/transactions/balance-holding',
    operationId: 'transactionBalanceHolding',
    tags: ['Account / Transaction'],
    summary: 'Get balance holding',
    description: 'Get balance holding',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get balance holding successfully',
        ),
        new OA\Response(
            response: 500,
            description: 'Failed to get balance holding',
        ),
    ]
)]

class TransactionController extends Controller
{
    public function getBalanceHolding()
    {
        try {
            $balanceHolding = Transaction::where('user_id', auth()->user()->id)->where('status', 'holding')->sum('amount');

            return response()->json([
                'status' => true,
                'message' => 'Get balance holding successfully',
                'data' => $balanceHolding
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get balance holding',
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'sort' => 'string|in:inMonth,allTime',
        ], [
            'page.integer' => 'Trường trang phải là kiểu số',
            'page.min' => 'Trường trang không được nhỏ hơn 1',
            'pageSize.integer' => 'Trường pageSize phải là kiểu số',
            'pageSize.min' => 'Trường pageSize không được nhỏ hơn 1',
            'sort.string' => 'Trường sort phải là kiểu chuỗi',
            'sort.in' => 'Trường sort phải thuộc các giá trị: inMonth, allTime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $sort = $request->input('sort', 'allTime');

        $query = Transaction::query();

        if ($sort == 'inMonth') {
            $query->whereMonth('created_at', now()->month);
        }

        $totalItems = $query->where('user_id', auth()->user()->id)->count();

        $transactions = $query->where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

            return response()->json([
                'status' => true,
                'message' => 'Get all order successfully',
                "data" => [
                    "transactions" => $transactions->items(),
                    "page" => $transactions->currentPage(),
                    "pageSize" => $transactions->perPage(),
                    "totalPages" => $transactions->lastPage(),
                    "totalResults" => $transactions->total(),
                    "total" => $totalItems
                ],
            ]);
    }
}
