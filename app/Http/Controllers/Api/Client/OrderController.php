<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/account/order/get-all',
    operationId: 'getAllOrder',
    tags: ['Account'],
    summary: 'Get all order',
    description: 'Get all order',
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
            name: 'status',
            in: 'query',
            required: false,
            description: 'Trạng thái của order',
            schema: new OA\Schema(type: 'string', enum: ['pending', 'hiring', 'completed', 'canceled', 'out_of_date'])
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Mã đơn hàng cần tìm kiếm',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get all order successfully'
        )
    ]
)]

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer|min:1',
            'pageSize' => 'integer|min:1',
            'status' => 'string|in:pending,hiring,completed,canceled,out_of_date',
            'search' => 'string',
        ], [
            'page.integer' => 'Trường trang phải là kiểu số',
            'page.min' => 'Trường trang không được nhỏ hơn 1',
            'pageSize.integer' => 'Trường pageSize phải là kiểu số',
            'pageSize.min' => 'Trường pageSize không được nhỏ hơn 1',
            'status.string' => 'Trường status phải là kiểu chuỗi',
            'status.in' => 'Trường status phải là pending, hiring, completed, canceled hoặc out_of_date',
            'search.string' => 'Trường search phải là kiểu chuỗi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $search = $request->input('search');
        $status = $request->input('status');


        $query = Order::query()->with('orderDetails')->where('user_id', auth()->id());
        $totalItems = $query->count();
        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', ['pending', 'approved', 'wating_take_book', 'hiring', 'increasing', 'wating_return', 'completed', 'canceled', 'out_of_date']);
        } 

        if ($search) {
            $query->where('order_code', 'like', '%'.$search.'%');
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'status' => 200,
            'message' => 'Get all order successfully',
            "data" => [
                "books" => $orders->items(),
                "page" => $orders->currentPage(),
                "pageSize" => $orders->perPage(),
                "totalPages" => $orders->lastPage(),
                "totalResults" => $orders->total(),
                "total" => $totalItems
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Order $order)
    {
        //
    }
}
