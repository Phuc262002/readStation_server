<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\VNPay\VnpayCreatePayment;
use App\Http\Controllers\Controller;
use App\Mail\Order;
use App\Models\BookDetail;
use App\Models\ExtensionDetails;
use App\Models\Extensions;
use App\Models\LoanOrderDetails;
use App\Models\LoanOrders;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use PayOS\PayOS;
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
            description: 'Dữ liệu không hợp lệ'
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
            description: 'Dữ liệu không hợp lệ'
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
            description: 'Dữ liệu không hợp lệ'
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
                enum: ['pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'extended', 'active', 'returning', 'completed', 'canceled', 'overdue']
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
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 500,
            description: 'Lỗi không xác định'
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/admin/orders/store-has-user',
    operationId: 'adminOrderStoreHasUser',
    tags: ['Admin / Orders'],
    summary: 'Create order',
    description: 'Create order',
    security: [
        ['bearerAuth' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['user_id', 'payment_method', 'total_service_fee', 'total_deposit_fee', 'total_all_fee', 'order_details'],
            properties: [
                new OA\Property(property: 'user_id', type: 'string', description: 'Id người dùng'),
                new OA\Property(property: 'payment_method', type: 'string', description: 'Phương thức thanh toán', enum: ['online', 'cash']),
                new OA\Property(property: 'total_service_fee', type: 'integer', description: 'Tổng phí dịch vụ'),
                new OA\Property(property: 'total_deposit_fee', type: 'number', description: 'Tiền đặt cọc'),
                new OA\Property(property: 'total_all_fee', type: 'number', description: 'Tổng tiền'),
                new OA\Property(
                    property: 'order_details',
                    type: 'array',
                    description: 'Chi tiết đơn hàng',
                    items: new OA\Items(
                        type: 'object',
                        required: ['book_details_id', 'service_fee', 'deposit'],
                        properties: [
                            new OA\Property(property: 'book_details_id', type: 'integer', description: 'Id chi tiết sách'),
                            new OA\Property(property: 'service_fee', type: 'integer', description: 'Phí dịch vụ'),
                            new OA\Property(property: 'deposit_fee', type: 'number', description: 'Tiền đặt cọc')
                        ]
                    )
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Create order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Create order failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/admin/orders/return-each-book/{id}',
    operationId: 'adminOrderReturnEachBook',
    tags: ['Admin / Orders'],
    summary: 'Trả từng sách',
    description: 'Trả từng sách',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id chi tiết đơn hàng',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['condition', 'actual_return_condition', 'fine_amount'],
            properties: [
                new OA\Property(property: 'condition', type: 'string', description: 'Tình trạng sách'),
                new OA\Property(property: 'actual_return_condition', type: 'string', description: 'Tình trạng thực tế khi trả sách'),
                new OA\Property(property: 'fine_amount', type: 'number', description: 'Số tiền phạt')
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Trả từng sách',
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ'
        ),
        new OA\Response(
            response: 500,
            description: 'Lỗi không xác định'
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/admin/orders/extension-all/{id}',
    operationId: 'extensionAllOrderAdmin',
    tags: ['Admin / Orders'],
    summary: 'Extension all order',
    description: 'Extension all order',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của order',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Extension all order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Extension all order failed',
        ),
    ]
)]

#[OA\Post(
    path: '/api/v1/admin/orders/extension-each-book/{id}',
    operationId: 'extensionEachBookAdmin',
    tags: ['Admin / Orders'],
    summary: 'Extension each book',
    description: 'Extension each book',
    security: [
        ['bearerAuth' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'Id của order chi tiết',
            schema: new OA\Schema(type: 'integer')
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Extension all order successfully'
        ),
        new OA\Response(
            response: 400,
            description: 'Dữ liệu không hợp lệ',
        ),
        new OA\Response(
            response: 500,
            description: 'Extension all order failed',
        ),
    ]
)]


class OrderController extends Controller
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
            'status' => 'in:wating_payment,pending,approved,ready_for_pickup,preparing_shipment,in_transit,extended,active,returning,completed,canceled,overdue',
            'search' => 'string',
            'sort' => 'in:asc,desc',
        ], [
            'page.integer' => 'Số trang phải là số nguyên',
            'pageSize.integer' => 'Số lượng phải là số nguyên',
            'status.in' => 'Trạng thái phải là wating_payment,pending,approved,ready_for_pickup,preparing_shipment,in_transit,extended,active,returning,completed,canceled,overdue',
            'search.string' => 'Tìm kiếm phải là chuỗi',
            'sort.in' => 'Sắp xếp phải là asc hoặc desc',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
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
            $query->whereHas('loanOrderDetails', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%$search%")
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('fullname', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%");
                    });
            });
        }

        $total = $query->count();

        $orders = $query->orderBy('id', $sort)->paginate($pageSize, ['*'], 'page', $page);

        return response()->json([
            'data' => [
                'orders' => $orders->items(),
                'page' => $orders->currentPage(),
                'pageSize' => $orders->perPage(),
                'lastPage' => $orders->lastPage(),
                'totalResults' => $orders->total(),
                'total' => $total
            ]
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'payment_method' => 'required|string|in:online,cash',
            'user_note' => 'nullable|string',
            'total_deposit_fee' => 'required|numeric|min:0',
            'total_service_fee' => 'required|integer|min:0',
            'total_all_fee' => 'required|numeric|min:10000',

            'order_details' => 'required|array',
            'order_details.*.book_details_id' => 'required|integer',
            'order_details.*.service_fee' => 'required|integer|min:0',
            'order_details.*.deposit_fee' => 'required|numeric|min:0',
            'order_details.*.number_of_days' => 'required|integer|min:1',
        ], [
            'user_id.required' => 'Trường id người dùng là bắt buộc',
            'user_id.exists' => 'Id người dùng không tồn tại',
            'payment_method.required' => 'Trường phương thức thanh toán là bắt buộc',
            'payment_method.string' => 'Trường phương thức thanh toán phải là kiểu chuỗi',
            'payment_method.in' => 'Trường phương thức thanh toán phải là online hoặc cash',
            'user_note.string' => 'Trường ghi chú phải là kiểu chuỗi',
            'total_deposit_fee.required' => 'Trường tiền đặt cọc là bắt buộc',
            'total_deposit_fee.numeric' => 'Trường tiền đặt cọc phải là kiểu số',
            'total_deposit_fee.min' => 'Trường tiền đặt cọc không được nhỏ hơn 0',

            'total_service_fee.required' => 'Trường tổng phí dịch vụ là bắt buộc',
            'total_service_fee.integer' => 'Trường tổng phí dịch vụ phải là kiểu số',
            'total_service_fee.min' => 'Trường tổng phí dịch vụ không được nhỏ hơn 0',
            'total_all_fee.required' => 'Trường tổng tiền là bắt buộc',
            'total_all_fee.numeric' => 'Trường tổng tiền phải là kiểu số',
            'total_all_fee.min' => 'Trường tổng tiền không được nhỏ hơn 10,000',

            'order_details.required' => 'Trường chi tiết đơn hàng là bắt buộc',
            'order_details.array' => 'Trường chi tiết đơn hàng phải là kiểu mảng',
            'order_details.*.book_details_id.required' => 'Trường id chi tiết sách là bắt buộc',
            'order_details.*.book_details_id.integer' => 'Trường id chi tiết sách phải là kiểu số',
            'order_details.*.service_fee.required' => 'Trường phí dịch vụ là bắt buộc',
            'order_details.*.service_fee.integer' => 'Trường phí dịch vụ phải là kiểu số',
            'order_details.*.service_fee.min' => 'Trường phí dịch vụ không được nhỏ hơn 1',
            'order_details.*.deposit.required' => 'Trường tiền đặt cọc là bắt buộc',
            'order_details.*.deposit.numeric' => 'Trường tiền đặt cọc phải là kiểu số',
            'order_details.*.deposit.min' => 'Trường tiền đặt cọc không được nhỏ hơn 0',
            'order_details.*.number_of_days.required' => 'Trường số ngày là bắt buộc',
            'order_details.*.number_of_days.integer' => 'Trường số ngày phải là kiểu số',
            'order_details.*.number_of_days.min' => 'Trường số ngày không được nhỏ hơn 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {

            $allOrder = LoanOrders::where('user_id', $request->user_id)->get();

            foreach ($allOrder as $order) {
                if (in_array($order->status, ['wating_payment', 'pending', 'approved', 'ready_for_pickup', 'preparing_shipment', 'in_transit', 'active', 'extended', 'returning', 'overdue'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Bạn hiện đang có đơn hàng đang chờ xử lý, vui lòng chờ đơn hàng hiện tại được xử lý xong',
                    ]);
                }
            }

            $bookDetailsIds = collect($request->order_details)->pluck('book_details_id')->toArray();

            foreach ($bookDetailsIds as $bookDetailsId) {
                $bookDetail = BookDetail::find($bookDetailsId);

                if (!$bookDetail) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Id chi tiết sách không tồn tại',
                    ]);
                }

                if ($bookDetail->status !== 'active') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Sách không còn trạng thái cho thuê',
                    ]);
                }

                if ($bookDetail->stock < 1) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Sách đã hết hàng',
                    ]);
                }
            }

            if ($request->payment_method === 'online') {
                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $order = LoanOrders::create(array_merge($request->all(), [
                    'pickup_date' => now(),
                    'user_id' => $request->user_id,
                    'status' => 'wating_payment',
                    'delivery_method' => 'pickup',
                ]));

                $orderDetails = $request->order_details;

                $order->loanOrderDetails()->createMany($orderDetails);

                $orderDetails = $order->loanOrderDetails;

                foreach ($orderDetails as $key => $detail) {
                    $orderDetails[$key]->update([
                        'original_due_date' => date('Y-m-d', strtotime('+' . $detail->number_of_days . ' days')),
                        'current_due_date' => date('Y-m-d', strtotime('+' . $detail->number_of_days . ' days')),
                        'status' => 'active',
                    ]);
                }

                $transaction = Transaction::create([
                    'user_id' => $request->user_id,
                    'transaction_code' => $transaction_code,
                    'transaction_type' => 'payment',
                    'loan_order_id' => $order->id,
                    'portal' => 'vnpay',
                    'expired_at' => now()->addMinutes(30),
                    'transaction_method' => 'online',
                    'amount' => $request->total_all_fee,
                    'description' => 'Thanh toán đơn thuê ' . $order->order_code,
                ]);

                if (!$transaction) {
                    $order->delete();
                }

                // $body = $request->input();
                // $body["amount"] = intval($transaction->amount);
                // $body["orderCode"] = intval($transaction->transaction_code);
                // $body["description"] =  $order->order_code;
                // $body["expiredAt"] = now()->addMinutes(30)->getTimestamp();
                // $body["returnUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&amount=" . $transaction->amount . "&description=" . $transaction->transaction_code;
                // $body["cancelUrl"] = env('CLIENT_URL') . "/payment/result?portal=payos&amount=" . $transaction->amount . "&description=" . $transaction->transaction_code;
                // $payOS = new PayOS($this->payOSClientId, $this->payOSApiKey, $this->payOSChecksumKey);

                // $response = $payOS->createPaymentLink($body);

                $vnpay = new VnpayCreatePayment();

                $response = $vnpay->createPaymentLink($transaction->amount, $transaction->transaction_code, "Thanh toán đơn hàng " . $transaction->transaction_code, true);

                $transaction->update([
                    'status' => 'pending',
                    'expired_at' => now()->addMinutes(30),
                    'extra_info' => [
                        'checkoutUrl' => $response
                    ]
                ]);
            } else {
                $order = LoanOrders::create(array_merge($request->all(), [
                    'user_id' => $request->user_id,
                    'pickup_date' => now(),
                    'delivery_method' => 'pickup',
                    'loan_date' => now(),
                    'status' => 'active',
                    'total_return_fee' => $request->total_deposit_fee
                ]));

                $orderDetails = $request->order_details;

                $order->loanOrderDetails()->createMany($orderDetails);
                $orderDetails = $order->loanOrderDetails;

                foreach ($orderDetails as $key => $detail) {
                    $orderDetails[$key]->update([
                        'original_due_date' => date('Y-m-d', strtotime('+' . $detail->number_of_days . ' days')),
                        'current_due_date' => date('Y-m-d', strtotime('+' . $detail->number_of_days . ' days')),
                        'status' => 'active',
                    ]);
                }

                $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

                $transaction = Transaction::create([
                    'user_id' => $request->user_id,
                    'transaction_code' => $transaction_code,
                    'portal' => 'payos',
                    'loan_order_id' => $order->id,
                    'transaction_type' => 'payment',
                    'expired_at' => now(),
                    'transaction_method' => 'offline',
                    'amount' => $request->total_all_fee,
                    'description' => 'Thanh toán tiền mặt đơn thuê ' . $order->order_code,
                    'status' => 'completed',
                    "completed_at" => now()
                ]);

                if (!$transaction) {
                    $order->delete();
                }
            }

            foreach ($bookDetailsIds as $bookDetailsId) {
                $bookDetail = BookDetail::find($bookDetailsId);
                $bookDetail->update([
                    'stock' => $bookDetail->stock - 1
                ]);
            }

            $order = LoanOrders::with(['transactions', 'loanOrderDetails', 'loanOrderDetails.bookDetails', 'loanOrderDetails.bookDetails.book'])->find($order->id);

            Mail::to($order->user->email)->send(new Order($order));

            return response()->json([
                'status' => true,
                'message' => 'Create order successfully',
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Create order failed',
                'errors' => $th->getMessage()
            ]);
        }
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
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }


        try {
            $order = LoanOrders::with([
                'user',
                'user.role',
                'shippingMethod',
                'loanOrderDetails',
                'loanOrderDetails.bookDetails',
                'loanOrderDetails.bookReviews',
                'loanOrderDetails.returnHistories',
                'loanOrderDetails.extensionsDetails',
                'loanOrderDetails.bookDetails.publishingCompany',
                'loanOrderDetails.bookDetails.book',
                'loanOrderDetails.bookDetails.book.author',
                'loanOrderDetails.bookDetails.book.category',
                'loanOrderDetails.bookDetails.book.shelve',
                'loanOrderDetails.bookDetails.book.shelve.bookcase',
                'transactions',
                'extensions',
                'extensions.approvedBy',
                'extensions.feeTransaction',
                'extensions.extensionDetails',
                'extensions.extensionDetails.loanOrderDetail',
                'extensions.extensionDetails.loanOrderDetail.bookDetails',
                'extensions.extensionDetails.loanOrderDetail.bookDetails.book',
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
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        switch ($request->status) {
            case 'pending':
                return $this->paymentDoneOrder($id);
                break;
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

    public function paymentDoneOrder($id)
    {
        try {
            $order = LoanOrders::with('loanOrderDetails')->find($id);

            if ($order->status != 'wating_payment') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng không ở trạng thái chờ thanh toán',
                ], 400);
            }

            $order->update([
                'status' => 'approved'
            ]);

            $transaction = Transaction::where('loan_order_id', $order->id)->first();
            
            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Thanh toán đơn hàng thành công',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Thanh toán đơn hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
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
                ]);
            }

            $order->update([
                'status' => 'canceled',
                'reason_cancel' => $request->reason_cancel
            ]);

            $transaction = Transaction::where('loan_order_id', $order->id)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'canceled'
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
                'status' => 'ready_for_pickup',
                'pickup_date' => now(),
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
                'status' => 'preparing_shipment',
                'pickup_date' => now(),
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

            $order->update([
                'status' => 'in_transit',
                'pickup_date' => now(),
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
                    'original_due_date' => now()->addDays($orderDetail->number_of_days)->format('Y-m-d'),
                    'current_due_date' => now()->addDays($orderDetail->number_of_days)->format('Y-m-d'),
                ]);
            }

            $order->update([
                'status' => 'active',
                'loan_date' => now(),
                'delivered_date' => now(),
                'total_return_fee' => $order->total_deposit_fee
            ]);

            $transaction = Transaction::where('loan_order_id', $order->id)->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
            }

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
        try {
            $order = LoanOrders::find($id);

            if ($order->status != 'active') {
                return response()->json([
                    'status' => false,
                    'message' => 'Đơn hàng đã hoàn thành',
                ], 400);
            }

            return response()->json($order->loanOrderDetails);

            foreach ($order->loanOrderDetails as $orderDetail) {
                $orderDetail->update([
                    'status' => 'completed',
                    'return_date' => now(),
                    'actual_return_condition' => 'good'
                ]);

                $orderDetail->bookDetails->update([
                    'stock' => $orderDetail->bookDetails->stock + 1
                ]);

                $orderDetail->returnHistories()->create([
                    'return_date' => now(),
                    'condition' => 'good',
                    'processed_by' => auth()->id(),
                    'return_method' => 'pickup',
                    'pickup_info' => [
                        'pickup_date' => now(),
                        'received_at_library_date' => now(),
                    ],
                    'status' => 'completed',
                ]);
            }

            $order->update([
                'status' => 'completed',
                'completed_date' => now()
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Hoàn thành đơn hàng',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Hoàn thành đơn hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }

        return response()->json([
            'status' => false,
            'message' => 'Chức năng này chưa được hỗ trợ',
        ], 400);
    }

    public function returnEachBook(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['loan_order_details_id' => $id]
        ), [
            'loan_order_details_id' => 'required|exists:loan_order_details,id',
            'condition' => 'nullable|string',
            'actual_return_condition' => 'required|string|in:excellent,good,fair,poor,damaged,lost',
            'fine_amount' => 'required_if:actual_return_condition,poor,damaged,lost|numeric|min:0',
        ], [
            'loan_order_details_id.required' => 'Trường id chi tiết đơn hàng là bắt buộc',
            'loan_order_details_id.integer' => 'Trường id chi tiết đơn hàng phải là kiểu số',
            'loan_order_details_id.exists' => 'Id chi tiết đơn hàng không tồn tại',
            'condition.string' => 'Trường condition phải là kiểu chuỗi',
            'actual_return_condition.required' => 'Trường tình trạng trả sách là bắt buộc',
            'actual_return_condition.string' => 'Trường tình trạng trả sách phải là kiểu chuỗi',
            'actual_return_condition.in' => 'Trường tình trạng trả sách phải là excellent,good,fair,poor,damaged,lost',
            'fine_amount.required_if' => 'Trường tiền phạt là bắt buộc',
            'fine_amount.numeric' => 'Trường tiền phạt phải là kiểu số',
            'fine_amount.min' => 'Trường tiền phạt không được nhỏ hơn 0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        $orderDetail = LoanOrderDetails::find($id);

        if ($orderDetail->status != 'active' && $orderDetail->status != 'extended') {
            return response()->json([
                'status' => false,
                'message' => 'Chi tiết đơn hàng không ở trạng thái đang mượn',
            ], 400);
        }

        try {
            $orderDetail->createReturnHistory(array_merge($request->all(), [
                'processed_by' => auth()->id(),
                'return_date' => now(),
                'return_method' => 'library',
                'received_at_library_date' => now(),
                'status' => $request->actual_return_condition == 'lost' ? 'lost' : 'completed',
            ]));

            $orderDetail->update([
                'status' => 'completed',
                'actual_return_condition' => $request->actual_return_condition,
                'fine_amount' => $request->fine_amount ?? 0,
                'return_date' => now(),
            ]);

            if (in_array($request->actual_return_condition, ['good', 'excellent'])) {
                $orderDetail->bookDetails->update([
                    'stock' => $orderDetail->bookDetails->stock + 1,
                ]);
            } else {
                $orderDetail->bookDetails->update([
                    'stock_broken' => $orderDetail->bookDetails->stock_broken + 1,
                ]);
            }            

            $order = LoanOrders::find($orderDetail->loan_order_id);

            if ($order->loanOrderDetails->filter(function ($detail) {
                return $detail->status === 'active' || $detail->status === 'extended';
            })->count() == 0) {
                $order->update([
                    'status' => 'completed',
                    'completed_date' => now(),
                ]);

                Transaction::create([
                    'user_id' => auth()->user()->id,
                    'transaction_code' => intval(substr(strtotime(now()) . rand(1000, 9999), -9)),
                    'portal' => null,
                    'loan_order_id' => $order->id,
                    'expired_at' => now(),
                    'transaction_type' => 'refund',
                    'transaction_method' => 'offline',
                    'amount' => $order->total_deposit_fee - LoanOrderDetails::where('loan_order_id', $order->id)->sum('fine_amount') > 0 ? $order->total_deposit_fee - LoanOrderDetails::where('loan_order_id', $order->id)->sum('fine_amount') : 0,
                    'description' => 'Hoàn tiền đơn hàng ' . $order->order_code,
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }


            $order->update([
                'total_fine_fee' => $order->loanOrderDetails->sum('fine_amount'),
                'total_return_fee' => $order->total_deposit_fee - $order->loanOrderDetails->sum('fine_amount')
            ]);



            return response()->json([
                'status' => true,
                'message' => 'Trả sách thành công',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Hoàn thành đơn hàng thất bại',
                'errors' => $th->getMessage()
            ], 500);
        }
    }

    public function extensionAllOrder(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['id' => $id]
        ), [
            'id' => 'required|exists:loan_orders,id',
            'extended_method' => 'required|string|in:online,cash',
            'extension' => 'required|array',
            'extension.*.number_of_days' => 'required|integer|min:1',
            'extension.*.loan_order_details_id' => 'required|integer|exists:loan_order_details,id',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
            'extended_method.required' => 'Trường phương thức gia hạn là bắt buộc',
            'extended_method.string' => 'Trường phương thức gia hạn phải là kiểu chuỗi',
            'extended_method.in' => 'Trường phương thức gia hạn phải là online hoặc cash',
            'extension.required' => 'Trường thông tin gia hạn là bắt buộc',
            'extension.array' => 'Trường thông tin gia hạn phải là kiểu mảng',
            'extension.*.number_of_days.required' => 'Trường số ngày gia hạn là bắt buộc',
            'extension.*.number_of_days.integer' => 'Trường số ngày gia hạn phải là kiểu số',
            'extension.*.number_of_days.min' => 'Trường số ngày gia hạn phải lớn hơn hoặc bằng 1',
            'extension.*.loan_order_details_id.required' => 'Trường id chi tiết đơn hàng là bắt buộc',
            'extension.*.loan_order_details_id.integer' => 'Trường id chi tiết đơn hàng phải là kiểu số',
            'extension.*.loan_order_details_id.exists' => 'Id chi tiết đơn hàng không tồn tại',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $order = LoanOrders::with('loanOrderDetails')->find($id);

            if ($order->status !== 'active' && $order->status !== 'extended') {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Không thể gia hạn đơn hàng'
                ]);
            }

            if ($order->current_extensions >= $order->max_extensions) {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Đơn hàng đã đạt số lần gia hạn tối đa'
                ]);
            }

            foreach ($order->loanOrderDetails as $orderDetail) {
                if ($orderDetail->current_due_date < now()) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Extension order failed',
                        'errors' => 'Đơn hàng đã quá hạn'
                    ]);
                }
            }

            $extensionCreate = Extensions::create([
                'loan_order_id' => $order->id,
                'extension_date' => now(),
                'extension_fee' => 0,
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            $extensionDetails = [];

            foreach ($request->extension as $extension) {
                $orderDetail = LoanOrderDetails::find($extension['loan_order_details_id']);
                $extension_fee = 0;

                if ($orderDetail->bookDetails->book->category->name == 'Sách giáo khoa' && $order->user->role->name == 'student') {
                    $extension_fee = 0;
                } else {
                    if ($orderDetail->bookDetails->price < 50000) {
                        $extension_fee = 1000;
                    } elseif ($orderDetail->bookDetails->price < 100000) {
                        $extension_fee = 2000;
                    } else {
                        $extension_fee = 4000;
                    }
                }

                $extensionDetails[] = [
                    'extension_id' => $extensionCreate->id,
                    'loan_order_detail_id' => $extension['loan_order_details_id'],
                    'number_of_days' => $extension['number_of_days'],
                    'new_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + ' . $extension['number_of_days'] . ' days')),
                    'extension_fee' => $extension_fee * intval($extension['number_of_days']),
                ];
            }


            $extensionCreate->extensionDetails()->createMany($extensionDetails);

            $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'transaction_code' => $transaction_code,
                'portal' => null,
                'loan_order_id' => $order->id,
                'transaction_type' => 'extend',
                'expired_at' => now(),
                'transaction_method' => 'offline',
                'amount' => ExtensionDetails::where('extension_id', $extensionCreate->id)->sum('extension_fee'),
                'description' => 'Thanh toán gia hạn ' . $order->order_code,
                'status' => 'completed',
                'completed_at' => now()
            ]);

            $extensionCreate->update([
                'fee_transaction_id' => $transaction->id,
                'extension_fee' => ExtensionDetails::where('extension_id', $extensionCreate->id)->sum('extension_fee')
            ]);

            foreach ($request->extension as $extension) {
                $orderDetail = LoanOrderDetails::find($extension['loan_order_details_id']);

                $orderDetail->update([
                    'current_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + ' . $extension['number_of_days'] . ' days')),
                    'status' => 'extended'
                ]);
            }

            $order->update([
                'status' => 'extended',
                'current_extensions' => $order->current_extensions + 1,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Extension order successfully',
                'data' => [
                    'order' => $order,
                    'extension' => $extensionCreate,
                    'transaction' => $transaction
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Extension order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }

    public function extensionEachBook(Request $request, $id)
    {
        $validator = Validator::make(array_merge(
            $request->all(),
            ['id' => $id]
        ), [
            'id' => "required|exists:loan_order_details,id",
            'number_of_days' => 'required|integer|min:1',
        ], [
            'id.required' => 'Trường id là bắt buộc',
            'id.exists' => 'Id không tồn tại',
            'number_of_days.required' => 'Trường số ngày là bắt buộc',
            'number_of_days.integer' => 'Trường số ngày phải là kiểu số',
            'number_of_days.min' => 'Trường số ngày không được nhỏ hơn 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "staus" => false,
                "message" => "Dữ liệu không hợp lệ",
                "errors" => $validator->errors()
            ], 400);
        }

        try {
            $orderDetail = LoanOrderDetails::find($request->id);
            $order = LoanOrders::with('loanOrderDetails', 'user')->find($orderDetail->loan_order_id);

            if ($order->status !== 'active' && $order->status !== 'extended') {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Không thể gia hạn đơn hàng'
                ]);
            }

            if ($order->current_extensions >= $order->max_extensions) {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Đơn hàng đã đạt số lần gia hạn tối đa'
                ]);
            }

            if ($orderDetail->current_due_date < now()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Extension order failed',
                    'errors' => 'Đơn hàng đã quá hạn'
                ]);
            }

            $extension_fee = 0;

            if ($orderDetail->bookDetails->book->category->name == 'Sách giáo khoa' && $order->user->role->name == 'student') {
                $extension_fee = 0;
            } else {
                if ($orderDetail->bookDetails->price < 50000) {
                    $extension_fee = 1000;
                } elseif ($orderDetail->bookDetails->price < 100000) {
                    $extension_fee = 2000;
                } else {
                    $extension_fee = 4000;
                }
            }

            $extension = Extensions::create([
                'loan_order_id' => $order->id,
                'extension_date' => now(),
                'new_due_date' => date('Y-m-d', strtotime($order->current_due_date . ' + ' . $request->number_of_days . ' days')),
                'extension_fee' => 0,
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);

            $extension->extensionDetails()->create([
                'extension_id' => $extension->id,
                'number_of_days' => $request->number_of_days,
                'loan_order_detail_id' => $orderDetail->id,
                'new_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + ' . $request->number_of_days . ' days')),
                'extension_fee' => $extension_fee * $request->number_of_days
            ]);

            $transaction_code = intval(substr(strtotime(now()) . rand(1000, 9999), -9));

            $transaction = Transaction::create([
                'user_id' => auth()->user()->id,
                'transaction_code' => $transaction_code,
                'portal' => null,
                'loan_order_id' => $order->id,
                'transaction_type' => 'extend',
                'expired_at' => now(),
                'transaction_method' => 'offline',
                'amount' => $extension_fee * $request->number_of_days,
                'description' => 'Thanh toán gia hạn ' . $order->order_code,
                'status' => 'completed',
                'completed_at' => now()
            ]);

            $extension->update([
                'fee_transaction_id' => $transaction->id,
                'extension_fee' => ExtensionDetails::where('extension_id', $extension->id)->sum('extension_fee')
            ]);

            $orderDetail->update([
                'current_due_date' => date('Y-m-d', strtotime($orderDetail->current_due_date . ' + ' . $request->number_of_days . ' days')),
                'status' => 'extended'
            ]);

            $order->update([
                'status' => 'extended',
                'current_extensions' => $order->current_extensions + 1,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Extension order successfully',
                'data' => [
                    'order' => $order,
                    'extension' => $extension,
                    'transaction' => $transaction
                ]
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Extension order failed',
                'errors' => $th->getMessage()
            ]);
        }
    }
}
