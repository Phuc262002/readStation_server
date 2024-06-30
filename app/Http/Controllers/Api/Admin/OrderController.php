<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanOrders;
use App\Models\Wallet;
use App\Models\Transaction;
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
                enum: ['pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'extended', 'active', 'returning', 'completed', 'canceled', 'overdue']
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

#[OA\Put(
    path: '/api/v1/admin/orders/update/{id}',
    operationId: 'adminOrderUpdate',
    tags: ['Admin / Orders'],
    summary: 'Cập nhật trạng thái đơn hàng',
    description: 'Cập nhật trạng thái đơn hàng',
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
        new OA\Parameter(
            name: 'status',
            in: 'query',
            required: true,
            description: 'Trạng thái đơn hàng',
            schema: new OA\Schema(
                type: 'string',
                enum: ['pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'extended', 'active', 'returning', 'completed', 'canceled']
            )
        ),
        new OA\Parameter(
            name: 'reason_cancel',
            in: 'query',
            required: false,
            description: 'Lý do hủy đơn hàng',
            schema: new OA\Schema(type: 'string')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Cập nhật trạng thái đơn hàng',
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
                'loanOrderDetails.bookDetails.publishingCompany',
                'loanOrderDetails.bookDetails.book',
                'loanOrderDetails.bookDetails.book.author',
                'loanOrderDetails.bookDetails.book.category',
                'loanOrderDetails.bookDetails.book.shelve',
                'loanOrderDetails.bookDetails.book.shelve.bookcase',
                'transaction',
                'extensions',
                'extensions.extensionDetails',
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make(array_merge(['id' => $id], $request->all()), [
            'id' => 'required|integer|min:1|exists:loan_orders,id',
            'status' => 'required|string|in:pending,approved,ready_for_pickup,preparing_shipment,in_transit,extended,active,returning,completed,canceled,overdue',
            'reason_cancel' => 'required_if:status,canceled',
        ], [
            'id.required' => 'Trường id là bắt buộc.',
            'id.integer' => 'Id phải là một số nguyên.',
            'id.min' => 'Id phải lớn hơn hoặc bằng 1.',
            'id.exists' => 'Id không tồn tại.',
            'status.required' => 'Trường status là bắt buộc.',
            'status.string' => 'Trường status phải là chuỗi.',
            'status.in' => 'Trường status phải là pending,approved,ready_for_pickup,preparing_shipment,in_transit,extended,active,completed,canceled',
            'reason_cancel.required_if' => 'Lý do hủy đơn hàng là bắt buộc.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Validation error",
                "errors" => $validator->errors()
            ], 400);
        }

        switch ($request->status) {
            case 'approved':
                return $this->approvedOrder($id);
                break;
            case 'ready_for_pickup':
                return $this->readyForPickupOrder($id);
                break;
            case 'preparing_shipment':
                return $this->preparingShipmentOrder($id);
                break;
            case 'in_transit':
                return $this->inTransitOrder($id);
                break;
            case 'extended':
                break;
            case 'active':
                return $this->activeOrder($id);
                break;
            case 'completed':
                return $this->completedOrder($id);
                break;
            case 'canceled':
                return $this->cancelOrder($id, $request);
                break;
            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Trạng thái không hợp lệ',
                ], 400);
                break;
        }
    }

    public function approvedOrder($id)
    {
        try {
            $order = LoanOrders::find($id);

            if ($order->status != 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái chờ duyệt',
                ], 400);
            }

            $order->update([
                'status' => 'approved'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Duyệt đơn hàng thành công',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Duyệt đơn hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function cancelOrder($id, $request)
    {
        try {
            $order = LoanOrders::find($id);

            if ($order->status != 'pending') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái chờ duyệt',
                ], 400);
            }

            foreach ($order->loanOrderDetails as $orderDetail) {
                $orderDetail->bookDetails->update([
                    'stock' => $orderDetail->bookDetails->stock + 1
                ]);

                $orderDetail->update([
                    'status' => 'canceled',
                    'reason_cancel' => $request->reason_cancel
                ]);
            }

            $order->update([
                'status' => 'canceled',

            ]);

            $transaction = Transaction::find($order->transaction_id);

            if ($transaction) {
                $transaction->update([
                    'status' => 'canceled'
                ]);

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));
                $transaction = Transaction::create([
                    'transaction_code' => $transaction_code,
                    'transaction_type' => 'refund',
                    'transaction_method' => 'wallet',
                    'amount' => $transaction->amount,
                    'status' => 'completed',
                    'completed_at' => now(),
                    'description' => 'Hoàn tiền đơn thuê ' . $order->order_code,
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Hủy đơn hàng thành công',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Hủy đơn hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function readyForPickupOrder($id)
    {
        try {
            $order = LoanOrders::find($id);

            if ($order->delivery_method == 'shipper') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không phải là hình thức nhận sách tại thư viện',
                ], 400);
            }

            if ($order->status != 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái đã duyệt',
                ], 400);
            }

            $order->update([
                'status' => 'ready_for_pickup'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Sách đã sẵn sàng để lấy',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Sách đã sẵn sàng để lấy',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function preparingShipmentOrder($id)
    {
        try {
            $order = LoanOrders::find($id);

            if ($order->delivery_method == 'pickup') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không phải là hình thức giao hàng',
                ], 400);
            }

            if ($order->status != 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái đã duyệt',
                ], 400);
            }

            $order->update([
                'status' => 'preparing_shipment'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Chuẩn bị giao hàng thành công',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Chuẩn bị giao hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function inTransitOrder($id)
    {
        try {
            $order = LoanOrders::find($id);

            if ($order->delivery_method == 'pickup') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không phải là hình thức giao hàng',
                ], 400);
            }

            if ($order->status != 'approved') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái đã duyệt',
                ], 400);
            }

            $order->update([
                'status' => 'in_transit'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Đang giao hàng',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Đang giao hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function activeOrder($id)
    {
        try {
            $order = LoanOrders::find($id);

            if ($order->status == 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng đã được mượn',
                ], 400);
            }

            if ($order->status != 'ready_for_pickup' && $order->status != 'in_transit') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái đã sẵn sàng để lấy hoặc đang giao',
                ], 400);
            }

            foreach ($order->loanOrderDetails as $orderDetail) {
                $orderDetail->update([
                    'status' => 'active',
                    'original_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 4 days')),
                    'current_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 4 days')),
                ]);
            }

            $order->update([
                'status' => 'active',
                'loan_date' => now(),
                'pickup_date' => now(),
                'delivered_date' => now(),
                'original_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 4 days')),
                'current_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + 4 days')),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Đơn hàng đang mượn',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Đơn hàng đang mượn thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function completedOrder($id)
    {
        // try {
        //     $order = LoanOrders::find($id);

        //     if ($order->status != 'active') {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Đơn hàng không ở trạng thái đang mượn',
        //         ], 400);
        //     }

        //     $order->update([
        //         'status' => 'completed'
        //     ]);

        //     return response()->json([
        //         'status' => true,
        //         'message' => 'Hoàn thành đơn hàng',
        //     ]);
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Hoàn thành đơn hàng thất bại',
        //         'errors' => $th->getMessage()
        //     ], 500);
        // }

        return response()->json([
            'status' => false,
            'message' => 'Chức năng này chưa được hỗ trợ',
        ], 400);
    }
}
