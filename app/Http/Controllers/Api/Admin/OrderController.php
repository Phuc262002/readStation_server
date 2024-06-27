<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/v1/admin/orders/statistic',
    operationId: 'adminOrderStatistic',
    tags: ['Admin / Orders'],
    summary: 'Thống kê đơn hàng',
    description: 'Thống kê đơn hàng',
    security: [
        ['bearerAuth' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Thống kê đơn hàng',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 500,
            description: 'Lỗi không xác định'
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/admin/orders',
    operationId: 'adminOrderIndex',
    tags: ['Admin / Orders'],
    summary: 'Danh sách đơn hàng',
    description: 'Danh sách đơn hàng',
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
            description: 'Trạng thái đơn hàng',
            schema: new OA\Schema(
                type: 'string',
                enum: ['pending', 'approved', 'wating_take_book', 'hiring', 'increasing', 'wating_return', 'completed', 'canceled', 'out_of_date']
            )
        ),
        new OA\Parameter(
            name: 'search',
            in: 'query',
            required: false,
            description: 'Tìm kiếm theo mã đơn hàng, tên người dùng, email, số điện thoại',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Danh sách đơn hàng',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 500,
            description: 'Lỗi không xác định'
        ),
    ]
)]

#[OA\Get(
    path: '/api/v1/admin/orders/{id}',
    operationId: 'adminOrderShow',
    tags: ['Admin / Orders'],
    summary: 'Chi tiết đơn hàng',
    description: 'Chi tiết đơn hàng',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id đơn hàng',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Chi tiết đơn hàng',
        ),
        new OA\Response(
            response: 400,
            description: 'Validation error'
        ),
        new OA\Response(
            response: 500,
            description: 'Lỗi không xác định'
        ),
    ]
)]

class OrderController extends Controller
{
    public function statisticOrder()
    {
        $orders = LoanOrders::count();
        $ordersHiring = LoanOrders::where('status', 'active')->count();
        $ordersCompleted = LoanOrders::where('status', 'completed')->count();
        $ordersOutOfDate = LoanOrders::where('status', 'overdue')->count();
        $ordersPending = LoanOrders::where('status', 'pending')->count();
        $ordersApproved = LoanOrders::where('status', 'approved')->count();
        $ordersWatingTakeBook = LoanOrders::where('status', 'in_transit')->count();
        $ordersCanceled = LoanOrders::where('status', 'canceled')->count();


        return response()->json([
            'status' => true,
            'message' => 'Get statistic order success',
            'data' => [
                'orders' => $orders,
                'ordersHiring' => $ordersHiring,
                'ordersCompleted' => $ordersCompleted,
                'ordersOutOfDate' => $ordersOutOfDate,
                'ordersPending' => $ordersPending,
                'ordersApproved' => $ordersApproved,
                'ordersWatingTakeBook' => $ordersWatingTakeBook,
                'ordersCanceled' => $ordersCanceled,
            ]
        ]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'integer',
            'pageSize' => 'integer',
            'status' => 'in:pending,approved,ready_for_pickup,preparing_shipment,in_transit,extended,active,returning,completed,canceled,overdue',
            'search' => 'string',
            'sort' => 'in:asc,desc',
        ], [
            'page.integer' => 'Số trang phải là số nguyên',
            'pageSize.integer' => 'Số lượng phải là số nguyên',
            'status.in' => 'Trạng thái phải là pending,approved,ready_for_pickup,preparing_shipment,in_transit,extended,active,returning,completed,canceled,overdue',
            'search.string' => 'Tìm kiếm phải là chuỗi',
            'sort.in' => 'Sắp xếp phải là asc hoặc desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $status = $request->input('status');
        $search = $request->input('search');
        $sort = $request->input('sort', 'desc');

        $query = LoanOrders::query()->with(['user', 'loanOrderDetails']);

        if ($status) {
            $query->where('status', $status);
        }

        $total = $query->count();

        if ($search) {
            $query->where('order_code', 'like', "%$search%")->orWhereHas('user', function ($query) use ($search) {
                $query->where('fullname', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phone', 'like', "%$search%");
            });
        }


        $orders = $query->orderBy('id', $sort)->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([

            'data' => [
                'orders' => $orders->items(),
                'page' => $orders->currentPage(),
                'pageSize' => $orders->perPage(),
                'lastPage' => $orders->lastPage(),
                'totalResults' => $orders->total(),
                'total' =>  $total
            ]
        ]);
    }

    public function show(Request $request, $id)
    {
        $validator = Validator::make(['id' => $id], [
            'id' => 'required|integer|exists:loan_orders,id'
        ], [
            'id.required' => 'Id không được để trống',
            'id.integer' => 'Id phải là số nguyên',
            'id.exists' => 'Id không tồn tại'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }


        try {
            $order = LoanOrders::with([
                'user',
                'shippingMethod',
                'loanOrderDetails',
                'loanOrderDetails.bookDetails',
                'loanOrderDetails.extensionsDetails',
                // 'loanOrderDetails.bookDetails.publishingCompany',
                // 'loanOrderDetails.bookDetails.publishingCompany',
                'loanOrderDetails.bookDetails.book',
                // 'loanOrderDetails.bookDetails.book.author',
                // 'loanOrderDetails.bookDetails.book.category',
                // 'loanOrderDetails.bookDetails.book.shelve',
                // 'loanOrderDetails.bookDetails.book.shelve.bookcase',
                'transaction',
                'extensions',
                // 'extensions.extensionDetails',
            ])->find($id);

            return response()->json([
                'status' => true,
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Lỗi không xác định',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, LoanOrders $order)
    {
        //
    }

    public function destroy(LoanOrders $order)
    {
        //
    }
}
